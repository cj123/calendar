package handler

import (
	"encoding/json"
	"log"
	"net/http"
	"strconv"
	"time"

	"github.com/cj123/calendar/entity"
	"github.com/gorilla/mux"
	"gopkg.in/bluesuncorp/validator.v9"
)

func (h *Handler) GetAppointmentsHandler(w http.ResponseWriter, r *http.Request) {
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

	appointments, err := h.appointmentRepository.FindBetweenDates(startDate, finishDate)

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

func (h *Handler) CreateAppointmentHandler(w http.ResponseWriter, r *http.Request) {
	var appointment entity.Appointment

	err := unmarshalRequest(r, &appointment)

	if err != nil {
		http.Error(w, "Unable to unmarshal request", http.StatusInternalServerError)
		return
	}

	validate := validator.New()

	if errs := validate.Struct(appointment); errs != nil {
		http.Error(w, errs.Error(), http.StatusBadRequest)
		return
	}

	err = h.db.Create(&appointment).Error

	if err != nil {
		http.Error(w, "Unable to create appointment", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusCreated)
}

type deleteAppointmentRequest struct {
	Date      time.Time `json:"date"`
	DeleteAll bool      `json:"delete_all"`
}

/**
 * @TODO: delete all future events means all events from a given date.
 * so, we just need to modify the event to set the end date to be the date of the previous appointment?
 */
func (h *Handler) DeleteAppointmentHandler(w http.ResponseWriter, r *http.Request) {
	vars := mux.Vars(r)

	id := vars["id"]

	var (
		request deleteAppointmentRequest
		err     error
	)

	if err = unmarshalRequest(r, &request); err != nil {
		log.Print(err)
		http.Error(w, "Unable to marshal request", http.StatusInternalServerError)
		return
	}

	if request.DeleteAll {
		log.Printf("Deleting all appointments with id: %s\n", id)

		// no specific date, delete all occurrences
		err = h.db.Delete(entity.Appointment{}, "id = ?", id).Error
	} else {
		log.Printf("Deleting appointment recurrence at %s with id: %s\n", request.Date, id)

		uid, err := strconv.ParseUint(id, 0, 10)

		if err != nil {
			http.Error(w, "Can't parse UID", http.StatusBadRequest)
			return
		}

		// there is a date, we just wish to add this to deleted
		deletedDate := entity.AppointmentDeletedDate{
			DeletedDate: entity.DeletedDate{
				Date: request.Date,
			},
			AppointmentID: uint(uid),
		}

		err = h.db.Create(&deletedDate).Error
	}

	if err != nil {
		log.Print(err)
		http.Error(w, "Unable to delete appointment: "+id, http.StatusInternalServerError)
		return
	}
}

func (h *Handler) UpdateAppointmentHandler(w http.ResponseWriter, r *http.Request) {

}
