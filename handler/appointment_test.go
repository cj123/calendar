package handler

import (
	"net/http"
	"testing"
	"time"

	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
)

func TestHandler_GetAppointmentsHandler(t *testing.T) {
	t.Run("Get appointments", func(t *testing.T) {
		var appointments []model.Appointment

		_, err := makeRequest("GET", "/calendar/1/appointments?start=2016-01-12&finish=2016-12-31", nil, &appointments, nil)

		if err != nil {
			t.Error(err)
		}

		if len(appointments) < 1 {
			t.Fail()
		}
	})

	t.Run("Get appointments invalid start", func(t *testing.T) {
		res, err := makeRequest("GET", "/calendar/1/appointments?start=2016-0d-12&finish=2016-12-31", nil, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusBadRequest {
			t.Fail()
		}
	})

	t.Run("Get appointments invalid finish", func(t *testing.T) {
		res, err := makeRequest("GET", "/calendar/1/appointments?start=2016-01-12&finish=2016-12-33", nil, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusBadRequest {
			t.Fail()
		}
	})
}

func TestHandler_CreateAppointmentHandler(t *testing.T) {
	t.Run("Valid", func(t *testing.T) {
		appointment := model.Appointment{
			Item: model.Item{
				Text:       "test appointment",
				Start:      time.Now(),
				Finish:     time.Now().Add(time.Hour),
				CalendarID: 1,
			},
		}

		res, err := makeRequest("POST", "/calendar/1/appointments", &appointment, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusCreated {
			t.Fail()
		}

		var created model.Appointment

		db.Model(model.Appointment{}).Order("id DESC").Limit(1).First(&created)

		if created.Text != appointment.Text || !created.Start.Equal(appointment.Start) || !created.Finish.Equal(appointment.Finish) {
			t.Fail()
		}
	})

	t.Run("Invalid text", func(t *testing.T) {
		appointment := model.Appointment{
			Item: model.Item{
				Text:       "",
				Start:      time.Now(),
				Finish:     time.Now().Add(time.Hour),
				CalendarID: 1,
			},
		}

		res, err := makeRequest("POST", "/calendar/1/appointments", &appointment, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusBadRequest {
			t.Fail()
		}
	})
}

func TestHandler_DeleteAppointmentHandler(t *testing.T) {
	t.Run("Delete all occurrences", func(t *testing.T) {
		request := deleteItemRequest{DeleteAll: true}

		// there should be an appointment "1"
		res, err := makeRequest("DELETE", "/calendar/1/appointment/1", request, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusOK {
			t.Fail()
		}

		var deleted model.Appointment

		err = db.Where("id = ?", 1).First(&deleted).Error

		if err != gorm.ErrRecordNotFound {
			t.Fail()
		}
	})

	t.Run("Delete only one occurrence", func(t *testing.T) {
		dateToDelete := time.Now()

		request := deleteItemRequest{DeleteAll: false, Date: dateToDelete}

		// there should be an appointment "2"
		res, err := makeRequest("DELETE", "/calendar/1/appointment/2", request, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusOK {
			t.Fail()
		}

		var deleted model.AppointmentDeletedDate

		err = db.Where("appointment_id = ?", 2).First(&deleted).Error

		if err == gorm.ErrRecordNotFound {
			t.Fail()
		} else if err != nil {
			t.Error(err)
		}

		if deleted.AppointmentID != 2 || !deleted.Date.Equal(dateToDelete) {
			t.Fail()
		}
	})

	t.Run("Delete cannot parse UID", func(t *testing.T) {
		dateToDelete := time.Now()

		request := deleteItemRequest{Date: dateToDelete}

		// there should be an appointment "2"
		res, err := makeRequest("DELETE", "/calendar/1/appointment/s2", request, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusBadRequest {
			t.Fail()
		}
	})
}
