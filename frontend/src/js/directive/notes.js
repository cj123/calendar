angular.module("calendar").directive("notes", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '='
        },
        templateUrl: "calendar/view/directives/notes.html",
        controller: [
            "$scope", "$uibModal", "Item", "hotkeys",
            function($scope, $uibModal, Item, hotkeys) {
                $scope.notes = [];

                // watch the current date of the view for changes.
                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, function() {
                    loadNotes($scope.currentDate);
                });

                $scope.$on("refresh", function() {
                    loadNotes($scope.currentDate);
                });

                function loadNotes(date) {
                    if (!date) {
                        return;
                    }

                    Item.get("note", date.clone(), date.clone()).then(function(notes) {
                        $scope.notes = Item.removeDeletedDates(notes);
                    });
                }

                $scope.new = function() {
                    var start = $scope.currentDate.clone()
                        .hour(0)
                        .minute(0)
                        .second(0);

                    $scope.notes.push({
                        start: start,
                        finish: start.clone().hour(1).minute(0).second(0),
                        hilite: "always",
                        data_type: "note",
                        recurrence_rule: "",
                    });
                };
            }
        ]
    };
}]);
