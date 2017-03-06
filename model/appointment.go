package model

import (
	"github.com/jinzhu/gorm"
	"log"
)

type Appointment struct {
	Item
	Timezone          string             `json:"timezone"`
	Alarms            []AppointmentAlarm `json:"alarms"`
	SkipDefaultAlarms bool               `json:"skip_default_alarms"`

	// DeletedDates cannot be inherited since we need to use gorm's preloading
	// which does not support preloading on anonymous structs.
	DeletedDates []AppointmentDeletedDate `json:"deleted"`
}

func (a *Appointment) BeforeCreate(tx *gorm.DB) error {
	err := a.Item.BeforeCreate()

	if err != nil {
		return err
	}

	a.Item.DataType = AppointmentItemType

	return nil
}

func (a *Appointment) AfterCreate(tx *gorm.DB) error {
	if !a.SkipDefaultAlarms && a.Alarms == nil {
		log.Printf("Appointment uid: %s does not have any alarms associated with it. Adding...", a.UID)

		var opts CalendarOptions

		err := tx.Model(CalendarOptions{}).Where("calendar_id = ?", a.CalendarID).First(&opts).Error

		if err == gorm.ErrRecordNotFound {
			// calendar options don't exist for some reason, create them
			opts = DefaultCalendarOptions()

			err = tx.Create(&opts).Error

			if err != nil {
				log.Printf("Could not create default options, err: %s", err)
				return err
			}
		} else if err != nil {
			log.Printf("could not load options, err: %s", err)
			return err
		}

		alarms := make([]AppointmentAlarm, len(opts.DefaultAlarms))

		for i, defaultAlarm := range opts.DefaultAlarms {
			alarms[i] = AppointmentAlarm{Alarm: defaultAlarm.Alarm, AppointmentID: a.ID}
		}

		app := *a

		app.Alarms = alarms

		// update the alarms
		err = tx.Model(a).Updates(app).Error

		if err != nil {
			return err
		}
	}

	return nil
}

func (a *Appointment) Name() string {
	return "appointment"
}

type AppointmentAlarm struct {
	Alarm
	AppointmentID uint `json:"appointment_id"`
}

type AppointmentDeletedDate struct {
	DeletedDate

	AppointmentID uint `json:"-"`
}
