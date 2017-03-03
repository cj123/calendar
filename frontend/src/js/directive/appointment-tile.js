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
            "$scope", "$document", "$uibModal", "$log", "Item",
            function($scope, $document, $uibModal, $log, Item) {
                $scope.active = false;
                $scope.calendarID = $scope.$parent.calendarID;

                $scope.viewDetail = function() {
                    $scope.active = false;

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
                        $log.debug("Item resize, adjusting length/offset accordingly.");
                        // change the appointment
                        $scope.info.length = args.height - (args.height % 5);

                        $scope.submit();
                    }
                });

                $scope.submit = function(reload) {
                    if (!$scope.info || !$scope.info.text) {
                        return;
                    }

                    if (!$scope.info.id) {
                        $log.debug("creating an appointment for the first time");

                        Item.create("appointment", $scope.info).then(function(data) {
                            $scope.info = data;

                            if (reload) {
                                $scope.$emit("refresh", true);
                            }
                        }).catch(function(err) {
                            $log.error(err);
                        });
                    } else {
                        $log.debug("updating an appointment");

                        // update here
                        Item.update($scope.info).then(function(response) {
                            $log.debug(response);

                            if (reload) {
                                $scope.$emit("refresh", true);
                            }
                        }).catch(function(err) {
                            $log.error(err);
                        });
                    }
                };
            }
        ]
    };
}]);
