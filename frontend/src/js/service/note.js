angular.module("calendar").factory("Note", [ "$http", "API_BASE", function($http, API_BASE) {
    var note = {};

    /**
     * Get Calendar Notes
     * @param date
     * @returns {HttpPromise}
     */
    note.getNotes = function(date) {
        return $http.get(API_BASE + "calendar/notes", {
            params: {
                date: date.format("Y-M-D")
            }
        });
    };

    return note;
}]);
