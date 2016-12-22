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

        function resetDayView() {
            CalendarOptions.get().then(function(response) {
                var opts = response.data;

                angular.element(document.getElementById("day-view")).scrollTop(60 * opts.DayviewTimeStart, 0);
            });
        }

        function monthDays(anyDayInMonth) {
            $scope.monthStart = anyDayInMonth.clone().date(1);
            var lastDayOfMonth = $scope.monthStart.clone().add(1, "month").subtract(1, "day");

            $scope.days = [];

            for (var i = $scope.monthStart.date(); i <= lastDayOfMonth.date(); i++) {
                $scope.days.push({day: i, events: []});
            }

            Appointment.getAppointments($scope.monthStart, lastDayOfMonth).then(function(appts) {
                for (var apptIndex = 0; apptIndex < appts.length; apptIndex++) {
                    var appt = appts[apptIndex];

                    if (appt.recurrences.length > 0) {
                        for (var apptRecurrIndex = 0; apptRecurrIndex < appt.recurrences.length; apptRecurrIndex++) {
                            var recurrence = appt.recurrences[apptRecurrIndex];

                            $scope.days[moment(recurrence).date() - 1].events.push(appt);
                        }
                    } else {
                        $scope.days[appt.start.date() - 1].events.push(appt);
                    }
                }
            });
        }
    }
]);
