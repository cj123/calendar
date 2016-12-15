package handler

import (
	"github.com/gorilla/mux"
	"github.com/jinzhu/gorm"
	"net/http"
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

	r.HandleFunc("/calendar/appointments", h.appointmentsHandler)
	r.HandleFunc("/calendar/notes", h.notesHandler)
	r.HandleFunc("/calendar/options", h.optionsHandler)
	r.HandleFunc("/calendar/import", h.importHandler)

	// @TODO: C-UD appointments

	r.PathPrefix("/").Handler(http.FileServer(http.Dir("../../frontend"))) // @TODO parameterize

	return r
}
