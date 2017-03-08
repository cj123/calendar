angular.module("calendar").directive("dayView", [function() {
    return {
        restrict: "E",
        scope: {
            currentDate: '=',
            days: '='
        },
        templateUrl: "calendar/view/directives/day-view.html",
        controller: [
            "$scope", "$log", "$uibModal", "CalendarOptions",
            function($scope, $log, $uibModal, CalendarOptions) {
                $scope.opts = {};

                CalendarOptions.get().then(function(response) {
                    $scope.opts = response.data;
                });

                function loadAppointments() {
                    if ($scope.days.length === 0) {
                        return;
                    }

                    var events = $scope.days[$scope.currentDate.date() - 1].events;

                    for (var eventIndex = 0; eventIndex < events.length; eventIndex++) {
                        for (var otherEventIndex = 0; otherEventIndex  < events.length; otherEventIndex++) {
                            if (eventIndex === otherEventIndex) {
                                continue;
                            }
                            
                            var event = events[eventIndex];
                            var otherEvent = events[otherEventIndex];

                            if (event.start.isBetween(otherEvent.start, otherEvent.finish, 'second', '[]')) {
                                if (!event.collisions[otherEvent.id]) {
                                    event.collisions.push(otherEvent.id);
                                }

                                if (!otherEvent.collisions[event.id]) {
                                    otherEvent.collisions.push(event.id);
                                }
                            }

                            console.log(event);
                        }
                    }

                    $scope.appointments = events;
                }

                $scope.$watch(function() {
                    if ($scope.currentDate) {
                        return $scope.currentDate.format("x");
                    }
                }, loadAppointments);

                $scope.$watch("days", loadAppointments);

                $scope.createAppointment = function(event) {
                    var offset = event.offsetY - (event.offsetY % 30); // rounded to nearest 30min

                    var start = $scope.currentDate.clone()
                        .hour(0)
                        .minute(0)
                        .second(0)
                        .add(offset, "minutes");

                    $scope.appointments.push({
                        offset: offset,
                        length: 30,
                        start: start,
                        finish: start.clone().add(30, "minutes"),
                        data_type: "appointment",
                        alarms: $scope.opts.DefaultAlarms
                    });
                };

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
            }
        ]
    };
}]);
