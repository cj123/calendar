package repository

import (
	"time"

	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
)

type Repository struct {
	DB *gorm.DB
}

type AppointmentRepository struct {
	Repository
}

func (r *AppointmentRepository) FindBetweenDates(start, finish time.Time) ([]model.Appointment, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var appointments []model.Appointment

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

	return appointments, err
}

func (r *AppointmentRepository) FindByID(id interface{}) (model.Appointment, error) {
	var appointment model.Appointment

	err := r.DB.Preload("DeletedDates").Preload("Alarms").First(&appointment, "id = ?", id).Error

	return appointment, err
}

type NoteRepository struct {
	Repository
}

func (r *NoteRepository) FindBetweenDates(start, finish time.Time) ([]model.Note, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var notes []model.Note

	err := r.DB.
		Preload("DeletedDates").
		Where(`
			start IS NOT NULL AND start <= ?
			AND (recurrence_rule != '' OR (start <= ? AND start >= ?))
		`, finish, finish, start).
		Order("start asc").
		Find(&notes).
		Error

	return notes, err
}
