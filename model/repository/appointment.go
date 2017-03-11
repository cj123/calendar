package repository

import (
	"errors"
	"time"

	"github.com/cj123/calendar/model"

	"github.com/jinzhu/gorm"
	"log"
)

var (
	InvalidAppointmentAssertionError = errors.New("invalid appointment type")
)

type AppointmentRepository struct {
	Repository

	opts OptionsRepository
	cal  CalendarRepository
}

func NewAppointmentRepository(db *gorm.DB) *AppointmentRepository {
	return &AppointmentRepository{
		Repository{
			db: db,
		},
		NewOptionsRepository(db),
		NewCalendarRepository(db),
	}
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

func (r *AppointmentRepository) Create(calID uint, m Model) error {
	if appointment, ok := m.(*model.Appointment); ok {
		appointment.CalendarID = calID

		for i := range appointment.Alarms {
			// zero out IDs so that they can be created in the db
			appointment.Alarms[i].ID = 0
		}

		return r.db.Create(&appointment).Error
	}

	return InvalidAppointmentAssertionError
}

func (r *AppointmentRepository) FindBetweenDates(calID uint, start, finish time.Time) ([]Model, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var appointments []*model.Appointment

	err := r.db.
		Preload("DeletedDates").
		Preload("Alarms").
		Where(`
				calendar_id = ? AND
				start IS NOT NULL AND start <= ?
				AND (recurrence_rule != '' OR (start <= ? AND start >= ?))
			`, calID, finish, finish, start).
		Order("start asc").
		Find(&appointments).
		Error

	return r.modelSlice(appointments), err
}

func (r *AppointmentRepository) FindByID(calID, uid uint) (model.Appointment, error) {
	var appointment model.Appointment

	err := r.db.Preload("DeletedDates").Preload("Alarms").First(&appointment, "id = ? AND calendar_id = ?", uid, calID).Error

	return appointment, err
}

func (r *AppointmentRepository) DeleteItem(calID, uid uint) error {
	opts, err := r.opts.FindByCalendarID(calID)

	if err != nil {
		return err
	}

	if opts.SoftDelete {
		delCal, err := r.cal.FindOrCreateForDeletedID(calID)

		if err != nil {
			return err
		}

		log.Printf("Performing soft delete on appointment uid: %d to cal: %d", uid, delCal.ID)

		return r.db.Model(&model.Appointment{}).Where("id = ?", uid).Update("calendar_id", delCal.ID).Error
	} else {
		return r.db.Delete(model.Appointment{}, "id = ? AND calendar_id = ?", uid, calID).Error
	}
}

func (r *AppointmentRepository) DeleteRecurrence(itemID uint, date time.Time) error {
	deletedDate := model.AppointmentDeletedDate{
		DeletedDate: model.DeletedDate{
			Date: date,
		},
		AppointmentID: itemID,
	}

	return r.db.Create(&deletedDate).Error
}

func (r *AppointmentRepository) Update(calID, itemID uint, new Model) error {
	appointment, err := r.FindByID(calID, itemID)

	if err != nil {
		return err
	}

	if updates, ok := new.(*model.Appointment); ok {
		tx := r.db.Begin()

		var alarmIDs []uint

		for _, alarm := range updates.Alarms {
			alarmIDs = append(alarmIDs, alarm.ID)
		}

		if len(alarmIDs) > 0 {
			err := tx.Where("appointment_id = ? AND id NOT IN (?)", itemID, alarmIDs).Delete(model.AppointmentAlarm{}).Error

			if err != nil {
				tx.Rollback()
				return err
			}
		}

		if updates.RecurrenceRule == "" {
			err = tx.Model(&appointment).Update("recurrence_rule", updates.RecurrenceRule).Error

			if err != nil {
				tx.Rollback()
				return err
			}
		}

		return tx.Model(&appointment).Updates(updates).Commit().Error
	}

	return InvalidAppointmentAssertionError
}
