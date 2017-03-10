package handler

import (
	"github.com/cj123/calendar/model"
	"net/http"
	"testing"
)

func TestHandler_OptionsHandler(t *testing.T) {
	var out map[string]interface{}

	res, err := makeRequest("GET", "/calendar/1/options", nil, &out, nil)

	if err != nil {
		t.Error(err)
	}

	if res.StatusCode != http.StatusOK {
		t.Fail()
	}

	if !out["MondayFirst"].(bool) {
		t.Fail()
	}
}

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
