angular.module("calendar").factory("Month", [ "$http", "API_BASE", function($http, API_BASE) {
    var month = {};

    /**
     * Get Calendar Days in a Month
     * @param month
     * @param year
     * @returns {HttpPromise}
     */
    month.getDays = function(month, year) {
        return $http.get(API_BASE + "calendar/month-view", {
            params: {
                year: year,
                month: month
            }
        }).then(function(response) {
            var data = response.data;
            var paddingDays = data.padding_days;
            var dayGrid = [];

            for (var i = 0; i < paddingDays; i++) {
                if (!(dayGrid[0] instanceof Array)) {
                    dayGrid[0] = [];
                }

                dayGrid[0].push({});
            }

            for (var j = 0; j < data.days.length; j++) {
                var week = ((j + paddingDays) / 7) | 0;

                if (!(dayGrid[week] instanceof Array)) {
                    dayGrid[week] = [];
                }

                dayGrid[week].push(data.days[j]);
            }

            return dayGrid;
        });
    };

    return month;
}]);
