package handler

import (
	"net/http"
	"io/ioutil"
	"encoding/json"

	"github.com/gorilla/mux"
	"github.com/jinzhu/gorm"
)

type Handler struct {
	db *gorm.DB
}

func NewHandler(db *gorm.DB) *Handler {
	return &Handler{
		db: db,
	}
}

func (h *Handler) Router() *mux.Router {
	r := mux.NewRouter()

	r.HandleFunc("/calendar/notes", h.notesHandler)
	r.HandleFunc("/calendar/options", h.optionsHandler)
	r.HandleFunc("/calendar/import", h.importHandler)

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