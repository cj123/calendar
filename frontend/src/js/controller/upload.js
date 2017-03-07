angular.module("calendar").controller("UploadController", [
    "$scope", "$log", "$stateParams", "Upload", "API_BASE",
    function($scope, $log, $stateParams, Upload, API_BASE) {
        $scope.uploadAttempt = false;
        $scope.uploadSuccess = false;
        $scope.calendarID = $stateParams.calendarID;

        $scope.submit = function() {
            if ($scope.import.file.$valid && $scope.file) {
                $scope.upload($scope.file);
            }
        };

        // upload on file select or drop
        $scope.upload = function(file) {
            Upload.upload({
                url: API_BASE + "calendar/" + $stateParams.calendarID + "/import",
                data: {file: file, 'format': $scope.calendarFormat}
            }).then(function(resp) {
                $scope.uploadSuccess = true;
                $scope.uploadAttempt = true;

                $log.debug("successfully imported " + $scope.calendarFormat + " calendar");
            }, function (resp) {
                $scope.uploadSuccess = false;
                $scope.uploadAttempt = true;

                $log.error(resp);
            });
        };
    }
]);
