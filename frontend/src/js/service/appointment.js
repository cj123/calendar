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

    appointmentFactory.delete = function(appointmentId, dateToDelete) {
        return $http.delete(API_BASE + "calendar/appointments/" + appointmentId, {
            data: {
                date: dateToDelete
            }
        });
    };

    return appointmentFactory;
}]);
