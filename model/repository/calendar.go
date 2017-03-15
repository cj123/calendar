package repository

import (
	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
)

type CalendarRepository interface {
	FindByID(uid uint) (*model.Calendar, error)
	FindOrCreateForDeletedID(uid uint) (*model.Calendar, error)
	AllCalendars() ([]model.Calendar, error)
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

func (r *dbCalendarRepository) FindOrCreateForDeletedID(uid uint) (*model.Calendar, error) {
	var cal model.Calendar

	err := r.db.First(&cal, "deleted_for = ?", uid).Error

	if err == gorm.ErrRecordNotFound {
		deletedCal := model.Calendar{
			DeletedFor: uid,
			Options:    model.DefaultCalendarOptions(),
			Version:    2.0,
			Name:       "Deleted Appointments",
		}

		err = r.db.Create(&deletedCal).Error

		if err != nil {
			return nil, err
		}

		return &deletedCal, err
	}

	return &cal, err
}

func (r *dbCalendarRepository) AllCalendars() ([]model.Calendar, error) {
	var cals []model.Calendar

	err := r.db.Find(&cals).Error

	return cals, err
}
