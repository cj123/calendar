angular.module("calendar").directive("appointmentTile", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            info: '=',
            isNew: '='
        },
        templateUrl: "calendar/view/tiles/appointment-tile.html",
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

                $scope.$on("angular-resizable.resizeEnd", function(evt, args) {
                    if (args.height) {
                        $log.debug("Item resize, adjusting length/offset accordingly.");

                        var length = args.height - (args.height % 5);

                        if (length + $scope.info.offset > 1440) {
                            // illegal resize, max it.
                            length = 1440 - $scope.info.offset;
                        }

                        // change the appointment
                        $scope.info.length = length;

                        $scope.info.start = $scope.info.start.hour(0).minute(0).second(0).add($scope.info.offset, 'minutes');
                        $scope.info.finish = $scope.info.start.clone().add(length, 'minutes');

                        console.log($scope.info);

                        $scope.submit(false);
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
