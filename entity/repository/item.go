package repository

import (
	"time"

	"github.com/cj123/calendar/entity"
	"github.com/jinzhu/gorm"
)

type Repository struct {
	DB *gorm.DB
}

type AppointmentRepository struct {
	Repository
}

func (r *AppointmentRepository) FindBetweenDates(start, finish time.Time) ([]entity.Appointment, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var appointments []entity.Appointment

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

type NoteRepository struct {
	Repository
}

func (r *NoteRepository) FindBetweenDates(start, finish time.Time) ([]entity.Note, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var notes []entity.Note

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
