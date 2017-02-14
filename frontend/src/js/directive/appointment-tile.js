angular.module("calendar").directive("appointmentTile", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            info: '=',
            isNew: '='
        },
        templateUrl: "calendar/view/appointment-tile.html",
        controller: [
            "$scope", "$document", "$uibModal", "Appointment",
            function($scope, $document, $uibModal, Appointment) {
                $scope.active = false;

                $scope.viewDetail = function() {
                    $uibModal.open({
                        animation: true,
                        templateUrl: "calendar/view/modals/appointment.html",
                        controller: "AppointmentModal",
                        resolve: {
                            appointment: function() {
                                return $scope.info;
                            },
                            currentDate: function() {
                                return $scope.currentDate;
                            }
                        }
                    });
                };

                $scope.$on("angular-resizable.resizing", function(evt, args) {
                    console.log(evt, args);
                    console.log(args.evt);
                });

                $scope.$on("angular-resizable.resizeEnd", function(evt, args) {
                    if (args.width) {
                        // @TODO dunno yet, probably save this and xOffset somewhere
                    }

                    if (args.height) {
                        console.log("I should update here");
                        // change the appointment
                        $scope.info.length = args.height - (args.height % 5);

                        $scope.submit();
                    }
                });

                $scope.submit = function() {
                    if (!$scope.info) {
                        return;
                    }

                    if (!$scope.info.id) {
                        Appointment.create($scope.info).then(function(response) {
                            console.log(response);

                            $scope.$emit("refresh", true);
                        }).catch(function(err) {
                            console.log(err);
                        });
                    } else {
                        // update here
                        Appointment.update($scope.info).then(function(response) {
                            console.log(response);

                            $scope.$emit("refresh", true);
                        }).catch(function(err) {
                            console.log(err);
                        });
                    }
                };
            }
        ]
    };
}]);
