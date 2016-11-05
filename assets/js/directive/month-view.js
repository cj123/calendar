angular.module("calendar").directive("monthView", [function() {
    return {
        restrict: "E",
        scope: {},
        templateUrl: "calendar/view/month-view.html",
        controller: ["$scope", "Month", "CalendarData", "moment", function($scope, Month, CalendarData, moment) {
            console.log($scope.test);

            $scope.dayIndex = 0;
            $scope.today = moment(); // @todo handle today better
            $scope.currentDate = CalendarData.currentDate;

            // watch the current date of the view for changes.
            $scope.$watch(function() {
                if (CalendarData.currentDate) {
                    return CalendarData.currentDate.format("x");
                }
            }, function() {
                loadGridView(CalendarData.currentDate);
            });

            function loadGridView(date) {
                Month.getDays(date.format("M"), date.format("Y")).then(function(response) {
                    $scope.days = response.data;
                });
            }
        }]
    };
}]);
