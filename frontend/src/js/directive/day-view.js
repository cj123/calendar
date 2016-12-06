angular.module("calendar").directive("dayView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '='
        },
        templateUrl: "calendar/view/day-view.html",
        controller: [
            "$scope", "$document", "$uibModal", "Appointment", "CalendarOptions",
            function($scope, $document, $uibModal, Appointment, CalendarOptions) {
                $scope.appointments = [];

                // watch the current date of the view for changes.
                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, function() {
                    loadAppointments($scope.currentDate);
                });

                function loadAppointments(date) {
                    if (!date) {
                        return;
                    }

                    Appointment.getAppointments(date.clone(), date.clone()).then(function(appointments) {
                        $scope.appointments = appointments;

                        for (var i = 0; i < appointments.length; i++) {
                            console.log(appointments[i]);
                        }

                        CalendarOptions.get().then(function(response) {
                            var opts = response.data;

                            angular.element(document.getElementById("day-view")).scrollTop(60 * opts.DayviewTimeStart, 0);
                        });
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
            }
        ]
    };
}]);
