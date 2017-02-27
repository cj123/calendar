angular.module("calendar").controller("ItemModal", [
    "$scope", "$rootScope", "$uibModalInstance", "$log", "item", "currentDate", "Item", "CalendarOptions", "moment",
    function($scope, $rootScope, $uibModalInstance, $log, item, currentDate, Item, UserOptions, moment) {
        $scope.item = item;
        var itemClone = JSON.parse(JSON.stringify(item));

        UserOptions.getAndMergeWithItem(item).then(function(mergedItem) {
            $scope.item = mergedItem;
        });

        $scope.cancel = function() {
            $scope.item = itemClone;
            $uibModalInstance.dismiss("cancel");
        };

        $scope.delete = function(deleteRecurrences) {
            var dateToDelete = null;

            if (!deleteRecurrences) {
                dateToDelete = currentDate
                    .hour(item.start.hour())
                    .minute(item.start.minute())
                    .second(item.start.second())
                    .millisecond(0)
                ;
            }

            return Item.delete($scope.item, dateToDelete).then(function(response) {
                if (response.status === 200) {
                    // reset the view
                    currentDate = moment();

                    $rootScope.$broadcast("refresh", true);
                    // close the modal
                    $uibModalInstance.close($scope.item);
                } else {
                    $log.error("invalid deletion status: ", response);
                }
            });
        };

        $scope.update = function(updateAllItems) {
            if (!$scope.item.id) {
                $log.debug("creating item from modal");

                // need to create the item
                Item.create($scope.item.data_type, $scope.item).then(function(response) {
                    $uibModalInstance.close($scope.item);
                    $rootScope.$broadcast("refresh", true);
                }).catch(function(err) {
                    console.log(err);
                });
            } else if (updateAllItems) {
                $log.debug("updating all items from modal");

                Item.update($scope.item).then(function(response) {
                    if (response.status === 200) {
                        $uibModalInstance.close($scope.item);
                        $rootScope.$broadcast("refresh", true);
                    } else {
                        $log.error("invalid update status: ", response);
                    }
                });
            } else {
                $scope.item.recurrence_rule = "";

                // create a new appointment with the current item, then
                // delete the recurrence of the old one.
                Item.create($scope.item.data_type, $scope.item).then(function(newItem) {
                    var dateToDelete = currentDate
                        .hour(item.start.hour())
                        .minute(item.start.minute())
                        .second(item.start.second())
                        .millisecond(0)
                    ;

                    Item.delete($scope.item, dateToDelete).then(function(response) {
                        $log.debug(response);

                        if (response.status === 200) {
                            $scope.item = itemClone; // put back item clone to prevent changes to appointment

                            $uibModalInstance.close($scope.item);
                            $rootScope.$broadcast("refresh", true);
                        }
                    });
                }).catch(function(err) {
                    console.log(err);
                });
            }
        };

        $scope.addAlarm = function() {
            if (!$scope.item.alarms) {
                $scope.item.alarms = [];
            }

            $scope.item.alarms.push({
                time: 10, // @TODO separate to const
                appointment_id: $scope.item.id
            });
        };

        $scope.removeAlarm = function(index) {
            $scope.item.alarms.splice(index, 1);
        };
    }
]);
