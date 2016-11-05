var calendar = angular.module("calendar", [
    "angularMoment",
    "templates",
    "ui.router",
    "ui.bootstrap"
]);

// api base, including trailing slash
calendar.constant("API_BASE", "https://calendar.docker.local/");

// routing
calendar.config(function($stateProvider, $urlRouterProvider, $locationProvider) {
    $stateProvider.state("index", {
        url: "/",
        templateUrl: "calendar/view/index.html",
        controller: "CalendarController"
    });

    $urlRouterProvider.otherwise("/");
    $locationProvider.html5Mode(true);
});

calendar.run();

// template cache.
angular.module("templates", []);
