angular.module("calendar").factory("Appointment", [ "$http", "moment", "API_BASE", function($http, moment, API_BASE) {
    var appointmentFactory = {};

    /**
     * Get Appointments for a given date range.
     * Sets time of startDate to be 00:00:00 and endDate to be 23:59:59.
     *
     * @param startDate
     * @param endDate
     * @returns {HttpPromise}
     */
    appointmentFactory.get = function(startDate, endDate) {
        return $http.get(API_BASE + "calendar/appointments", {
            params: {
                start: startDate.format("YYYY-MM-DD"),
                finish: endDate.format("YYYY-MM-DD")
            }
        });

        // @TODO here: mark conflicting appointments?
    };
    
    /**
     * Create an appointment.
     *
     * @param appointment
     * @returns {*}
     */
    appointmentFactory.create = function(appointment) {
        appointment.id = 0; // in the case we're duplicating appointments, don't pre-set the ID.
        return $http.post(API_BASE + "calendar/appointments", prepareAppointment(appointment));
    };

    /**
     * Updates an appointment
     *
     * @param appointment
     *
     * @returns {*}
     */
    appointmentFactory.update = function(appointment) {
        return $http.put(API_BASE + "calendar/appointment/" + appointment.id, prepareAppointment(appointment));
    };

    function prepareAppointment(a) {
        // @TODO process alarms into correct data structure!
        a.alarms = []; // @TODO alarms should be handled better!

        var hasUpdatedTime = false;

        if (!!a.startTime) {
            var start = moment(a.startTime);

            a.start.hours(start.hours()).minutes(start.minutes()).seconds(0);

            hasUpdatedTime = true;
        }

        if (!!a.finishTime) {
            var finish = moment(a.finishTime);

            a.finish.hours(finish.hours()).minutes(finish.minutes()).seconds(0);

            hasUpdatedTime = true;
        }

        // usually this occurs when the appointment has been dragged, not updated
        // in the modal.
        if (!hasUpdatedTime) {
            // take offset, set that up as the minutes and hours of the day.
            a.start = a.start.clone()
                .hour(0)
                .minute(0)
                .second(0)
                .add(a.offset, "minutes");

            // set the end as the start plus the length of appointment
            a.finish = a.start.clone().add(a.length, "minutes");
        }

        // @TODO: all day appointment check here.
        if (a.finish.isBefore(a.start) || a.finish.hour() !== 0 && a.finish.minute() !== 0 && a.start.date() !== a.finish.date()) {
            throw "invalid date given";
        }

        return a;
    }

    return appointmentFactory;
}]);
