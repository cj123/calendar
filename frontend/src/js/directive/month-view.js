angular.module("calendar").directive("monthView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            days: '=',
            monthStart: '='
        },
        templateUrl: "calendar/view/directives/month-view.html",
        controller: [
            "$scope", "moment", "CalendarOptions", "hotkeys",
            function ($scope, moment, CalendarOptions, hotkeys) {
                $scope.dayIndex = 0;
                $scope.today = moment();
                $scope.opts = [];

                CalendarOptions.get().then(function(res) {
                    $scope.opts = res.data;

                    $scope.$watch("days", function() {
                        if ($scope.days && $scope.monthStart) {
                            updateDays();
                        }
                    });
                });

                function updateDays() {
                    var dayGrid = [];
                    var shiftedDays = $scope.days.slice(0); // clone
                    var monthStartDay = $scope.monthStart.day();

                    if ($scope.opts.MondayFirst && monthStartDay === 0) {
                        monthStartDay = 7;
                    }

                    if (!$scope.opts.MondayFirst) {
                        monthStartDay++;
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
                }

                // hotkeys
                hotkeys.bindTo($scope)
                    .add({
                        combo: 'up',
                        description: 'previous week',
                        callback: function() {
                            $scope.currentDate.subtract(1, 'week');
                        }
                    })
                    .add({
                        combo: 'down',
                        description: 'next week',
                        callback: function() {
                            $scope.currentDate.add(1, 'week');
                        }
                    })
                    .add({
                        combo: 'right',
                        description: 'next day',
                        callback: function() {
                            $scope.currentDate.add(1, 'day');
                        }
                    })
                    .add({
                        combo: 'left',
                        description: 'previous day',
                        callback: function() {
                            $scope.currentDate.subtract(1, 'day');
                        }
                    })
                    .add({
                        combo: 't',
                        description: 'jump to today',
                        callback: function() {
                            $scope.currentDate = moment();
                        }
                    })
                ;
            }
        ]
    };
}]);
