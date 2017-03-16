angular.module("calendar").controller("CalendarController", [
    "$scope", "$log", "$interval", "$uibModal", "$document", "$stateParams", "moment", "Item", "CalendarOptions", "Clipboard", "hotkeys",
    function($scope, $log, $interval, $uibModal, $document, $stateParams, moment, Item, CalendarOptions, Clipboard, hotkeys) {
        $scope.currentDate = moment.tz(moment.tz.guess());
        $scope.monthStart = null;
        $scope.days = [];
        $scope.alarms = []; // array of date and appointment
        $scope.opts = {};

        $scope.calendarID = $stateParams.calendarID;
        Item.setCalendarID($stateParams.calendarID);
        CalendarOptions.setCalendarID($stateParams.calendarID);

        // watch the current date of the view for changes.
        $scope.$watch(function() {
            if ($scope.currentDate) {
                return $scope.currentDate.format("x");
            }
        }, function() {
            resetDayView(true);
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
                resetDayView(false);
            });
        });

        function resetDayView(scroll) {
            $document[0].title = "Calendar - " + $scope.currentDate.format("DD/MM/YYYY");

            CalendarOptions.get().then(function(response) {
                $scope.opts = response.data;

                if (scroll) {
                    angular.element(document.getElementById("day-view")).scrollTop(60 * $scope.opts.DayviewTimeStart, 0);
                }

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

                                    if (deletedDate.isSame(recurrenceDate, 'day')) {
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
                    $scope.alarms.push({
                        alert: momentDate.clone().subtract(appt.alarms[alarmIndex].time, 'minutes'),
                        time: appt.alarms[alarmIndex].time,
                        appointment: appt
                    });
                }
            }
        }

        $scope.activeAlarms = [];

        // watch for alarms
        $interval(function() {
            if (!$scope.alarms) {
                return;
            }

            var currentTime = moment();

            for (var alarmIndex = 0; alarmIndex < $scope.alarms.length; alarmIndex++) {
                if ($scope.alarms[alarmIndex].alert.isSame(currentTime, 'minute')) {
                    console.log("ALARM " + $scope.alarms[alarmIndex].id);

                    // add to active alarms
                    $scope.activeAlarms.push($scope.alarms[alarmIndex]);
                    triggerAlarm();

                    // remove alarm from index
                    $scope.alarms.splice(alarmIndex, 1);
                }
            }
        }, 1000); // 1 minute

        var audio = new Audio('/assets/sounds/alert.wav');

        function triggerAlarm() {
            if ($scope.opts.IgnoreAlarms) {
                return;
            }

            audio.play();

            $uibModal.open({
                animation: true,
                templateUrl: "calendar/view/modals/alarm.html",
                controller: "AlarmModal",
                resolve: {
                    activeAlarms: function() {
                        return $scope.activeAlarms;
                    },
                    currentDate: function() {
                        return $scope.currentDate;
                    }
                }
            });
        }


        // hotkeys
        hotkeys.bindTo($scope)
            .add({
                combo: 'ctrl+v',
                description: 'paste item',
                callback: function() {
                    var item = Clipboard.get();

                    if (!item) {
                        return;
                    }

                    item.id = 0;
                    item.uid = "";
                    item.start.year($scope.currentDate.year()).month($scope.currentDate.month()).date($scope.currentDate.date());
                    item.finish.year($scope.currentDate.year()).month($scope.currentDate.month()).date($scope.currentDate.date());

                    Item.create(item.data_type, item).then(function(data) {
                        $scope.$broadcast("refresh", true);
                    });
                }
            })
        ;
    }
]);
