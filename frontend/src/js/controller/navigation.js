angular.module("calendar").controller("NavigationController", [
    "$scope", "$uibModal", "$rootScope", "moment", "CalendarOptions",
    function($scope, $uibModal, $rootScope, moment, CalendarOptions) {
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

        $scope.calendars = [];

        CalendarOptions.getAllCalendars().then(function(response) {
            $scope.calendars = response.data;
        });

        $scope.refresh = function() {
            $rootScope.$broadcast("refresh", true);
        };
    }
]);
