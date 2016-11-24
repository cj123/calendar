angular.module("calendar").directive("monthView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '='
        },
        templateUrl: "calendar/view/month-view.html",
        controller: [
            "$scope", "Month", "moment",
            function($scope, Month, moment) {
                $scope.dayIndex = 0;
                $scope.today = moment();

                // watch the current date of the view for changes.
                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, function() {
                    loadGridView($scope.currentDate);
                });

                function loadGridView(date) {
                    Month.getDays(date.format("M"), date.format("Y")).then(function(daysByWeek) {
                        $scope.weeks = daysByWeek;
                    });
                }
            }
        ]
    };
}]);
