package handler

import (
	"io/ioutil"
	"log"
	"net/http"

	"github.com/cj123/calendar/format"
	"github.com/jinzhu/gorm"
)

const (
	requestDateFormat = "2006-01-02"
)

func (h *Handler) ImportHandler(w http.ResponseWriter, r *http.Request) {
	r.ParseMultipartForm(32 << 20)

	calendarType := r.FormValue("format")

	if !format.IsValidCalendarType(calendarType) {
		http.Error(w, "Invalid calendar type", http.StatusBadRequest)
		return
	}

	file, _, err := r.FormFile("file")

	if err != nil {
		log.Printf("Calendar upload file error: %s", err)

		http.Error(w, "Bad file", http.StatusBadRequest)
		return
	}

	defer file.Close()

	b, err := ioutil.ReadAll(file)

	if err != nil {
		log.Printf("Calendar import error: %s", err)
		http.Error(w, "Could not read calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}

	cal, err := format.ReadCalendar(b, format.CalendarType(calendarType))

	if err != nil {
		log.Printf("Calendar import error: %s", err)
		http.Error(w, "Could not read calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}

	dbCal, err := h.calendarRepository.FindByID(muxVarAsUint(r, "calID"))

	if err == gorm.ErrRecordNotFound {
		log.Println("Creating new calendar")
		cal.Name = "Imported Calendar"

		err = h.db.Create(cal).Error
	} else if err == nil {
		for _, appt := range cal.Appointments {
			err := h.appointmentRepository.Create(dbCal.ID, &appt)

			if err != nil {
				log.Printf("could not create appointment %d", appt.UID)
			}
		}

		for _, note := range cal.Notes {
			err := h.noteRepository.Create(dbCal.ID, &note)

			if err != nil {
				log.Printf("could not create note %d", note.UID)
			}
		}
	}

	if err != nil {
		log.Printf("Calendar import error: %s", err)

		http.Error(w, "Could not create calendar: "+err.Error(), http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusCreated)
}

func (h *Handler) calendarGetHandler(w http.ResponseWriter, r *http.Request) {
	cals, err := h.calendarRepository.AllCalendars()

	if err != nil {
		http.Error(w, "Could not get calendars", http.StatusInternalServerError)
		return
	}

	data, err := marshalResponse(cals)

	if err != nil {
		http.Error(w, "Could not get calendars", http.StatusInternalServerError)
		return
	}

	w.Write(data)
}
