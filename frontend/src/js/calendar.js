var calendar = angular.module("calendar", [
    "angularMoment",
    "templates",
    "ui.router",
    "ui.bootstrap",
    "duScroll",
    "ngFileUpload",
    "angularResizable",
    "focus-if",
    "cfp.hotkeys"
]);

// routing
calendar.config(function($stateProvider, $urlRouterProvider, $locationProvider, $logProvider) {
    $stateProvider.state("upload", {
        url: "/{calendarID}/upload",
        templateUrl: "calendar/view/upload.html",
        controller: "UploadController"
    });

    $stateProvider.state("options", {
        url: "/{calendarID}/options",
        templateUrl: "calendar/view/options.html",
        controller: "OptionsController"
    });

    $stateProvider.state("index", {
        url: "/{calendarID}",
        templateUrl: "calendar/view/index.html",
        controller: "CalendarController"
    });

    $urlRouterProvider.otherwise("/1");

    $locationProvider.html5Mode(false).hashPrefix('!');

    $logProvider.debugEnabled(true);
});

calendar.run(["$rootScope", "$state", "$stateParams",
    function($rootScope, $state, $stateParams) {
        $rootScope.$state = $state;
        $rootScope.$stateParams = $stateParams;
    }
]);

// template cache.
angular.module("templates", []);
