package repository

import (
	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
)

type CalendarRepository interface {
	FindByID(uid uint) (*model.Calendar, error)
}

func NewCalendarRepository(db *gorm.DB) CalendarRepository {
	return &dbCalendarRepository{
		db: db,
	}
}

type dbCalendarRepository struct {
	db *gorm.DB
}

func (r *dbCalendarRepository) FindByID(uid uint) (*model.Calendar, error) {
	var cal model.Calendar

	err := r.db.First(&cal, "id = ?", uid).Error

	return &cal, err
}
