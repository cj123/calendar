angular.module("calendar").factory("Appointment", [ "$http", "moment", "API_BASE", function($http, moment, API_BASE) {
    var appointmentFactory = {};

    /**
     * Get Appointments for a given date range.
     * Sets time of startDate to be 00:00:00 and endDate to be 23:59:59.
     *
     * @param calID
     * @param startDate
     * @param endDate
     * @returns {HttpPromise}
     */
    appointmentFactory.get = function(calID, startDate, endDate) {
        return $http.get(API_BASE + "calendar/" + calID + "/appointments", {
            params: {
                start: startDate.format("YYYY-MM-DD"),
                finish: endDate.format("YYYY-MM-DD")
            }
        });
    };
    
    /**
     * Create an appointment.
     *
     * @param calID
     * @param appointment
     * @returns {*}
     */
    appointmentFactory.create = function(calID, appointment) {
        appointment.id = 0; // in the case we're duplicating appointments, don't pre-set the ID.
        appointment.uid = "";
        return $http.post(API_BASE + "calendar/" + calID + "/appointments", prepareAppointment(appointment));
    };

    /**
     * Updates an appointment
     *
     * @param calID
     * @param appointment
     *
     * @returns {*}
     */
    appointmentFactory.update = function(calID, appointment) {
        return $http.put(API_BASE + "calendar/" + calID + "/appointment/" + appointment.id, prepareAppointment(appointment));
    };

    function prepareAppointment(a) {
        var hasUpdatedTime = false;

        if (!!a.startTime) {
            a.start = moment(a.startTime).local().utcOffset(0, true);
            //a.start = a.start.clone().hours(a.startTime.getHours()).minutes(a.startTime.getMinutes()).seconds(0).utc();

            hasUpdatedTime = true;
        }

        if (!!a.finishTime) {
            console.log("finish", a.finishTime.getHours(), a.finishTime.getMinutes());

            //a.finish = a.finish.clone().hours(a.finishTime.getHours()).minutes(a.finishTime.getMinutes()).seconds(0).utc();
            a.finish = moment(a.finishTime).local().utcOffset(0, true);


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
                .add(a.offset, "minutes").utcOffset(0, true);

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
