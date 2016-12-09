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
                $scope.newAppointment = null;

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

                $scope.createAppointment = function(event) {
                    var offset = event.offsetY - (event.offsetY % 30); // rounded to nearest 30min

                    var start = $scope.currentDate.clone()
                        .hour(0)
                        .minute(0)
                        .second(0)
                        .add(offset, "minutes");

                    $scope.newAppointment = {
                        offset: offset,
                        start: start,
                        finish: start.clone().add(30, "minutes")
                    };

                    console.log($scope.newAppointment);
                };
            }
        ]
    };
}]);
