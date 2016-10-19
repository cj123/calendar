"use strict";

var moment = require("moment");
var calendarData = require("../model/calendar-data");
var dayView = require("./day-view");

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


module.exports = {
    init: function() {
        $monthView = $(".month-view");

        if (!$monthView.length) {
            return;
        }

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
    var currentDate = calendarData.currentDate;
    dayView.loadAppointments();

    $.ajax({
        url: "/ajax/month-view?year=" + currentDate.format("Y") + "&month=" + currentDate.format("M")
    }).done(function(response) {
        $monthName.text(currentDate.format("MMMM"));
        $year.text(currentDate.format("Y"));
        $dayGrid.html(response);

        $dayGrid.find(".day").removeClass("day--selected");
        $dayGrid.find(".day[data-day='" + currentDate.date() + "']").addClass("day--selected");
    });
}

function attachControlEvents() {
    $nextMonth.click(function() {
        calendarData.currentDate.add(1, "months");

        loadMonthView();
    });

    $previousMonth.click(function() {
        calendarData.currentDate.subtract(1, "months");

        loadMonthView();
    });

    $nextYear.click(function() {
        calendarData.currentDate.add(1, "years");

        loadMonthView();
    });

    $previousYear.click(function() {
        calendarData.currentDate.subtract(1, "years");

        loadMonthView();
    });

    $dayGrid.on("click", ".day", function(e) {
        e.preventDefault();

        var day = $(this).attr("data-day");
        calendarData.currentDate.date(day);

        loadMonthView();
    });
}

function attachDayControlEvents() {
    $previousDay.click(function() {
        calendarData.currentDate.subtract(1, "days");

        loadMonthView();
    });

    $today.click(function() {
        calendarData.currentDate = moment();

        loadMonthView();
    });

    $nextDay.click(function() {
        calendarData.currentDate.add(1, "days");

        loadMonthView();
    });
}
