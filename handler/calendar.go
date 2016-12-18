package handler

import (
	"encoding/json"
	"io/ioutil"
	"net/http"
	"time"

	"github.com/cj123/calendar/entity"
	"github.com/cj123/calendar/entity/repository"
	"github.com/cj123/calendar/format"
	"github.com/cj123/calendar/format/ical-tcl"
	"github.com/cj123/calendar/format/ics"
)

const (
	dateFormat = "2006-01-02"
)

func (h *Handler) notesHandler(w http.ResponseWriter, r *http.Request) {
	dateStr := r.URL.Query().Get("date")
	date, err := time.Parse(dateFormat, dateStr)

	if err != nil {
		http.Error(w, "Bad start date", http.StatusBadRequest)
		return
	}

	repo := repository.NoteRepository{repository.Repository{DB: h.db}}

	appointments, err := repo.FindBetweenDates(date, date)

	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	b, err := json.Marshal(&appointments)

	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	w.Write(b)
}

var defaultOptions = map[string]interface{}{
	"DefaultEarlyWarning": 1,
	"DefaultAlarms":       [...]int{0, 5, 10, 15},
	"DayviewTimeStart":    8,
	"DayviewTimeFinish":   18,
	"ItemWidth":           9,
	"NoticeHeight":        6,
	"AmPm":                false,
	"MondayFirst":         true,
	"AllowOverflow":       true,
	"Visible":             "1", // @TODO find out what this does
	"IgnoreAlarms":        false,
	"Color":               "<Default> <Default>",
	"Timezone":            "<Local>",
}

func (h *Handler) optionsHandler(w http.ResponseWriter, r *http.Request) {
	b, err := json.Marshal(defaultOptions)

	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	w.Write(b)
}

func (h *Handler) importHandler(w http.ResponseWriter, r *http.Request) {
	r.ParseMultipartForm(32 << 20)

	calendarType := r.FormValue("format")

	if calendarType != "ical-tcl" && calendarType != "ics" {
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

	var cal *entity.Calendar
	var reader format.Reader

	if calendarType == "ical-tcl" {
		reader = icaltcl.NewCalendarReader(icaltcl.NewICalLexer(string(b)))
	} else if calendarType == "ics" {
		reader = ics.NewICSReader(string(b))
	}

	cal, err = reader.Read()

	if err != nil {
		http.Error(w, "Could not read calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}

	err = h.db.Create(cal).Error

	if err != nil {
		http.Error(w, "Could not create calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}
}
