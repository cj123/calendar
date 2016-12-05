angular.module("calendar").factory("Appointment", [ "$http", "moment", "API_BASE", function($http, moment, API_BASE) {
    var appointment = {};

    function recurrTransform(rule) {

        // strip exclusion dates from the recurrence rule, because despite the library saying it
        // supports them, it does not.
        if (rule.indexOf(";EXDATE") !== -1) {
            rule = rule.substring(0, rule.indexOf(';EXDATE'));
        }

        return "RRULE:" + rule;
    }

    /**
     * Get Appointments for a given date range.
     * Sets time of startDate to be 00:00:00 and endDate to be 23:59:59.
     *
     * @param startDate
     * @param endDate
     * @returns {HttpPromise}
     */
    appointment.getAppointments = function(startDate, endDate) {
        return $http.get(API_BASE + "calendar/appointments", {
            params: {
                start: startDate.hour(0).minute(0).second(0).format("Y-M-D"),
                finish: endDate.hour(23).minute(59).second(59).format("Y-M-D")
            }
        }).then(function(response) {
            var appointments = response.data;
            var filtered = [];

            for (var appointmentIndex = 0; appointmentIndex < appointments.length; appointmentIndex++) {
                var appt = appointments[appointmentIndex];

                if (!!appt.recurrence_rule) {
                    var rule = rrulestr(recurrTransform(appt.recurrence_rule), {
                        dtstart: moment(appt.start).toDate(),
                    });

                    appt.rule = rule;
                    appt.recurrences = rule.between(startDate.toDate(), endDate.toDate(), true);

                    if (appt.recurrences.length > 0) { // @TODO non-recurring dates
                        filtered.push(appt);
                    }
                } else {
                    appt.rule = null;
                    appt.recurrences = [];
                    filtered.push(appt);
                }
            }

            return filtered;
        });
    };

    appointment.getOffset = function(appt) {
        var start = moment.tz(appt.start, appt.timezone);
        return (start.hour() * 60) + start.minute();
    };

    appointment.getLength = function(appt) {
        var start = moment.tz(appt.start, appt.timezone);
        var finish = moment.tz(appt.finish, appt.timezone);
        return Math.abs(moment.duration(finish.diff(start)).asMinutes());
    };

    return appointment;
}]);
