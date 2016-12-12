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
                start: startDate.format("Y-M-D"),
                finish: endDate.format("Y-M-D")
            }
        }).then(function(response) {
            return Item.filterBetweenDates(response.data, startDate, endDate);
        });
    };

    return appointmentFactory;
}]);
