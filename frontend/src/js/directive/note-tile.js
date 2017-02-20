angular.module("calendar").directive("noteTile", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            info: '=',
            isNew: '='
        },
        templateUrl: "calendar/view/note-tile.html",
        controller: [
            "$scope", "$document", "$uibModal", "Item",
            function($scope, $document, $uibModal, Item) {
                $scope.viewDetail = function() {
                    $uibModal.open({
                        animation: true,
                        templateUrl: "calendar/view/modals/item.html",
                        controller: "ItemModal",
                        resolve: {
                            item: function() {
                                return $scope.info;
                            },
                            currentDate: function() {
                                return $scope.currentDate;
                            }
                        }
                    });
                };

                $scope.update = function() {
                    if (!!$scope.info.id) {
                        // this is an update request
                    } else {
                        // this is a create request
                        Item.create("note", $scope.info).then(function(response) {
                            console.log(response);

                            $scope.$emit("refresh", true);
                        });
                    }
                };

                $scope.delete = function() {
                    Item.delete($scope.info, null).then(function(response) {
                        $scope.$emit("refresh", true);
                    });
                };
            }
        ]
    };
}]);
