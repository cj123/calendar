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
        }).then(function(response) {
            var appointments = response.data;

            for (var appointmentIndex = 0; appointmentIndex < appointments.length; appointmentIndex++) {
                var appointment = appointments[appointmentIndex];
                var startTime = date.clone().minute(0).hour(0).add(appointment.start_time, "minutes");
                var endTime = startTime.clone().add(appointment.length, "minutes");

                appointments[appointmentIndex].startTime = startTime;
                appointments[appointmentIndex].endTime = endTime;
            }

            return appointments;
        });
    };

    return appointment;
}]);
