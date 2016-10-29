angular.module("calendar").factory("Appointment", [ "$http", function($http) {
    var appointment = {};

    appointment.getAppointments = function(date) {
        return $http.get("/ajax/day-view", {
            params: {
                date: date.format("Y-M-D")
            }
        });
    };

    return appointment;
}]);
