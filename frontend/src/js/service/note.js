angular.module("calendar").factory("Note", [ "$http", "API_BASE", function($http, API_BASE) {
    var noteFactory = {};

    /**
     * Get Notes.
     *
     * @param start
     * @param end
     * @returns {*}
     */
    noteFactory.get = function(start, end) {
        return $http.get(API_BASE + "calendar/notes", {
            params: {
                start: start.format("YYYY-MM-DD"),
                finish: end.format("YYYY-MM-DD")
            }
        });
    };

    /**
     * Create a note.
     *
     * @param note
     * @returns {*}
     */
    noteFactory.create = function(note) {
        note.id = 0; // in the case we're duplicating notes, don't pre-set the ID.
        return $http.post(API_BASE + "calendar/notes", note);
    };

    /**
     * Update a note.
     *
     * @param note
     * @returns {*}
     */
    noteFactory.update = function(note) {
        return $http.put(API_BASE + "calendar/note/" + note.id, note);
    };

    return noteFactory;
}]);
