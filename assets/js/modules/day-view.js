"use strict";

var $dayView;
var $grid;
var $appointments;
var appointments;
var $appointmentInfoModal;
var calendarData = require("../model/calendar-data");

module.exports = {
    init: function() {
        $dayView = $(".day-view");

        if (!$dayView.length) {
            return;
        }

        $grid         = $dayView.find(".day-view__grid");
        $appointments = $dayView.find(".day-view__appointments");
        $appointmentInfoModal = $("#appointment-info");

        this.loadAppointments();

        $(document).on("click", ".appointment", function() {
            var searchId = $(this).attr("data-id");
            var appointment;

            console.log(searchId);
            for (var appointmentIndex = 0; appointmentIndex < appointments.length; appointmentIndex++) {
                if (appointments[appointmentIndex].id == searchId) {
                    appointment = appointments[appointmentIndex];
                    break;
                }
            }

            console.log(appointment);

            populateModal($appointmentInfoModal, appointment);
            $appointmentInfoModal.modal("toggle");
        });
    },

    loadAppointments: function() {
        var currentDate = calendarData.currentDate;

        $.ajax({
            url: "/ajax/day-view?date=" + currentDate.format("Y-M-D")
        }).done(function(response) {
            $appointments.empty();

            if (!response.count) {
                return;
            }

            appointments = response.data;

            for (var appointmentIndex = 0; appointmentIndex < appointments.length; appointmentIndex++) {
                $appointments.append(createAppointment(appointments[appointmentIndex]));
            }
        });
    }
};

function createAppointment(appointment) {
    var $appointment = $("<a>", {
        "href": "#",
        "class": "appointment",
        "style": "top: " + appointment.start  + "px; height: " + appointment["length"] + "px",
        "data-id": appointment.id
    });

    $("<strong>").html(appointment.name).appendTo($appointment);

    var startTime = moment().minute(0).hour(0).add(appointment.start, "minutes");
    var endTime = startTime.clone().add(appointment["length"], "minutes");

    $("<time>").text(startTime.format("HH:mm") + "-" + endTime.format("HH:mm")).appendTo($appointment);

    return $appointment;
}

function populateModal($modal, appointment) {
    var startTime = moment().minute(0).hour(0).add(appointment.start, "minutes");
    var endTime = startTime.clone().add(appointment["length"], "minutes");

    $modal.find(".appointment-info__description").html(appointment.name);
    $modal.find(".start-time").val(startTime.format("HH:mm:00"));
    $modal.find(".finish-time").val(endTime.format("HH:mm:00"));
}
