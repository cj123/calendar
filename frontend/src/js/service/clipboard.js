angular.module("calendar").factory("Clipboard", function() {
    var clipboard = {};

    clipboard.data = null;

    clipboard.put = function(data) {
        console.log(data);
        clipboard.data = data;
    };

    clipboard.get = function() {
        return clipboard.data;
    };

    clipboard.clear = function() {
        clipboard.data = null;
    };

    return clipboard;
});
