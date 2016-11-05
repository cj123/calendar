angular.module("calendar").directive("dayView", [function() {
    return {
        restrict: "E",
        scope: {},
        templateUrl: "calendar/view/day-view.html",
        controller: ["$scope", "$uibModal", "Appointment", "CalendarData", "moment", function($scope, $uibModal, Appointment, CalendarData, moment) {
            $scope.currentDate = CalendarData.currentDate;
            $scope.appointments = [];

            // watch the current date of the view for changes.
            $scope.$watch(function() {
                if (CalendarData.currentDate) {
                    return CalendarData.currentDate.format("x");
                }
            }, function() {
                loadAppointments(CalendarData.currentDate);
            });

            function loadAppointments(date) {
                if (!date) {
                    return;
                }

                Appointment.getAppointments(date).then(function(response) {
                    var appointments = response.data;

                    for (var appointmentIndex = 0; appointmentIndex < appointments.length; appointmentIndex++) {
                        var appointment = appointments[appointmentIndex];
                        var startTime = $scope.currentDate.clone().minute(0).hour(0).add(appointment.start_time, "minutes");
                        var endTime = startTime.clone().add(appointment.length, "minutes");

                        appointments[appointmentIndex].startTime = startTime;
                        appointments[appointmentIndex].endTime = endTime;
                    }

                    $scope.appointments = appointments;
                });
            }


            $scope.viewAppointmentDetail = function(appointment) {
                $uibModal.open({
                    animation: true,
                    templateUrl: "calendar/view/modals/appointment.html",
                    controller: "AppointmentModal",
                    resolve: {
                        appointment: function() {
                            return appointment;
                        }
                    }
                });
            };
        }]
    };
}]);
