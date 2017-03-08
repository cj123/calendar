angular.module("calendar").controller("ListModal", [
    "$scope", "$rootScope", "$uibModalInstance", "$log", "Item", "startDate", "endDate", "moment",
    function($scope, $rootScope, $uibModalInstance, $log, Item, startDate, endDate, moment) {
        $scope.startDate = startDate;
        $scope.endDate = endDate;

        $scope.appointments = {};

        Item.get("appointment", startDate, endDate).then(function(appts) {
            for (var index = 0; index < appts.length; index++) {
                var appt = appts[index];

                if (appt.recurrences.length > 0) {
                    for (var recurrIndex = 0; recurrIndex < appt.recurrences.length; recurrIndex++) {
                        var recurrDate = moment(appt.recurrences[recurrIndex]);

                        // only add in recurrences which fit in our range
                        if (!recurrDate.isBetween(startDate, endDate, null, '[]')) {
                            continue;
                        }

                        addAppointment(recurrDate, appt);
                    }
                } else {
                    addAppointment(appt.start, appt);
                }
            }
        });

        function addAppointment(date, appointment) {
            var d = date.format("YYYY-MM-DD");

            if (!$scope.appointments[d]) {
                $scope.appointments[d] = [];
            }

            $scope.appointments[d].push(appointment);
        }
        console.log($scope.appointments);

        $scope.close = function() {
            $uibModalInstance.dismiss("close");
        };
    }
]);
