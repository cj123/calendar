package handler

import (
	"github.com/cj123/calendar/model"
	"net/http"
	"testing"
)

func TestHandler_ImportHandler(t *testing.T) {
	t.Run("Valid Filetype", func(t *testing.T) {
		res, err := makeFileUploadRequest("/calendar/1/import", map[string]string{"format": "ical-tcl"}, "file", "file", []byte(icalTest))

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusCreated {
			t.Fail()
		}
	})

	t.Run("Invalid Filetype", func(t *testing.T) {
		res, err := makeFileUploadRequest("/calendar/1/import", map[string]string{"format": "calendar"}, "file", "file", []byte(icalTest))

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusBadRequest {
			t.Fail()
		}
	})
}

func TestHandler_calendarGetHandler(t *testing.T) {
	var calendars []model.Calendar

	res, err := makeRequest("GET", "/calendars", nil, &calendars, nil)

	if err != nil {
		t.Error(err)
	}

	if res.StatusCode != http.StatusOK {
		t.Fail()
	}

	if len(calendars) != 2 {
		t.Fail()
	}
}
