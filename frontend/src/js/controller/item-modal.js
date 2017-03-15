angular.module("calendar").controller("ItemModal", [
    "$scope", "$rootScope", "$uibModalInstance", "$log", "item", "currentDate", "Item", "CalendarOptions", "moment",
    function($scope, $rootScope, $uibModalInstance, $log, item, currentDate, Item, UserOptions, moment) {
        $scope.item = item;
        $scope.item.startTime = $scope.item.start.toDate();
        $scope.item.finishTime = $scope.item.finish.toDate();

        var itemClone = JSON.parse(JSON.stringify(item));
        $scope.itemClone = itemClone;

        UserOptions.getAndMergeWithItem(item).then(function(mergedItem) {
            $scope.item = mergedItem;
        });

        UserOptions.getAllCalendars().then(function(response) {
            $scope.calendars = response.data;
        });

        $scope.cancel = function() {
            $scope.item = itemClone;
            $uibModalInstance.dismiss("close");
        };

        $scope.delete = function(deleteRecurrences) {
            if (!$scope.item.id) {
                $uibModalInstance.close($scope.item);
                $rootScope.$broadcast("refresh", true);
                return;
            }

            var dateToDelete = null;

            if (!deleteRecurrences) {
                dateToDelete = currentDate
                    .clone()
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
            console.log($scope.item);

            if ($scope.item.recurrence_rule === 'FREQ=null') {
                $scope.item.recurrence_rule = "";
            }


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
                $log.debug("cloning item id: " + $scope.item.uid);

                $scope.item.recurrence_rule = "";
                $scope.item.uid = ""; // unset UID, allow it to be recreated

                // set the date of the appointment to today since we've cleared out the recurrence rule
                $scope.item.start.year(currentDate.year()).month(currentDate.month()).date(currentDate.date());
                $scope.item.finish.year(currentDate.year()).month(currentDate.month()).date(currentDate.date());

                // create a new appointment with the current item, then
                // delete the recurrence of the old one.
                Item.create($scope.item.data_type, $scope.item).then(function(newItem) {
                    $scope.item = newItem;

                    $log.debug("created item id: " + $scope.item.uid);

                    var dateToDelete = currentDate
                        .hour(item.start.hour())
                        .minute(item.start.minute())
                        .second(item.start.second())
                        .millisecond(0)
                    ;

                    Item.delete(itemClone, dateToDelete).then(function(response) {
                        if (response.status === 200) {
                            //$scope.item = itemClone; // put back item clone to prevent changes to appointment
                            $log.debug("deleted item id: " + $scope.item.uid);

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
            if (!($scope.item.alarms instanceof Array)) {
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
