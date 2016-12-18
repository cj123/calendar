angular.module("calendar")
    .controller("AppointmentModal", ["$scope", "$uibModalInstance", "appointment", "Appointment", "CalendarOptions", function($scope, $uibModalInstance, appointment, Appointment, UserOptions) {
        $scope.appointment = appointment;

        UserOptions.getAndMergeWithAppointment(appointment).then(function(mergedAppt) {
            $scope.appointment = mergedAppt;
        });

        $scope.cancel = function() {
            $uibModalInstance.dismiss("cancel");
        };

        $scope.delete = function(deleteRecurrences) {

            Appointment.delete($scope.appointment.id, deleteRecurrences).then(function() {
                console.log("deleted");
            });
        };
    }]);
