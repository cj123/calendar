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
            "$scope", "moment", "CalendarOptions",
            function ($scope, moment, CalendarOptions) {
                $scope.dayIndex = 0;
                $scope.today = moment();
                $scope.opts = [];

                CalendarOptions.get().then(function(res) {
                    $scope.opts = res.data;
                }).then(function() {
                    $scope.$watch("days", function() {
                        var dayGrid = [];
                        var shiftedDays = $scope.days.slice(0); // clone
                        var monthStartDay = $scope.monthStart.day();

                        if ($scope.opts.MondayFirst && monthStartDay === 0) {
                            monthStartDay = 7;
                        }

                        for (var i = 1; i < monthStartDay; i++) {
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
                });
            }
        ]
    };
}]);
