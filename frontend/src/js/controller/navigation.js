angular.module("calendar").controller("NavigationController", [
    "$scope", "$uibModal", "$rootScope", "moment",
    function($scope, $uibModal, $rootScope, moment) {
        console.log('nav control');

        $scope.listView = function(time, period, useBeginningOf) {
            var start = moment();

            if (useBeginningOf) {
                start = start.startOf(period);
            }

            var end = start.clone().add(time, period);

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

        $scope.refresh = function() {
            $rootScope.$broadcast("refresh", true);
        };
    }
]);
