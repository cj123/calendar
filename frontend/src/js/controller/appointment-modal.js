angular.module("calendar")
    .controller("AppointmentModal", ["$scope", "$uibModalInstance", "appointment", function($scope, $uibModalInstance, appointment) {
        $scope.appointment = appointment;

        $scope.cancel = function() {
            $uibModalInstance.dismiss("cancel");
        };
    }]);
