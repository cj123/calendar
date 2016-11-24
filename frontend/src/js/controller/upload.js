angular.module("calendar").controller("UploadController", [
    "$scope", "Upload", "API_BASE",
    function($scope, Upload, API_BASE) {
        $scope.uploadAttempt = false;
        $scope.uploadSuccess = false;

        $scope.submit = function() {
            if ($scope.import.file.$valid && $scope.file) {
                $scope.upload($scope.file);
            }
        };

        // upload on file select or drop
        $scope.upload = function(file) {
            Upload.upload({
                url: API_BASE + "calendar/import",
                data: {file: file, 'format': $scope.calendarFormat}
            }).then(function(resp) {
                $scope.uploadSuccess = true;
                $scope.uploadAttempt = true;
            }, function (resp) {
                $scope.uploadSuccess = false;
                $scope.uploadAttempt = true;
            });
        };
    }
]);
