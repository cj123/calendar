angular.module("calendar").factory("Note", [ "$http", "API_BASE", function($http, API_BASE) {
    var note = {};

    note.getNotes = function(date) {
        return $http.get(API_BASE + "ajax/notes", {
            params: {
                date: date.format("Y-M-D")
            }
        });
    };

    return note;
}]);
