angular.module("calendar").directive("notes", [function() {
    return {
        restrict: "E",
        scope: {},
        templateUrl: "calendar/view/notes.html",
        controller: ["$scope", "$uibModal", "Note", "CalendarData", function($scope, $uibModal, Note, CalendarData) {
            $scope.currentDate = CalendarData.currentDate;
            $scope.notes = [];

            // watch the current date of the view for changes.
            $scope.$watch(function() {
                if (CalendarData.currentDate) {
                    return CalendarData.currentDate.format("x");
                }
            }, function() {
                loadNotes(CalendarData.currentDate);
            });

            function loadNotes(date) {
                if (!date) {
                    return;
                }

                Note.getNotes(date).then(function(response) {
                    $scope.notes = response.data;
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
        }]
    };
}]);
