angular.module("calendar").directive("appointmentTile", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            info: '=',
            isNew: '='
        },
        templateUrl: "calendar/view/appointment-tile.html",
        controller: [
            "$scope", "$document", "$uibModal", "Item",
            function($scope, $document, $uibModal, Item) {
                $scope.active = false;

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

                $scope.$on("angular-resizable.resizing", function(evt, args) {
                    console.log(evt, args);
                    console.log(args.evt);
                });

                $scope.$on("angular-resizable.resizeEnd", function(evt, args) {
                    if (args.width) {
                        // @TODO dunno yet, probably save this and xOffset somewhere
                    }

                    if (args.height) {
                        console.log("I should update here");
                        // change the appointment
                        $scope.info.length = args.height - (args.height % 5);

                        $scope.submit();
                    }
                });

                $scope.submit = function(reload) {
                    if (!$scope.info) {
                        return;
                    }

                    if (!$scope.info.id) {
                        Item.create("appointment", $scope.info).then(function(response) {
                            $scope.info = response.data;

                            if (reload) {
                                $scope.$emit("refresh", true);
                            }
                        }).catch(function(err) {
                            console.log(err);
                        });
                    } else {
                        // update here
                        Item.update($scope.info).then(function(response) {
                            console.log(response);

                            if (reload) {
                                $scope.$emit("refresh", true);
                            }
                        }).catch(function(err) {
                            console.log(err);
                        });
                    }
                };
            }
        ]
    };
}]);
