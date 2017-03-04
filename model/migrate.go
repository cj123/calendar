package model

import "github.com/jinzhu/gorm"

func Migrate(db *gorm.DB) error {
	return db.AutoMigrate(
		&AppointmentDeletedDate{},
		&NoteDeletedDate{},
		&AppointmentAlarm{},
		&DefaultAlarm{},
		&Appointment{},
		&Note{},
		&CalendarOptions{},
		&Calendar{},
	).Error
}
