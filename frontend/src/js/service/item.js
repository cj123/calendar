angular.module("calendar").factory("Item", ["moment", "$http", "$log", "API_BASE", "Appointment", "Note", function(moment, $http, $log, API_BASE, Appointment, Note) {
    var itemFactory = {};

    function isAppointment(item) {
        return item.data_type === "appointment";
    }

    function isNote(item) {
        return item.data_type === "note";
    }

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
            response = Appointment.get(startDate, endDate);
        } else if (itemType == "note") {
            response = Note.get(startDate, endDate);
        } else {
            throw "Invalid item type" + itemType;
        }

        return response.then(function(response) {
            return itemFactory.filterBetweenDates(response.data, startDate, endDate);
        });
    };

    itemFactory.create = function(itemType, item) {
        if (itemType == "appointment") {
            return Appointment.create(item).then(function(response) {
                $log.debug("successfully created appointment, reattaching time information");
                return itemFactory.processTimes(response.data);
            });
        } else if (itemType == "note") {
            return Note.create(item);
        } else {
            throw "Invalid item type";
        }
    };

    itemFactory.delete = function(item, dateToDelete) {
        if (!isAppointment(item) && !isNote(item)) {
            throw "Invalid item type";
        }

        return $http.delete(API_BASE + "calendar/" + item.data_type + "/" + item.id, {
            data: {
                date: dateToDelete !== null && dateToDelete.toISOString() || moment().toISOString(),
                delete_all: dateToDelete === null
            }
        });
    };

    itemFactory.update = function(item) {
        if (isAppointment(item)) {
            return Appointment.update(item);
        } else if (isNote(item)) {
            return Note.update(item);
        } else {
            throw "Invalid item type";
        }
    };

    /**
     * Filter items to only display those occuring between two dates. between two dates.
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
                item.recurrences = recurrencesBetween(item.recurrence_rule, item.start, startDate, endDate);

                if (item.recurrences.length > 0) {
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
                continue;
            } else if (next.compare(setEnd) > 0) {
                break;
            }

            recurrences.push(next.toJSDate());
        }

        return recurrences;
    }

    /**
     * Given an item, assign it a length, offset and start and end moment objects.
     * @param item
     * @returns {*}
     */
    itemFactory.processTimes = function(item) {
        if (!item.timezone) {
            item.timezone = moment.tz.guess();
        }

        item.start = moment.tz(item.start, "YYYY-MM-DDTHH:mm:ss", item.timezone);
        item.finish = moment.tz(item.finish, "YYYY-MM-DDTHH:mm:ss", item.timezone);
        item.length = Math.abs(moment.duration(item.finish.diff(item.start)).asMinutes());
        item.offset = Math.abs((item.start.hour() * 60) + item.start.minute());

        return item;
    };


    return itemFactory;
}]);
