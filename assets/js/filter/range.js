// based on http://stackoverflow.com/a/14932395
angular.module("calendar").filter("range", function() {
    return function (arr) {
        var lower = 0;
        var upper = 0;

        if (arr.length === 1) {
            upper = parseInt(arr[0], 10) - 1;
        } else if (arr.length === 2) {
            lower = parseInt(arr[0], 10);
            upper = parseInt(arr[1], 10);
        }

        var out = [];

        for (var index = lower; index <= upper; index++) {
            out.push(index);
        }

        return out;
    };
});
