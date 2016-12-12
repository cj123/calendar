angular.module("calendar").directive("monthView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '='
        },
        templateUrl: "calendar/view/month-view.html",
        controller: [
            "$scope", "Appointment", "moment",
            function($scope, Appointment, moment) {
                $scope.dayIndex = 0;
                $scope.today = moment();

                // watch the current date of the view for changes.
                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, function() {
                    monthDays($scope.currentDate);
                });


                function monthDays(dayInMonth) {
                    var firstDayOfMonth = dayInMonth.clone().date(1);
                    var lastDayOfMonth = firstDayOfMonth.clone().add(1, "month").subtract(1, "day");
                    var days = [];

                    for (var i = firstDayOfMonth.date(); i <= lastDayOfMonth.date(); i++) {
                        days.push({day: i, events: false});
                    }

                    Appointment.getAppointments(firstDayOfMonth, lastDayOfMonth).then(function(appts) {
                        for (var apptIndex = 0; apptIndex < appts.length; apptIndex++) {
                            var appt = appts[apptIndex];

                            if (appt.recurrences.length > 0) {
                                for (var apptRecurrIndex = 0; apptRecurrIndex < appt.recurrences.length; apptRecurrIndex++) {
                                    var recurrence = appt.recurrences[apptRecurrIndex];

                                    days[moment(recurrence).date() - 1].events = true;
                                }
                            } else {
                                days[appt.start.date() - 1].events = true;
                            }
                        }

                        for (i = 1; i < firstDayOfMonth.day(); i++) {
                            // prepend padding days
                            days.unshift({});
                        }

                        var dayGrid = [];

                        for (var j = 0; j < days.length; j++) {
                            var week = (j / 7) | 0;

                            if (!(dayGrid[week] instanceof Array)) {
                                dayGrid[week] = [];
                            }

                            dayGrid[week].push(days[j]);
                        }

                        $scope.weeks = dayGrid;
                    });


                }
            }
        ]
    };
}]);
