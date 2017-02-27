angular.module("calendar").controller("CalendarController", [
    "$scope", "$log", "$interval", "moment", "Item", "CalendarOptions",
    function($scope, $log, $interval, moment, Item, CalendarOptions) {
        $scope.currentDate = moment();
        $scope.monthStart = null;
        $scope.days = [];
        $scope.alarms = []; // array of date and appointment

        // watch the current date of the view for changes.
        $scope.$watch(function() {
            if ($scope.currentDate) {
                return $scope.currentDate.format("x");
            }
        }, function() {
            resetDayView();
        });

        $scope.$watch(function() {
            if ($scope.currentDate) {
                return $scope.currentDate.format("YYYYMM");
            }
        }, function() {
            monthDays($scope.currentDate);
        });

        $scope.$on("refresh", function() {
            monthDays($scope.currentDate).then(function() {
                resetDayView();
            });
        });

        function resetDayView() {
            CalendarOptions.get().then(function(response) {
                var opts = response.data;

                angular.element(document.getElementById("day-view")).scrollTop(60 * opts.DayviewTimeStart, 0);

                $log.debug("reloaded calendar options");
            });
        }

        function monthDays(anyDayInMonth) {
            $log.debug("refreshing month days " + $scope.currentDate.format("YYYYMM"));

            // reset alarms
            $scope.alarms = [];

            $scope.monthStart = anyDayInMonth.clone().date(1);
            var lastDayOfMonth = $scope.monthStart.clone().add(1, "month").subtract(1, "day");

            var days = [];

            for (var i = $scope.monthStart.date(); i <= lastDayOfMonth.date(); i++) {
                days.push({day: i, events: []});
            }

            return Item.get("appointment", $scope.monthStart, lastDayOfMonth).then(function(appts) {
                for (var apptIndex = 0; apptIndex < appts.length; apptIndex++) {
                    var appt = appts[apptIndex];

                    if (appt.recurrences.length > 0) {
                        for (var apptRecurrIndex = 0; apptRecurrIndex < appt.recurrences.length; apptRecurrIndex++) {
                            var recurrence = appt.recurrences[apptRecurrIndex];
                            var recurrenceDate = moment(recurrence);
                            var isDeleted = false;

                            if (appt.deleted && appt.deleted.length) {
                                for (var deletedIndex = 0; deletedIndex < appt.deleted.length; deletedIndex++) {
                                    var deletedDate = moment(appt.deleted[deletedIndex].date, "YYYY-MM-DDTHH:mm:ss");

                                    if (deletedDate.diff(recurrenceDate) === 0) {
                                        isDeleted = true;
                                    }
                                }

                                if (isDeleted) {
                                    continue;
                                }
                            }

                            days[recurrenceDate.date() - 1].events.push(appt);
                            populateAlarms(appt, recurrenceDate);
                        }
                    } else {
                        days[appt.start.date() - 1].events.push(appt);
                        populateAlarms(appt, appt.start);
                    }
                }
            }).then(function() {
                $scope.days = days;

                $log.debug($scope.alarms);
            });
        }

        /**
         * Attach alarms to scope
         * @param appt
         * @param momentDate
         */
        function populateAlarms(appt, momentDate) {
            if (appt.alarms && appt.alarms.length) {
                for (var alarmIndex = 0; alarmIndex < appt.alarms.length; alarmIndex++) {
                    $scope.alarms.push({id: appt.id, alert: momentDate.clone().subtract(appt.alarms[alarmIndex].time, 'minutes')});
                }
            }
        }

        // watch for alarms
        $interval(function() {
            if (!$scope.alarms) {
                console.log('no alarms');
                return;
            }

            var currentTime = moment();

            for (var alarmIndex = 0; alarmIndex < $scope.alarms.length; alarmIndex++) {
                if ($scope.alarms[alarmIndex].alert.isSame(currentTime, 'minute')) {
                    console.log("JEEEZUS CHRIST ALARM " + $scope.alarms[alarmIndex].id);

                    // @TODO alarm noise!
                    // @TODO open alarm popup
                } else {
                    console.log("invalid alarm time right now :(");
                }
            }
        }, 1000); // 1 minute
    }
]);
