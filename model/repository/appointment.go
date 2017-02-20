package repository

import (
	"errors"
	"time"

	"github.com/cj123/calendar/model"
)

var (
	InvalidAppointmentAssertionError = errors.New("invalid appointment type")
)

type AppointmentRepository struct {
	Repository
}

func (r *AppointmentRepository) Model() Model {
	return &model.Appointment{}
}

func (r *AppointmentRepository) modelSlice(appointments []*model.Appointment) []Model {
	models := make([]Model, len(appointments))

	for i, appointment := range appointments {
		models[i] = appointment
	}

	return models
}

func (r *AppointmentRepository) Create(m Model) error {
	if appointment, ok := m.(*model.Appointment); ok {
		return r.DB.Create(&appointment).Error
	}

	return InvalidAppointmentAssertionError
}

func (r *AppointmentRepository) FindBetweenDates(start, finish time.Time) ([]Model, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var appointments []*model.Appointment

	err := r.DB.
		Preload("DeletedDates").
		Preload("Alarms").
		Where(`
				start IS NOT NULL AND start <= ?
				AND (recurrence_rule != '' OR (start <= ? AND start >= ?))
			`, finish, finish, start).
		Order("start asc").
		Find(&appointments).
		Error

	return r.modelSlice(appointments), err
}

func (r *AppointmentRepository) FindByID(id interface{}) (model.Appointment, error) {
	var appointment model.Appointment

	err := r.DB.Preload("DeletedDates").Preload("Alarms").First(&appointment, "id = ?", id).Error

	return appointment, err
}

func (r *AppointmentRepository) DeleteItem(uid uint) error {
	return r.DB.Delete(model.Appointment{}, "id = ?", uid).Error
}

func (r *AppointmentRepository) DeleteRecurrence(itemID uint, date time.Time) error {
	deletedDate := model.AppointmentDeletedDate{
		DeletedDate: model.DeletedDate{
			Date: date,
		},
		AppointmentID: itemID,
	}

	return r.DB.Create(&deletedDate).Error
}

func (r *AppointmentRepository) Update(itemID uint, new Model) error {
	appointment, err := r.FindByID(itemID)

	if err != nil {
		return err
	}

	if updates, ok := new.(*model.Appointment); ok {
		return r.DB.Model(&appointment).Updates(updates).Error
	}

	return InvalidAppointmentAssertionError
}
