angular.module("calendar").directive("noteTile", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            info: '=',
            isNew: '='
        },
        templateUrl: "calendar/view/tiles/note-tile.html",
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

                $scope.update = function(reload) {
                    if (!!$scope.info.id) {
                        // this is an update request
                        Item.update($scope.info).then(function(response) {
                            console.log(response);

                            if (reload) {
                                $scope.$emit("refresh", true);
                            }
                        });
                    } else {
                        // this is a create request
                        Item.create("note", $scope.info).then(function(response) {
                            $scope.info = response.data;
                        });
                    }
                };

                $scope.delete = function() {
                    if (!$scope.info.id) {
                        $scope.$emit("refresh", true);
                        return;
                    }

                    Item.delete($scope.info, null).then(function(response) {
                        $scope.$emit("refresh", true);
                    });
                };
            }
        ]
    };
}]);
