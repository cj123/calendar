package repository

import (
	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
)

type OptionsRepository interface {
	FindByCalendarID(uid uint, create bool) (*model.CalendarOptions, error)
	Update(calID uint, new *model.CalendarOptions) error
}

func NewOptionsRepository(db *gorm.DB) OptionsRepository {
	return &dbOptionsRepository{
		db: db,
	}
}

type dbOptionsRepository struct {
	db *gorm.DB
}

func (r *dbOptionsRepository) FindByCalendarID(uid uint, create bool) (*model.CalendarOptions, error) {
	var opts model.CalendarOptions

	err := r.db.Preload("DefaultAlarms").First(&opts, "calendar_id = ?", uid).Error

	if err == gorm.ErrRecordNotFound && create {
		defaults := model.DefaultCalendarOptions()
		defaults.CalendarID = uid

		return &defaults, r.db.Create(&defaults).Error
	}

	return &opts, err
}

func (r *dbOptionsRepository) Update(calID uint, new *model.CalendarOptions) error {
	opts, err := r.FindByCalendarID(calID, false)

	if err != nil {
		return err
	}

	tx := r.db.Begin()

	err = tx.Where("calendar_id = ?", calID).Delete(model.CalendarOptions{}).Error

	// handle alarm update
	var alarmIDs []uint

	for _, alarm := range new.DefaultAlarms {
		alarmIDs = append(alarmIDs, alarm.ID)
	}

	if len(alarmIDs) > 0 {
		err := tx.Where("id NOT IN (?)", alarmIDs).Delete(model.DefaultAlarm{}).Error

		if err != nil {
			tx.Rollback()
			return err
		}
	}

	err = tx.Model(opts).Create(new).Error

	if err != nil {
		tx.Rollback()
		return err
	}

	return tx.Commit().Error
}
