package handler

import (
	"encoding/json"
	"io/ioutil"
	"net/http"

	"github.com/cj123/calendar/format"
	"github.com/cj123/calendar/model"
)

const (
	requestDateFormat = "2006-01-02"
)

func (h *Handler) OptionsHandler(w http.ResponseWriter, r *http.Request) {
	// @TODO get actual options!
	// calID := muxVarAsUint(r, "calID")
	b, err := json.Marshal(model.DefaultCalendarOptions())

	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	w.Write(b)
}

func (h *Handler) ImportHandler(w http.ResponseWriter, r *http.Request) {
	r.ParseMultipartForm(32 << 20)

	calendarType := r.FormValue("format")

	if !format.IsValidCalendarType(calendarType) {
		http.Error(w, "Invalid calendar type", http.StatusBadRequest)
		return
	}

	file, _, err := r.FormFile("file")

	if err != nil {
		http.Error(w, "Bad file", http.StatusBadRequest)
		return
	}

	defer file.Close()

	b, err := ioutil.ReadAll(file)

	if err != nil {
		http.Error(w, "Could not read calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}

	cal, err := format.ReadCalendar(b, format.CalendarType(calendarType))

	if err != nil {
		http.Error(w, "Could not read calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}

	err = h.db.Create(cal).Error

	if err != nil {
		http.Error(w, "Could not create calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusCreated)
}
