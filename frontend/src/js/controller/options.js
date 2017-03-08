angular.module("calendar").controller("OptionsController", [
    "$scope", "$stateParams", "CalendarOptions",
    function($scope, $stateParams, CalendarOptions) {
        $scope.opts = {};
        $scope.message = "";

        CalendarOptions.setCalendarID($stateParams.calendarID);

        CalendarOptions.get().then(function(response) {
            $scope.opts = response.data;
        });

        $scope.save = function() {
            CalendarOptions.update($scope.opts).then(function(response) {
                if (response.status === 200) {
                    $scope.message = "Your options have successfully been saved";
                } else {
                    $scope.message = "Failed to save options";
                }
            }).catch(function(err) {
                $scope.message = "Failed to save options";
            });
        };

        $scope.addAlarm = function() {
            if (!($scope.opts.DefaultAlarms instanceof Array)) {
                $scope.opts.DefaultAlarms = [];
            }

            $scope.opts.DefaultAlarms.push({
                time: 10
            });
        };

        $scope.removeAlarm = function(index) {
            $scope.opts.DefaultAlarms.splice(index, 1);
        };
    }
]);