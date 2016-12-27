package model

import "github.com/jinzhu/gorm"

func Migrate(db *gorm.DB) error {
	return db.AutoMigrate(
		&AppointmentDeletedDate{},
		&NoteDeletedDate{},
		&Alarm{},
		&Appointment{},
		&Note{},
		&Option{},
		&Calendar{},
	).Error
}
