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
	noteRepository        *repository.NoteRepository
	appointmentRepository *repository.AppointmentRepository
}

func NewHandler(db *gorm.DB) *Handler {
	return &Handler{
		db:                    db,
		noteRepository:        &repository.NoteRepository{repository.Repository{DB: db}},
		appointmentRepository: &repository.AppointmentRepository{repository.Repository{DB: db}},
	}
}

func (h *Handler) Router() *mux.Router {
	r := mux.NewRouter()

	r.HandleFunc("/calendar/options", h.OptionsHandler)
	r.HandleFunc("/calendar/import", h.ImportHandler)

	r.Path("/calendar/appointments").Methods("GET").HandlerFunc(itemGetHandler(h.appointmentRepository))
	r.Path("/calendar/appointments").Methods("POST").HandlerFunc(itemCreateHandler(h.appointmentRepository))
	r.Path("/calendar/appointment/{id}").Methods("PUT").HandlerFunc(itemUpdateHandler(h.appointmentRepository))
	r.Path("/calendar/appointment/{id}").Methods("DELETE").HandlerFunc(itemDeleteHandler(h.appointmentRepository))

	r.Path("/calendar/notes").Methods("GET").HandlerFunc(itemGetHandler(h.noteRepository))
	r.Path("/calendar/notes").Methods("POST").HandlerFunc(itemCreateHandler(h.noteRepository))
	r.Path("/calendar/note/{id}").Methods("PUT").HandlerFunc(itemUpdateHandler(h.noteRepository))
	r.Path("/calendar/note/{id}").Methods("DELETE").HandlerFunc(itemDeleteHandler(h.noteRepository))

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
