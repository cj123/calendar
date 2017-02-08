package handler

import (
	"encoding/json"
	"net/http"
	"time"
)

func (h *Handler) GetNotesHandler(w http.ResponseWriter, r *http.Request) {
	dateStr := r.URL.Query().Get("date")
	date, err := time.Parse(requestDateFormat, dateStr)

	if err != nil {
		http.Error(w, "Bad start date", http.StatusBadRequest)
		return
	}

	notes, err := h.noteRepository.FindBetweenDates(date, date)

	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	b, err := json.Marshal(&notes)

	if err != nil {
		http.Error(w, "Internal server error", http.StatusInternalServerError)
		return
	}

	w.Write(b)
}

// @TODO
func (h *Handler) CreateNotesHandler(w http.ResponseWriter, r *http.Request) {}
func (h *Handler) UpdateNotesHandler(w http.ResponseWriter, r *http.Request) {}
func (h *Handler) DeleteNotesHandler(w http.ResponseWriter, r *http.Request) {}
