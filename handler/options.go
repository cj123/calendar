package handler

import (
	"encoding/json"
	"log"
	"net/http"

	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
)

func (h *Handler) optionsGetHandler(w http.ResponseWriter, r *http.Request) {
	calID := muxVarAsUint(r, "calID")

	opts, err := h.optionsRepository.FindByCalendarID(calID)

	if err != nil {
		http.Error(w, "internal server error", http.StatusInternalServerError)
		return
	}

	b, err := json.Marshal(opts)

	if err != nil {
		http.Error(w, "internal server error", http.StatusInternalServerError)
		return
	}

	w.Write(b)
}

func (h *Handler) optionsUpdateHandler(w http.ResponseWriter, r *http.Request) {
	calID := muxVarAsUint(r, "calID")

	var updateRequest model.CalendarOptions

	err := unmarshalRequest(r, &updateRequest)

	if err != nil {
		log.Println(err)
		http.Error(w, "Unable to unmarshal request", http.StatusInternalServerError)
		return
	}

	err = h.optionsRepository.Update(calID, &updateRequest)

	if err == gorm.ErrRecordNotFound {
		http.Error(w, "not found", http.StatusNotFound)
		return
	} else if err != nil {
		http.Error(w, "internal server error", http.StatusInternalServerError)
		return
	}

	log.Printf("Updated opts with ID: %d", calID)

	w.WriteHeader(http.StatusOK)
}
