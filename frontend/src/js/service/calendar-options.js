angular.module("calendar").factory("CalendarOptions", [ "$http", "$cacheFactory", "API_BASE", function($http, $cacheFactory, API_BASE) {
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
     * Update calendar options
     *
     * @param calID
     * @param opts
     */
    calendarOptions.update = function(opts) {
        return $http.put(API_BASE + "calendar/" + calendarOptions.calendarID + "/options", opts).then(function(response) {
            $cacheFactory.get('$http').removeAll();

            return response;
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
