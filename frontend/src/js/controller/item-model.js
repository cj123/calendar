angular.module("calendar").controller("ItemModal", [
    "$scope", "$rootScope", "$uibModalInstance", "$log", "item", "currentDate", "Item", "CalendarOptions", "moment",
    function($scope, $rootScope, $uibModalInstance, $log, item, currentDate, Item, UserOptions, moment) {
        $scope.item = item;

        UserOptions.getAndMergeWithItem(item).then(function(mergedItem) {
            $scope.item = mergedItem;
        });

        $scope.cancel = function() {
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
                Item.create($scope.item).then(function(response) {
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
                var id = $scope.item.id;

                Item.create($scope.item).then(function(response) {
                    if (response.status === 201) {
                        $scope.item.id = id;

                        $scope.delete(false);
                    } else {
                        console.log(response);
                    }
                }).catch(function(err) {
                    console.log(err);
                });
            }
        };
    }
]);
