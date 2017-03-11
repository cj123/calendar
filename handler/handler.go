package handler

import (
	"encoding/json"
	"io/ioutil"
	"net/http"

	"github.com/cj123/calendar/model/repository"
	"github.com/gorilla/mux"
	"github.com/jinzhu/gorm"
)

type Handler struct {
	db                    *gorm.DB
	noteRepository        repository.ItemRepository
	appointmentRepository repository.ItemRepository
	optionsRepository     repository.OptionsRepository
	calendarRepository    repository.CalendarRepository
}

func NewHandler(db *gorm.DB) *Handler {
	return &Handler{
		db:                    db,
		noteRepository:        repository.NewNoteRepository(db),
		appointmentRepository: repository.NewAppointmentRepository(db),
		optionsRepository:     repository.NewOptionsRepository(db),
		calendarRepository:    repository.NewCalendarRepository(db),
	}
}

func (h *Handler) Router() *mux.Router {
	r := mux.NewRouter()

	r.Path("/calendars").Methods("GET").HandlerFunc(h.calendarGetHandler)
	r.Path("/calendar/{calID}/options").Methods("GET").HandlerFunc(h.optionsGetHandler)
	r.Path("/calendar/{calID}/options").Methods("PUT").HandlerFunc(h.optionsUpdateHandler)
	r.HandleFunc("/calendar/{calID}/import", h.ImportHandler)

	r.Path("/calendar/{calID}/appointments").Methods("GET").HandlerFunc(itemGetHandler(h.appointmentRepository))
	r.Path("/calendar/{calID}/appointments").Methods("POST").HandlerFunc(itemCreateHandler(h.appointmentRepository))
	r.Path("/calendar/{calID}/appointment/{id}").Methods("PUT").HandlerFunc(itemUpdateHandler(h.appointmentRepository))
	r.Path("/calendar/{calID}/appointment/{id}").Methods("DELETE").HandlerFunc(itemDeleteHandler(h.appointmentRepository))

	r.Path("/calendar/{calID}/notes").Methods("GET").HandlerFunc(itemGetHandler(h.noteRepository))
	r.Path("/calendar/{calID}/notes").Methods("POST").HandlerFunc(itemCreateHandler(h.noteRepository))
	r.Path("/calendar/{calID}/note/{id}").Methods("PUT").HandlerFunc(itemUpdateHandler(h.noteRepository))
	r.Path("/calendar/{calID}/note/{id}").Methods("DELETE").HandlerFunc(itemDeleteHandler(h.noteRepository))

	return r
}

func unmarshalRequest(r *http.Request, into interface{}) error {
	body := r.Body
	defer body.Close()

	bytes, err := ioutil.ReadAll(body)

	if err != nil {
		return err
	}

	return json.Unmarshal(bytes, &into)
}

func marshalResponse(item interface{}) ([]byte, error) {
	return json.Marshal(&item)
}
