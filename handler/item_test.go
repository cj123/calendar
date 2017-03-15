package handler

import (
	"net/http"
	"reflect"
	"testing"
	"time"

	"github.com/cj123/calendar/model"
	"github.com/cj123/calendar/model/repository"
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

func TestHandler_AppointmentUpdateHandler(t *testing.T) {
	t.Run("Valid update", func(t *testing.T) {
		repo := repository.NewAppointmentRepository(db)
		appt, err := repo.FindByID(1, 5)

		if err != nil {
			t.Error(err)
		}

		// make some modifications to appointment
		appt.Alarms = appt.Alarms[2:len(appt.Alarms)] // remove first 2 alarms
		appt.Text = "updated appointment test"
		appt.Todo = true

		res, err := makeRequest("PUT", "/calendar/1/appointment/5", appt, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusOK {
			t.Fail()
		}

		// get the appointment back
		updatedAppt, err := repo.FindByID(1, 5)

		if err != nil {
			t.Error(err)
		}

		// clear out the gorm fields on both so we know we're checking identically
		appt.UpdatedAt = time.Now()
		updatedAppt.UpdatedAt = appt.UpdatedAt

		for i := range appt.Alarms {
			alarmTime := time.Now()
			appt.Alarms[i].UpdatedAt = alarmTime
			updatedAppt.Alarms[i].UpdatedAt = alarmTime
		}

		if !reflect.DeepEqual(appt, updatedAppt) {
			t.Fail()
		}
	})

	t.Run("Invalid text value", func(t *testing.T) {
		repo := repository.NewAppointmentRepository(db)
		appt, err := repo.FindByID(1, 10)

		if err != nil {
			t.Error(err)
		}

		// make some modifications to appointment
		appt.Text = "" // this should fail
		appt.Todo = true
		appt.Start = time.Now()

		res, err := makeRequest("PUT", "/calendar/1/appointment/10", appt, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusBadRequest {
			t.Fail()
		}
	})
}

func TestHandler_NotesHandler(t *testing.T) {
	var notes []model.Note

	res, err := makeRequest("GET", "/calendar/1/notes?start=2016-09-30&finish=2016-09-30", nil, &notes, nil)

	if err != nil {
		t.Error(err)
	}

	if res.StatusCode != http.StatusOK {
		t.Fail()
	}

	if len(notes) < 1 {
		t.Fail()
	}
}
