var calendar = angular.module("calendar", [
    "angularMoment",
    "templates",
    "ui.router",
    "ui.bootstrap",
    "duScroll"
]);

// routing
calendar.config(function($stateProvider, $urlRouterProvider, $locationProvider) {
    $stateProvider.state("index", {
        url: "/",
        templateUrl: "calendar/view/index.html",
        controller: "CalendarController"
    });

    $urlRouterProvider.otherwise("/");

    $locationProvider.html5Mode(false).hashPrefix('!');
});

calendar.run();

// template cache.
angular.module("templates", []);
