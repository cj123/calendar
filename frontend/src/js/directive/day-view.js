angular.module("calendar").directive("dayView", [function() {
    return {
        restrict: "E",
        scope: {},
        templateUrl: "calendar/view/day-view.html",
        controller: ["$scope", "$document", "$uibModal", "Appointment", "CalendarData", "CalendarOptions", function($scope, $document, $uibModal, Appointment, CalendarData, CalendarOptions) {
            $scope.currentDate = CalendarData.currentDate;
            $scope.appointments = [];

            // watch the current date of the view for changes.
            $scope.$watch(function() {
                if (CalendarData.currentDate) {
                    return CalendarData.currentDate.format("x");
                }
            }, function() {
                loadAppointments(CalendarData.currentDate);
            });

            function loadAppointments(date) {
                if (!date) {
                    return;
                }

                Appointment.getAppointments(date).then(function(appointments) {
                    $scope.appointments = appointments;

                    CalendarOptions.get().then(function(response) {
                        var opts = response.data;

                        angular.element(document.getElementById("day-view")).scrollTop(60 * opts.DayviewTimeStart, 0);
                    });
                });
            }

            $scope.viewAppointmentDetail = function(appointment) {
                $uibModal.open({
                    animation: true,
                    templateUrl: "calendar/view/modals/appointment.html",
                    controller: "AppointmentModal",
                    resolve: {
                        appointment: function() {
                            return appointment;
                        }
                    }
                });
            };
        }]
    };
}]);
