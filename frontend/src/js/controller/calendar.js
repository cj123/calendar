angular.module("calendar").controller("CalendarController", [
    "$scope", "moment", "Appointment", "CalendarOptions",
    function($scope, moment, Appointment, CalendarOptions) {
        $scope.currentDate = moment();
        $scope.monthStart = null;
        $scope.days = [];

        // watch the current date of the view for changes.
        $scope.$watch(function() {
            if ($scope.currentDate) {
                return $scope.currentDate.format("x");
            }
        }, function() {
            resetDayView();
        });

        $scope.$watch(function() {
            if ($scope.currentDate) {
                return $scope.currentDate.format("YYYYMM");
            }
        }, function() {
            monthDays($scope.currentDate);
        });

        $scope.$on("refresh", function() {
            monthDays($scope.currentDate).then(function() {
                resetDayView();
            });
        });

        function resetDayView() {
            CalendarOptions.get().then(function(response) {
                var opts = response.data;

                angular.element(document.getElementById("day-view")).scrollTop(60 * opts.DayviewTimeStart, 0);
            });
        }

        function monthDays(anyDayInMonth) {
            $scope.monthStart = anyDayInMonth.clone().date(1);
            var lastDayOfMonth = $scope.monthStart.clone().add(1, "month").subtract(1, "day");

            var days = [];

            for (var i = $scope.monthStart.date(); i <= lastDayOfMonth.date(); i++) {
                days.push({day: i, events: []});
            }

            return Appointment.getAppointments($scope.monthStart, lastDayOfMonth).then(function(appts) {
                for (var apptIndex = 0; apptIndex < appts.length; apptIndex++) {
                    var appt = appts[apptIndex];

                    if (appt.recurrences.length > 0) {
                        for (var apptRecurrIndex = 0; apptRecurrIndex < appt.recurrences.length; apptRecurrIndex++) {
                            var recurrence = appt.recurrences[apptRecurrIndex];
                            var recurrenceDate = moment(recurrence);
                            var isDeleted = false;

                            if (appt.deleted && appt.deleted.length) {
                                for (var deletedIndex = 0; deletedIndex < appt.deleted.length; deletedIndex++) {
                                    var deletedDate = moment(appt.deleted[deletedIndex].date, "YYYY-MM-DDTHH:mm:ss");

                                    if (deletedDate.diff(recurrenceDate) === 0) {
                                        isDeleted = true;
                                    }
                                }

                                if (isDeleted) {
                                    continue;
                                }
                            }

                            days[recurrenceDate.date() - 1].events.push(appt);
                        }
                    } else {
                        days[appt.start.date() - 1].events.push(appt);
                    }
                }
            }).then(function() {
                $scope.days = days;
            });
        }
    }
]);
