"use strict";

var moment = require("moment");

var $monthView;
var $dayGrid;

var $controls;
var $previousMonth;
var $nextMonth;
var $monthName;
var $previousYear;
var $nextYear;
var $year;

var $dayControls;

var $today;
var $previousDay;
var $nextDay;

// current date in the day view.
var currentDate;

module.exports = {
    init: function() {
        $monthView = $(".month-view");

        if (!$monthView.length) {
            return;
        }

        currentDate = moment();

        $controls      = $monthView.find(".month-view__controls");
        $previousMonth = $controls.find(".previous-month");
        $nextMonth     = $controls.find(".next-month");
        $previousYear  = $controls.find(".previous-year");
        $nextYear      = $controls.find(".next-year");
        $monthName     = $controls.find(".month");
        $year          = $controls.find(".year");

        $dayGrid = $monthView.find(".month-view__grid");

        $dayControls = $monthView.find(".month-view__skip");
        $previousDay = $dayControls.find(".previous-day");
        $today       = $dayControls.find(".today");
        $nextDay     = $dayControls.find(".next-day");

        attachControlEvents();
        attachDayControlEvents();

        // set up month view with today's date initially.
        loadMonthView();
    }
};

function loadMonthView() {
    // @TODO: only make request if month/year changes. NOT DAY
    $.ajax({
        url: "/ajax/month-view?year=" + currentDate.format("Y") + "&month=" + currentDate.format("M")
    }).done(function(response) {
        $monthName.text(currentDate.format("MMMM"));
        $year.text(currentDate.format("Y"));
        $dayGrid.html(response);

        $dayGrid.find(".day").removeClass("day--selected");
        $dayGrid.find(".day-" + currentDate.date()).addClass("day--selected");
    });
}

function attachControlEvents() {
    $nextMonth.click(function() {
        currentDate.add(1, "months");

        loadMonthView();
    });

    $previousMonth.click(function() {
        currentDate.subtract(1, "months");

        loadMonthView();
    });

    $nextYear.click(function() {
        currentDate.add(1, "years");

        loadMonthView();
    });

    $previousYear.click(function() {
        currentDate.subtract(1, "years");

        loadMonthView();
    });
}

function attachDayControlEvents() {
    $previousDay.click(function() {
        currentDate.subtract(1, "days");

        loadMonthView();
    });

    $today.click(function() {
        currentDate = moment();

        loadMonthView();
    });

    $nextDay.click(function() {
        currentDate.add(1, "days");

        loadMonthView();
    });
}
