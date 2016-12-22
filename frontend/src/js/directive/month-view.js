angular.module("calendar").directive("monthView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            days: '=',
            monthStart: '='
        },
        templateUrl: "calendar/view/month-view.html",
        controller: [
            "$scope", "moment",
            function ($scope, moment) {
                $scope.dayIndex = 0;
                $scope.today = moment();

                $scope.$watch("days", function() {
                    var dayGrid = [];
                    var shiftedDays = $scope.days.slice(0); // clone

                    for (var i = 1; i < $scope.monthStart.day(); i++) {
                        // prepend padding days
                        shiftedDays.unshift({});
                    }

                    for (var j = 0; j < shiftedDays.length; j++) {
                        var week = (j / 7) | 0;

                        if (!(dayGrid[week] instanceof Array)) {
                            dayGrid[week] = [];
                        }

                        dayGrid[week].push(shiftedDays[j]);
                    }

                    $scope.weeks = dayGrid;
                });
            }
        ]
    };
}]);
