angular.module("calendar").directive("notes", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '='
        },
        templateUrl: "calendar/view/notes.html",
        controller: [
            "$scope", "$uibModal", "Note",
            function($scope, $uibModal, Note) {
                $scope.notes = [];

                // watch the current date of the view for changes.
                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, function() {
                    loadNotes($scope.currentDate);
                });

                function loadNotes(date) {
                    if (!date) {
                        return;
                    }

                    Note.getNotes(date.clone()).then(function(notes) {
                        $scope.notes = notes;
                    });
                }

                $scope.viewNoteDetail = function(note) {
                    $uibModal.open({
                        animation: true,
                        templateUrl: "calendar/view/modals/appointment.html",
                        controller: "AppointmentModal",
                        resolve: {
                            appointment: function() {
                                return note;
                            }
                        }
                    });
                };
            }
        ]
    };
}]);
