angular.module("calendar").controller("AppointmentModal", [
    "$scope", "$uibModalInstance", "appointment", "currentDate", "Appointment", "CalendarOptions", "moment",
    function($scope, $uibModalInstance, appointment, currentDate, Appointment, UserOptions, moment) {
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
                dateToDelete = currentDate;
            }

            Appointment.delete($scope.appointment.id, dateToDelete).then(function(response) {
                if (response.status === 200) {
                    // reset the view
                    currentDate = moment();

                    // close the modal
                    $scope.cancel();
                } else {
                    // @TODO display an error?
                }
            });
        };
    }]);
