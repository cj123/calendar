angular.module("calendar").directive("dayView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            days: '='
        },
        templateUrl: "calendar/view/day-view.html",
        controller: [
            "$scope", "$document", "$uibModal", "Appointment",
            function($scope, $document, $uibModal, Appointment) {
                function loadAppointments() {
                    $scope.appointments = $scope.days[$scope.currentDate.date() - 1].events;

                    $scope.newAppointment = null; // clear out any newly created appointments
                }

                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, loadAppointments);

                $scope.$watch("days", loadAppointments);

                $scope.newAppointment = null;

                $scope.viewAppointmentDetail = function(appointment) {
                    $uibModal.open({
                        animation: true,
                        templateUrl: "calendar/view/modals/appointment.html",
                        controller: "AppointmentModal",
                        resolve: {
                            appointment: function() {
                                return appointment;
                            },
                            currentDate: function() {
                                return $scope.currentDate;
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

                    //console.log($scope.newAppointment);
                };

                $scope.submitAppointment = function() {
                    Appointment.create($scope.newAppointment).then(function(response) {
                        console.log(response);

                        $scope.$emit("refresh", true);
                    }).catch(function(err) {
                       console.log(err);
                    });
                };
            }
        ]
    };
}]);
