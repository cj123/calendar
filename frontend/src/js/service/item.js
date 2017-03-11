angular.module("calendar").factory("Item", [
    "moment", "$http", "$log", "API_BASE", "Appointment", "Note",
    function(moment, $http, $log, API_BASE, Appointment, Note)
{
    var itemFactory = {};

    function isAppointment(item) {
        return item.data_type === "appointment";
    }

    function isNote(item) {
        return item.data_type === "note";
    }

    /**
     * Set up calendar ID for modification
     * @param id
     */
    itemFactory.setCalendarID = function(id) {
        itemFactory.calendarID = id;
    };

    /**
     * Get calendar ID
     * @returns {*}
     */
    itemFactory.getCalendarID = function() {
        return itemFactory.calendarID;
    };

    /**
     * Get a given item type, between start and end dates.
     *
     * @param itemType
     * @param startDate
     * @param endDate
     * @returns {*}
     */
    itemFactory.get = function(itemType, startDate, endDate) {
        var response;

        if (itemType == "appointment") {
            response = Appointment.get(itemFactory.calendarID, startDate, endDate);
        } else if (itemType == "note") {
            response = Note.get(itemFactory.calendarID, startDate, endDate);
        } else {
            throw "Invalid item type" + itemType;
        }

        return response.then(function(response) {
            return itemFactory.filterBetweenDates(response.data, startDate, endDate);
        });
    };

    itemFactory.create = function(itemType, item) {
        if (itemType == "appointment") {
            return Appointment.create(itemFactory.calendarID, item).then(function(response) {
                $log.debug("successfully created appointment, reattaching time information");
                return itemFactory.processTimes(response.data);
            });
        } else if (itemType == "note") {
            return Note.create(itemFactory.calendarID, item);
        } else {
            throw "Invalid item type";
        }
    };

    itemFactory.delete = function(item, dateToDelete) {
        if (!isAppointment(item) && !isNote(item)) {
            throw "Invalid item type";
        }

        return $http.delete(API_BASE + "calendar/" + itemFactory.calendarID + "/" + item.data_type + "/" + item.id, {
            data: {
                date: dateToDelete !== null && dateToDelete.toISOString() || moment().toISOString(),
                delete_all: dateToDelete === null
            }
        });
    };

    itemFactory.update = function(item) {
        if (isAppointment(item)) {
            return Appointment.update(itemFactory.calendarID, item);
        } else if (isNote(item)) {
            return Note.update(itemFactory.calendarID, item);
        } else {
            throw "Invalid item type";
        }
    };

    /**
     * Filter items to only display those occurring between two dates. between two dates.
     * @param items
     * @param startDate
     * @param endDate
     * @returns {Array}
     */
    itemFactory.filterBetweenDates = function(items, startDate, endDate) {
        var filtered = [];

        startDate.hour(0).minute(0).second(0);
        endDate.hour(23).minute(59).second(59);

        for (var itemIndex = 0; itemIndex < items.length; itemIndex++) {
            var item = itemFactory.processTimes(items[itemIndex]);

            // mark collisions
            item.collisions = [];

            if (!!item.recurrence_rule) {
                try {
                    item.recurrences = recurrencesBetween(item.recurrence_rule, item.start, startDate, endDate);

                    if (item.recurrences.length > 0) {
                        filtered.push(item);
                    }
                } catch (err) {
                    $log.debug("recurrence error: " + err + " on item: " + item.text);

                    // add to filtered w/o rule
                    item.recurrences = [];
                    filtered.push(item);
                }

            } else {
                item.recurrences = [];
                filtered.push(item);
            }
        }

        return filtered;
    };

    /**
     *
     * @param rrulestr
     * @param dtstart
     * @param setStart
     * @param setEnd
     * @returns {Array}
     */
    function recurrencesBetween(rrulestr, dtstart, setStart, setEnd) {
        var start = ICAL.Time.fromJSDate(dtstart.toDate());

        // hack: https://github.com/mozilla-comm/ical.js/issues/243
        var rule = ICAL.Recur.fromData(ICAL.Recur._stringToData(rrulestr, true));

        var iterator = rule.iterator(start);

        setStart = ICAL.Time.fromJSDate(setStart.toDate());
        setEnd = ICAL.Time.fromJSDate(setEnd.toDate());

        var recurrences = [];

        for (var next = iterator.next(); next; next = iterator.next()) {
            if (next.compare(setStart) < 0) {
                // keep going till we hit start
                continue;
            } else if (next.compare(setEnd) > 0) {
                // stop if we get past the end
                break;
            }

            recurrences.push(next.toJSDate());
        }

        return recurrences;
    }

    /**
     * Remove exdate from recurrence rule.
     *
     * @param rule
     * @returns {string}
     */
    itemFactory.stripExDate = function(rule) {
        // strip exclusion dates from the recurrence rule, because despite the library saying it
        // supports them, it does not.
        if (rule.indexOf(";EXDATE") !== -1) {
            rule = rule.substring(0, rule.indexOf(';EXDATE'));
        }
        return "RRULE:" + rule;
    };

    /**
     * Given an item, assign it a length, offset and start and end moment objects.
     * @param item
     * @returns {*}
     */
    itemFactory.processTimes = function(item) {
        if (!item.timezone || item.timezone == "<Local>" || item.timezone == "Local") {
            item.timezone = moment.tz.guess();
        }

        item.start = moment.tz(item.start, "YYYY-MM-DDTHH:mm:ss", item.timezone);
        item.finish = moment.tz(item.finish, "YYYY-MM-DDTHH:mm:ss", item.timezone);
        item.length = Math.abs(moment.duration(item.finish.diff(item.start)).asMinutes());
        item.offset = Math.abs((item.start.hour() * 60) + item.start.minute());

        if (!!item.recurrence_rule) {
            item.rule = rrulestr(itemFactory.stripExDate(item.recurrence_rule), {
                dtstart: item.start.toDate()
            });
        }

        return item;
    };


    return itemFactory;
}]);
