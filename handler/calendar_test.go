package handler

import (
	"net/http"
	"testing"
	"github.com/cj123/calendar/entity"
)

func TestHandler_OptionsHandler(t *testing.T) {
	var out map[string]interface{}

	res, err := makeRequest("GET", "/calendar/options", nil, &out, nil)

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

}

func TestHandler_NotesHandler(t *testing.T) {
	var notes []entity.Note

	res, err := makeRequest("GET", "/calendar/notes?date=2016-09-30", nil, &notes, nil)

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