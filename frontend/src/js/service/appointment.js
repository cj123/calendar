angular.module("calendar").factory("Appointment", [ "$http", "API_BASE", function($http, API_BASE) {
    var appointment = {};

    /**
     * Get Appointments for a given date.
     *
     * @param date
     * @returns {HttpPromise}
     */
    appointment.getAppointments = function(date) {
        return $http.get(API_BASE + "calendar/day-view", {
            params: {
                date: date.format("Y-M-D")
            }
        });
    };

    return appointment;
}]);
