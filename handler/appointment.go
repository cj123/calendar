package handler

import (
	"net/http"
	"time"
	"encoding/json"
	"io/ioutil"

	"gopkg.in/validator.v2"
	"github.com/cj123/calendar/entity/repository"
	"github.com/gorilla/mux"
	"github.com/cj123/calendar/entity"
)

func (h *Handler) getAppointmentsHandler(w http.ResponseWriter, r *http.Request) {
	startDateStr, finishDateStr := r.URL.Query().Get("start"), r.URL.Query().Get("finish")

	startDate, err := time.Parse(dateFormat, startDateStr)

	if err != nil {
		http.Error(w, "Bad start date", http.StatusBadRequest)
		return
	}

	finishDate, err := time.Parse(dateFormat, finishDateStr)

	if err != nil {
		http.Error(w, "Bad finish date", http.StatusBadRequest)
		return
	}

	repo := repository.AppointmentRepository{repository.Repository{DB: h.db}}

	appointments, err := repo.FindBetweenDates(startDate, finishDate)

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

func (h *Handler) createAppointmentHandler(w http.ResponseWriter, r *http.Request) {
	var appointment entity.Appointment

	body := r.Body
	defer body.Close()

	bytes, err := ioutil.ReadAll(body)

	if err != nil {
		http.Error(w, "Unable to unmarshal appointment", http.StatusInternalServerError)
		return
	}

	err = json.Unmarshal(bytes, &appointment)

	if err != nil {
		http.Error(w, "Unable to unmarshal appointment", http.StatusInternalServerError)
		return
	}

	if errs := validator.Validate(appointment); errs != nil {
		http.Error(w, errs.Error(), http.StatusBadRequest)
		return
	}

	err = h.db.Create(&appointment).Error

	if err != nil {
		http.Error(w, "Unable to create appointment", http.StatusInternalServerError)
		return
	}
}

// @TODO delete this or all?
func (h *Handler) deleteAppointmentHandler(w http.ResponseWriter, r *http.Request) {
	vars := mux.Vars(r)

	id := vars["id"]

	err := h.db.Delete(entity.Appointment{}, "id = ?", id).Error

	if err != nil {
		http.Error(w, "Unable to delete appointment: " + id, http.StatusInternalServerError)
		return
	}
}

func (h *Handler) updateAppointmentHandler(w http.ResponseWriter, r *http.Request) {

}