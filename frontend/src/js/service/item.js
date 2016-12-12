angular.module("calendar").factory("Item", ["moment", function(moment) {
    var itemFactory = {};

    /**
     * Remove exdate from recurrence rule.
     *
     * @param rule
     * @returns {string}
     */
    function stripExDate(rule) {
        // strip exclusion dates from the recurrence rule, because despite the library saying it
        // supports them, it does not.
        if (rule.indexOf(";EXDATE") !== -1) {
            rule = rule.substring(0, rule.indexOf(';EXDATE'));
        }

        return "RRULE:" + rule;
    }

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

            if (!!item.recurrence_rule) {
                var rule = rrulestr(stripExDate(item.recurrence_rule), {
                    dtstart: item.start.toDate(),
                });

                item.rule = rule;
                item.recurrences = rule.between(startDate.toDate(), endDate.toDate(), true);

                if (item.recurrences.length > 0) { // @TODO non-recurring dates
                    filtered.push(item);
                }
            } else {
                item.rule = null;
                item.recurrences = [];
                filtered.push(item);
            }
        }

        return filtered;
    };

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
