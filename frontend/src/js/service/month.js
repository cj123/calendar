angular.module("calendar").factory("Month", [ "$http", "API_BASE", function($http, API_BASE) {
    var month = {};

    month.getDays = function(month, year) {
        return $http.get(API_BASE + "ajax/month-view", {
            params: {
                year: year,
                month: month
            }
        });
    };

    return month;
}]);
