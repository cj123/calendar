angular.module("calendar").controller("NavigationController", [
    "$scope", "$uibModal", "moment",
    function($scope, $uibModal, moment) {
        console.log('nav control');

        $scope.listView = function(time, period, useBeginningOf) {
            var start = moment();

            if (useBeginningOf) {
                start = start.startOf(period);
            }

            var end = start.clone().add(time, period);

            // open list modal with start and end.
            console.log(start, end);

            $uibModal.open({
                animation: true,
                templateUrl: "calendar/view/modals/list.html",
                controller: "ListModal",
                resolve: {
                    startDate: function() {
                        return start;
                    },
                    endDate: function() {
                        return end;
                    }
                }
            });
        };
    }
]);
