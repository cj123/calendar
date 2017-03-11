angular.module("calendar").directive("repeats", [function() {
    return {
        restrict: "E",
        scope: {
            item: '='
        },
        templateUrl: "calendar/view/directives/repeats.html",
        controller: [
            "$scope", "Item",
            function($scope, Item) {
                $scope.recurRule = ICAL.Recur.fromData(ICAL.Recur._stringToData($scope.item.recurrence_rule, true));

                if (!$scope.recurRule.interval) {
                    $scope.recurRule.interval = 1;
                }

                $scope.byDay = $scope.recurRule.parts.BYDAY || [];
                $scope.byMonth = $scope.recurRule.parts.BYMONTH || [];

                /**
                 * Add value to array if it doesn't exist, remove it if it does.
                 * @param arr
                 * @param val
                 */
                $scope.toggleIndex = function(arr, val) {
                    var index = arr.indexOf(val);

                    if (index === -1) {
                        arr.push(val);
                    } else {
                        arr.splice(index, 1);
                    }
                };

                /**
                 * set frequency of recurrence rule
                 * (n.b. also clears out previous rule as frequency changes mean things need editing
                 *
                 * @param frequencyString
                 */
                $scope.setFrequency = function(frequencyString) {
                    if (frequencyString === 'NONE') {
                        $scope.recurRule = null;
                        return;
                    }

                    if (!$scope.recurRule) {
                        $scope.recurRule = ICAL.Recur.fromData({});
                    }

                    $scope.recurRule.freq = frequencyString;
                };


                /**********************************************************************
                 *
                 *                              Watches
                 *
                 **********************************************************************/
                $scope.$watch("byDay", function() {
                    console.log('da');
                    if ($scope.byDay.length < 1) {
                        //delete $scope.recurRule.parts.BYDAY;
                    } else {
                        $scope.recurRule.parts.BYDAY = $scope.byDay;
                    }

                    console.log($scope.recurRule);
                }, true);

                $scope.$watch("byMonth", function() {
                    console.log('mo');
                    if ($scope.byMonth.length < 1) {
                        //delete $scope.recurRule.parts.BYDAY;
                    } else {
                        $scope.recurRule.parts.BYMONTH = $scope.byMonth;
                    }
                }, true);


                // watch the recurrence rule for changes
                $scope.$watch(function() {
                    if ($scope.recurRule) {
                        return $scope.recurRule.toString();
                    }
                }, function() {
                    // set the parent rrule so it can be saved.
                    if ($scope.recurRule) {
                        $scope.item.recurrence_rule = $scope.recurRule.toString();

                        $scope.item.rule = rrulestr(Item.stripExDate($scope.item.recurrence_rule), {
                            dtstart: $scope.item.start.toDate()
                        });

                        $scope.description = $scope.item.rule.toText();
                    }
                });
            }
        ]
    };
}]);
