angular.module("calendar")
    .controller("AppointmentModal", ["$scope", "$uibModalInstance", "appointment", "CalendarOptions", function($scope, $uibModalInstance, appointment, UserOptions) {
        $scope.appointment = appointment;

        UserOptions.getAndMergeWithAppointment(appointment).then(function(mergedAppt) {
            $scope.appointment = mergedAppt;
        });

        $scope.cancel = function() {
            $uibModalInstance.dismiss("cancel");
        };
    }]);
