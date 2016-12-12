angular.module("calendar").controller("CalendarController", [
    "$scope", "moment",
    function($scope, moment) {
        $scope.currentDate = moment();
        $scope.monthAppointments = {};
    }
]);
