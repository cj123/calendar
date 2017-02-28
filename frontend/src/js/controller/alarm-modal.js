angular.module("calendar").controller("AlarmModal", [
    "$scope", "$rootScope", "$uibModalInstance", "$log", "activeAlarms", "currentDate", "moment",
    function($scope, $rootScope, $uibModalInstance, $log, activeAlarms, currentDate, moment) {
        console.log(activeAlarms);

        $scope.activeAlarms = activeAlarms;

        $scope.close = function() {
            $uibModalInstance.dismiss("close");
        };
    }
]);
