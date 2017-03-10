angular.module("calendar").directive("notes", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '='
        },
        templateUrl: "calendar/view/directives/notes.html",
        controller: [
            "$scope", "$uibModal", "Item",
            function($scope, $uibModal, Item) {
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
                        $scope.notes = notes;
                    });
                }

                $scope.new = function() {
                    var start = $scope.currentDate.clone()
                        .hour(0)
                        .minute(0)
                        .second(0);

                    $scope.notes.push({
                        start: start,
                        finish: start,
                        hilite: "always",
                        data_type: "note"
                    });
                };
            }
        ]
    };
}]);
