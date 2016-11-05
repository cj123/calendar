angular.module("calendar").factory("Appointment", [ "$http", "API_BASE", function($http, API_BASE) {
    var appointment = {};

    appointment.getAppointments = function(date) {
        return $http.get(API_BASE + "ajax/day-view", {
            params: {
                date: date.format("Y-M-D")
            }
        });
    };

    return appointment;
}]);
