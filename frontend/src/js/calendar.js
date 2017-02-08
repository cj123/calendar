var calendar = angular.module("calendar", [
    "angularMoment",
    "templates",
    "ui.router",
    "ui.bootstrap",
    "duScroll",
    "ngFileUpload",
    "angularResizable"
]);

// routing
calendar.config(function($stateProvider, $urlRouterProvider, $locationProvider) {
    $stateProvider.state("index", {
        url: "/",
        templateUrl: "calendar/view/index.html",
        controller: "CalendarController"
    });

    $stateProvider.state("upload", {
        url: "/upload",
        templateUrl: "calendar/view/upload.html",
        controller: "UploadController"
    });

    $urlRouterProvider.otherwise("/");

    $locationProvider.html5Mode(false).hashPrefix('!');
});

calendar.run();

// template cache.
angular.module("templates", []);
