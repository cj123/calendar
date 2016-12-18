package handler

import (
	"encoding/json"
	"io/ioutil"
	"net/http"

	"github.com/gorilla/mux"
	"github.com/jinzhu/gorm"
	"github.com/cj123/calendar/entity/repository"
)

type Handler struct {
	db *gorm.DB
	noteRepository repository.NoteRepository
	appointmentRepository repository.AppointmentRepository
}

func NewHandler(db *gorm.DB) *Handler {
	return &Handler{
		db: db,
		noteRepository: repository.NoteRepository{repository.Repository{DB: db}},
		appointmentRepository: repository.AppointmentRepository{repository.Repository{DB: db}},
	}
}

func (h *Handler) Router() *mux.Router {
	r := mux.NewRouter()

	r.HandleFunc("/calendar/notes", h.NotesHandler)
	r.HandleFunc("/calendar/options", h.OptionsHandler)
	r.HandleFunc("/calendar/import", h.ImportHandler)

	r.Path("/calendar/appointments").Methods("GET").HandlerFunc(h.GetAppointmentsHandler)
	r.Path("/calendar/appointments").Methods("POST").HandlerFunc(h.CreateAppointmentHandler)
	r.Path("/calendar/appointments/{id}").Methods("PUT").HandlerFunc(h.UpdateAppointmentHandler)
	r.Path("/calendar/appointments/{id}").Methods("DELETE").HandlerFunc(h.DeleteAppointmentHandler)

	r.PathPrefix("/").Handler(http.FileServer(http.Dir("../../frontend"))) // @TODO parameterize

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
