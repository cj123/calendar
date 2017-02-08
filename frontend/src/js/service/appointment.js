angular.module("calendar").factory("Appointment", [ "Item", "$http", "moment", "API_BASE", function(Item, $http, moment, API_BASE) {
    var appointmentFactory = {};

    /**
     * Get Appointments for a given date range.
     * Sets time of startDate to be 00:00:00 and endDate to be 23:59:59.
     *
     * @param startDate
     * @param endDate
     * @returns {HttpPromise}
     */
    appointmentFactory.getAppointments = function(startDate, endDate) {
        return $http.get(API_BASE + "calendar/appointments", {
            params: {
                start: startDate.format("YYYY-MM-DD"),
                finish: endDate.format("YYYY-MM-DD")
            }
        }).then(function(response) {
            return Item.filterBetweenDates(response.data, startDate, endDate);
        });
    };

    /**
     * Delete an appointment. if dateToDelete === null, all occurrences are deleted
     *
     * @param appointmentId
     * @param dateToDelete
     * @returns {*|boolean}
     */
    appointmentFactory.delete = function(appointmentId, dateToDelete) {
        return $http.delete(API_BASE + "calendar/appointment/" + appointmentId, {
            data: {
                date: dateToDelete !== null && dateToDelete.toISOString() || moment().toISOString(),
                delete_all: dateToDelete === null
            }
        });
    };

    /**
     * Create an appointment.
     *
     * @param appointment
     * @returns {*}
     */
    appointmentFactory.create = function(appointment) {
        // @todo we may need to modify our representation of the appointment here so the payload succeeds validation
        appointment.alarms = []; // @TODO alarms should be handled better!

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
        // @TODO process alarms into correct data structure!
        appointment.alarms = []; // @TODO alarms should be handled better!

        if (!!appointment.offset) {
            // parse this and set the start time of the appointment
        }

        if (!!appointment.length) {
            // parse this given the start time to get the end time
        }

        return $http.put(API_BASE + "calendar/appointment/" + appointment.id, prepareAppointment(appointment));
    };

    function prepareAppointment(a) {
        a.start = a.start.clone()
            .hour(0)
            .minute(0)
            .second(0)
            .add(a.offset, "minutes");

        a.finish = a.start.clone().add(a.length, "minutes");

        return a;
    }

    return appointmentFactory;
}]);
