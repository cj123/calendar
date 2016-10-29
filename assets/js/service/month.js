angular.module("calendar").factory("Month", [ "$http", function($http) {
    var month = {};

    month.getDays = function(month, year) {
        return $http.get("/ajax/month-view", {
            params: {
                year: year,
                month: month
            },
            cache: true
        });
    };

    return month;
}]);
