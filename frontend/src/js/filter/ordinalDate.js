// from http://stackoverflow.com/questions/24060603/angularjs-get-dates-with-suffix-rd-th-and-st
angular.module("calendar").filter('ordinal', function($filter) {
    var suffixes = ["th", "st", "nd", "rd"];
    return function(input) {
        var day = parseInt(input);
        var relevantDigits = (day < 30) ? day % 20 : day % 30;
        var suffix = (relevantDigits <= 3) ? suffixes[relevantDigits] : suffixes[0];
        return input + suffix;
    };
});