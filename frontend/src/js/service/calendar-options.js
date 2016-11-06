angular.module("calendar").factory("CalendarOptions", [ "$http", "API_BASE", function($http, API_BASE) {
    var calendarOptions = {};

    /**
     * Get Calendar Options (including defaults)
     *
     * @returns {HttpPromise}
     */
    calendarOptions.get = function() {
        return $http.get(API_BASE + "calendar/options", {
            cache: true
        });
    };

    /**
     * Get calendar defaults and merge them with a given appointment.
     *
     * @param appointment
     * @returns {*}
     */
    calendarOptions.getAndMergeWithAppointment = function(appointment) {
        return this.get().then(function(response) {
            var opts = response.data;

            appointment.alarms   = appointment.alarms || opts.DefaultAlarms;
            appointment.remind   = appointment.remind || opts.DefaultEarlyWarning;
            appointment.timezone = appointment.timezone || opts.Timezone;

            return appointment;
        });
    };

    return calendarOptions;
}]);
