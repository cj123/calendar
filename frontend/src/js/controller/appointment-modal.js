angular.module("calendar").controller("AppointmentModal", [
    "$scope", "$rootScope", "$uibModalInstance", "appointment", "currentDate", "Appointment", "CalendarOptions", "moment",
    function($scope, $rootScope, $uibModalInstance, appointment, currentDate, Appointment, UserOptions, moment) {
        $scope.appointment = appointment;

        UserOptions.getAndMergeWithAppointment(appointment).then(function(mergedAppt) {
            $scope.appointment = mergedAppt;
        });

        $scope.cancel = function() {
            $uibModalInstance.dismiss("cancel");
        };

        $scope.delete = function(deleteRecurrences) {
            var dateToDelete = null;

            if (!deleteRecurrences) {
                dateToDelete = currentDate.hour(appointment.start.hour()).minute(appointment.start.minute()).second(appointment.start.second()).millisecond(0);
            }

            return Appointment.delete($scope.appointment.id, dateToDelete).then(function(response) {
                if (response.status === 200) {
                    // reset the view
                    currentDate = moment();

                    // close the modal
                    $uibModalInstance.close($scope.appointment);

                    $rootScope.$broadcast("refresh", true);
                } else {
                    // @TODO display an error?
                }
            });
        };

        $scope.update = function(updateAllAppointments) {
            console.log($scope.appointment.id);

            if (!$scope.appointment.id) {
                // need to create the appointment
                Appointment.create($scope.appointment).then(function(response) {
                    $uibModalInstance.close($scope.appointment);
                    $rootScope.$broadcast("refresh", true);
                }).catch(function(err) {
                    console.log(err);
                });
            } else if (updateAllAppointments) {
                Appointment.update($scope.appointment).then(function(response) {
                    if (response.status === 200) {
                        $uibModalInstance.close($scope.appointment);
                        $rootScope.$broadcast("refresh", true);
                    } else {
                        console.log(response);
                    }
                });
            } else {
                var id = $scope.appointment.id;

                Appointment.create($scope.appointment).then(function(response) {
                    console.log(response);

                    if (response.status === 201) {
                        $scope.appointment.id = id;

                        $scope.delete(false);
                    } else {
                        console.log(response);
                    }
                }).catch(function(err) {
                    console.log(err);
                });
            }
        };
    }]);
