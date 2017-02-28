package repository

import (
	"errors"
	"time"

	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
	"github.com/davecgh/go-spew/spew"
)

var (
	InvalidAppointmentAssertionError = errors.New("invalid appointment type")
)

type AppointmentRepository struct {
	Repository
}

func NewAppointmentRepository(db *gorm.DB) *AppointmentRepository {
	return &AppointmentRepository{
		Repository{
			db: db,
		},
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

func (r *AppointmentRepository) Create(m Model) error {
	if appointment, ok := m.(*model.Appointment); ok {
		return r.db.Create(&appointment).Error
	}

	return InvalidAppointmentAssertionError
}

func (r *AppointmentRepository) FindBetweenDates(start, finish time.Time) ([]Model, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var appointments []*model.Appointment

	err := r.db.
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

	err := r.db.Preload("DeletedDates").Preload("Alarms").First(&appointment, "id = ?", id).Error

	return appointment, err
}

func (r *AppointmentRepository) DeleteItem(uid uint) error {
	return r.db.Delete(model.Appointment{}, "id = ?", uid).Error
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

func (r *AppointmentRepository) Update(itemID uint, new Model) error {
	appointment, err := r.FindByID(itemID)

	if err != nil {
		return err
	}

	spew.Dump(appointment)

	if updates, ok := new.(*model.Appointment); ok {
		/*var alarmIDs []uint

		for _, alarm := range updates.Alarms {
			alarmIDs = append(alarmIDs, alarm.ID)
		}

		spew.Dump(alarmIDs)

		if len(alarmIDs) > 0 {
			err := r.db.Where("appointment_id NOT IN (?)", alarmIDs).Delete(model.Alarm{}).Error

			if err != nil {
				return err
			}
		}*/

		return r.db.Model(&appointment).Updates(updates).Error
	}

	return InvalidAppointmentAssertionError
}
