"use strict";

var $dayView;
var $grid;
var $appointments;

var calendarData = require("../model/calendar-data");

module.exports = {
    init: function() {
        $dayView = $(".day-view");

        if (!$dayView.length) {
            return;
        }

        $grid         = $dayView.find(".day-view__grid");
        $appointments = $dayView.find(".day-view__appointments");

        this.loadAppointments();
    },

    loadAppointments: function() {
        var currentDate = calendarData.currentDate;

        $.ajax({
            url: "/ajax/day-view?date=" + currentDate.format("Y-M-D")
        }).done(function(response) {
            if (!response.count) {
                return;
            }

            $appointments.empty();

            for (var appointmentIndex = 0; appointmentIndex < response.data.length; appointmentIndex++) {
                $appointments.append(createAppointment(response.data[appointmentIndex]));
            }
        });
    }
};

function createAppointment(appointment) {
    var $appointment = $("<div>", {
        "class": "appointment",
        "style": "top: " + appointment.start  + "px; height: " + appointment["length"] + "px",
        "data-id": appointment.id
    });

    $("<strong>").text(appointment.name).appendTo($appointment);

    var startTime = moment().minute(0).hour(0).add(appointment.start, "minutes");
    var endTime = startTime.clone().add(appointment["length"], "minutes");

    $("<time>").text(startTime.format("HH:mm") + "-" + endTime.format("HH:mm")).appendTo($appointment);

    return $appointment;
}
