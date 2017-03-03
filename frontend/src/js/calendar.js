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
calendar.config(function($stateProvider, $urlRouterProvider, $locationProvider, $logProvider) {
    $stateProvider.state("index", {
        url: "/{calendarID}",
        templateUrl: "calendar/view/index.html",
        controller: "CalendarController"
    });

    $stateProvider.state("upload", {
        url: "/upload",
        templateUrl: "calendar/view/upload.html",
        controller: "UploadController"
    });

    $urlRouterProvider.otherwise("/1");

    $locationProvider.html5Mode(false).hashPrefix('!');

    $logProvider.debugEnabled(true);
});

calendar.run();

// template cache.
angular.module("templates", []);
