angular.module("calendar").directive("dayView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            days: '='
        },
        templateUrl: "calendar/view/directives/day-view.html",
        controller: [
            "$scope", "$log", "$interval", "$uibModal", "$stateParams", "Item", "CalendarOptions", "Collisions", "Clipboard", "hotkeys",
            function($scope, $log, $interval, $uibModal, $stateParams, Item, CalendarOptions, Collisions, Clipboard, hotkeys) {
                $scope.opts = {};
                $scope.timelinePosition = null;

                CalendarOptions.get().then(function(response) {
                    $scope.opts = response.data;
                });

                function loadAppointments() {
                    if ($scope.days.length === 0) {
                        return;
                    }
                    lastIndex = null;

                    var events = $scope.days[$scope.currentDate.date() - 1].events;
                    $scope.appointments = Collisions.calculateCollisions(events);
                }

                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, loadAppointments);

                $scope.$watch("days", loadAppointments);

                /**
                 * Create an appointment at the position on the day view pane.
                 *
                 * @param event
                 */
                $scope.createAppointment = function(event) {
                    var offset = event.offsetY - (event.offsetY % 30); // rounded to nearest 30min
                    console.log($scope.currentDate);

                    var start = moment.tz($scope.currentDate, $scope.opts.Timezone).hour(0).minute(0).second(0).millisecond(0).add(offset, "minutes");
                    console.log($scope.opts.DefaultAlarms);

                    $scope.appointments.push({
                        offset: offset,
                        length: 30,
                        start: start,
                        finish: start.clone().add(30, "minutes"),
                        data_type: "appointment",
                        alarms: $scope.opts.DefaultAlarms,
                        hilite: 'always',
                        recurrence_rule: '',
                        calendar_id: parseInt($stateParams.calendarID)
                    });
                };

                /**
                 * Trigger create an appointment from a button, simulating the double click event
                 * e.g. for mobile devices.
                 */
                $scope.createAppointmentButton = function() {
                    $scope.createAppointment({offsetY: 600});

                    var appointment = $scope.appointments[$scope.appointments.length - 1];

                    $uibModal.open({
                        animation: true,
                        templateUrl: "calendar/view/modals/item.html",
                        controller: "ItemModal",
                        resolve: {
                            item: function() {
                                return appointment;
                            },
                            currentDate: function() {
                                return $scope.currentDate;
                            }
                        }
                    });
                };

                $interval(function() {
                    var d = moment();

                    if (!d.isSame($scope.currentDate, "day")) {
                        $scope.timelinePosition = null;
                        return;
                    }

                    $scope.timelinePosition = d.minutes() + (d.hour() * 60);
                }, 1000); // 1 minute

                var lastIndex = null;

                // hotkeys
                hotkeys.bindTo($scope)
                    .add({
                        combo: 'n',
                        description: 'cycle through items',
                        callback: function(event, a) {
                            event.preventDefault();

                            if (lastIndex === null) {
                                lastIndex = 0;
                            } else {
                                $scope.appointments[lastIndex].active = false;
                                lastIndex = (lastIndex + 1) % $scope.appointments.length;
                            }

                            $scope.appointments[lastIndex].active = true;
                        }
                    })
                ;
            }
        ]
    };
}]);
