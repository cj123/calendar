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
            "$scope", "$rootScope", "$document", "$uibModal", "Item", "Clipboard", "hotkeys",
            function($scope, $rootScope, $document, $uibModal, Item, Clipboard, hotkeys) {
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


                // hotkeys
                hotkeys.bindTo($scope)
                    .add({
                        combo: 'ctrl+x',
                        description: 'delete item',
                        allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
                        callback: function(event, hotkey) {
                            if (!$scope.info.active) {
                                return;
                            }

                            event.preventDefault();

                            Clipboard.put($scope.info);

                            var dateToDelete = null;

                            if ($scope.info.recurrences && $scope.info.recurrences.length > 0) {
                                dateToDelete = $scope.currentDate
                                    .clone()
                                    .hour($scope.info.start.hour())
                                    .minute($scope.info.start.minute())
                                    .second($scope.info.start.second())
                                    .millisecond(0)
                                ;
                            }

                            Item.delete($scope.info, dateToDelete).then(function() {
                                $rootScope.$broadcast("refresh", true);
                            });

                        }
                    })
                    .add({
                        combo: 'ctrl+c',
                        description: 'copy selected item',
                        allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
                        callback: function(event, hotkey) {
                            if (!$scope.info.active) {
                                return;
                            }
                            event.preventDefault();

                            Clipboard.put($scope.info);
                        }
                    })
                ;
            }
        ]
    };
}]);
