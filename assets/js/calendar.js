/**
 * Calendar Javascript Base.
 * @author Callum Jones <cj@icj.me>
 */

var calendarData = require("./model/calendar-data.js");
var monthView = require("./modules/month-view.js");
var dayView = require("./modules/day-view.js");

(function() {
    console.log("Calendar.");
    calendarData.init();

    monthView.init();
    dayView.init();
})();
