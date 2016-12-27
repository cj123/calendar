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

            Appointment.delete($scope.appointment.id, dateToDelete).then(function(response) {
                if (response.status === 200) {
                    // reset the view
                    currentDate = moment();

                    // close the modal
                    $scope.cancel();

                    $rootScope.$broadcast("refresh", true);
                } else {
                    // @TODO display an error?
                }
            });
        };

        $scope.update = function() {
              
        };
    }]);
