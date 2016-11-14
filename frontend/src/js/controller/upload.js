angular.module("calendar").controller("UploadController", [
    "$scope", "FileUploader", "API_BASE",
    function($scope, FileUploader, API_BASE) {
        $scope.uploader = new FileUploader({
            url: API_BASE + "calendar/import"
        });
    }
]);
