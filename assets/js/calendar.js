/**
 * Calendar Javascript Base.
 * @author Callum Jones <cj@icj.me>
 */

var monthView = require("./modules/month-view.js");
var todayView = require("./modules/today-view.js");

(function() {
    console.log("Calendar.");

    monthView.init();
    todayView.init();
})();
