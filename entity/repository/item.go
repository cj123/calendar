package repository

import (
	"github.com/cj123/calendar/entity"
	"github.com/jinzhu/gorm"
	"time"
)

type Repository struct {
	DB *gorm.DB
}

type AppointmentRepository struct {
	Repository
}

func (r *AppointmentRepository) FindBetweenDates(start, finish time.Time) ([]entity.Appointment, error) {
	if start == finish {
		finish.Add((time.Hour * 24) - (1 * time.Second)) // 23:59:59
	}

	appointments := make([]entity.Appointment, 10)

	err := r.DB.Table("appointments").Where(`
		start IS NOT NULL AND start <= ?
		AND (recurrence_rule != '' OR (start <= ? AND start >= ?))
	`, finish, finish, start).Order("start asc").Find(&appointments).Error

	return appointments, err
}

type NoteRepository struct {
	Repository
}

func (r *NoteRepository) FindBetweenDates(start, finish time.Time) ([]entity.Note, error) {
	if start == finish {
		finish.Add((time.Hour * 24) - (1 * time.Second)) // 23:59:59
	}

	notes := make([]entity.Note, 10)

	err := r.DB.Table("notes").Where(`
		start IS NOT NULL AND start <= ?
		AND (recurrence_rule != '' OR (start <= ? AND start >= ?))
	`, finish, finish, start).Order("start asc").Find(&notes).Error

	return notes, err
}
