angular.module("calendar").factory("CalendarOptions", [ "$http", "API_BASE", function($http, API_BASE) {
    var calendarOptions = {};

    calendarOptions.setCalendarID = function(id) {
        calendarOptions.calendarID = id;
    };

    /**
     * Get Calendar Options (including defaults)
     *
     * @returns {HttpPromise}
     */
    calendarOptions.get = function() {
        return $http.get(API_BASE + "calendar/" + calendarOptions.calendarID + "/options", {
            cache: true
        });
    };

    /**
     * Get calendar defaults and merge them with a given item.
     *
     * @param item
     * @returns {*}
     */
    calendarOptions.getAndMergeWithItem = function(item) {
        return this.get().then(function(response) {
            var opts = response.data;

            item.remind_start   = item.remind_start || opts.DefaultEarlyWarning;
            item.timezone       = item.timezone || opts.Timezone;

            return item;
        });
    };

    return calendarOptions;
}]);
