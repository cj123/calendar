angular.module("calendar").factory("UserOptions", [ "$http", "API_BASE", function($http, API_BASE) {
    var userOptions = {};

    /**
     * Get User Options (including defaults)
     *
     * @returns {HttpPromise}
     */
    userOptions.get = function() {
        return $http.get(API_BASE + "user/options", {
            cache: true
        });
    };

    return userOptions;
}]);
