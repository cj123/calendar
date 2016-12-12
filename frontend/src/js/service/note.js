angular.module("calendar").factory("Note", [ "Item", "$http", "API_BASE", function(Item, $http, API_BASE) {
    var noteFactory = {};

    /**
     * Get Notes.
     *
     * @param date
     * @returns {*}
     */
    noteFactory.getNotes = function(date) {
        return $http.get(API_BASE + "calendar/notes", {
            params: {
                date: date.format("Y-M-D")
            }
        }).then(function(response) {
            return Item.filterBetweenDates(response.data, date, date.clone());
        });
    };

    return noteFactory;
}]);
