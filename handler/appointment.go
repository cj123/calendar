package handler

import (
	"encoding/json"
	"log"
	"net/http"
	"strconv"
	"time"

	"github.com/cj123/calendar/model"
	"github.com/gorilla/mux"
	"github.com/jinzhu/gorm"
	"gopkg.in/bluesuncorp/validator.v9"
)

func (h *Handler) GetAppointmentsHandler(w http.ResponseWriter, r *http.Request) {
	startDateStr, finishDateStr := r.URL.Query().Get("start"), r.URL.Query().Get("finish")

	startDate, err := time.Parse(requestDateFormat, startDateStr)

	if err != nil {
		http.Error(w, "Bad start date", http.StatusBadRequest)
		return
	}

	finishDate, err := time.Parse(requestDateFormat, finishDateStr)

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
	var appointment model.Appointment

	err := unmarshalRequest(r, &appointment)

	if err != nil {
		http.Error(w, "Unable to unmarshal request", http.StatusInternalServerError)
		return
	}

	validate := validator.New()

	if err := validate.Struct(appointment); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	err = h.db.Create(&appointment).Error

	if err != nil {
		http.Error(w, "Unable to create appointment", http.StatusInternalServerError)
		return
	}

	log.Printf("Created appointment with ID: %d\n", appointment.ID)

	w.WriteHeader(http.StatusCreated)
}

type deleteAppointmentRequest struct {
	Date      time.Time `json:"date"`
	DeleteAll bool      `json:"delete_all"`
}

/**
 * potential @TODO: #39 -- delete all future events means all events from a given date.
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
		http.Error(w, "Unable to unmarshal request", http.StatusInternalServerError)
		return
	}

	if request.DeleteAll {
		log.Printf("Deleting all appointments with id: %s\n", id)

		// no specific date, delete all occurrences
		err = h.db.Delete(model.Appointment{}, "id = ?", id).Error
	} else {
		log.Printf("Deleting appointment recurrence at %s with id: %s\n", request.Date, id)

		uid, err := strconv.ParseUint(id, 0, 10)

		if err != nil {
			http.Error(w, "Can't parse UID", http.StatusBadRequest)
			return
		}

		// there is a date, we just wish to add this to deleted
		deletedDate := model.AppointmentDeletedDate{
			DeletedDate: model.DeletedDate{
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
	id := mux.Vars(r)["id"]

	appointment, err := h.appointmentRepository.FindByID(id)

	if err == gorm.ErrRecordNotFound {
		http.Error(w, "not found", http.StatusNotFound)
		return
	} else if err != nil {
		http.Error(w, "internal server error", http.StatusInternalServerError)
		return
	}

	var updateRequest model.Appointment

	err = unmarshalRequest(r, &updateRequest)

	if err != nil {
		log.Println(err)
		http.Error(w, "Unable to unmarshal request", http.StatusInternalServerError)
		return
	}

	log.Printf("Updating appointment with ID: %s", id)

	validate := validator.New()

	if err := validate.Struct(updateRequest); err != nil {
		log.Println(err)
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	err = h.db.Model(&appointment).Updates(updateRequest).Error

	if err != nil {
		log.Println(err)
		http.Error(w, "could not update appointment", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}
