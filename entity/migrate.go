package entity

import "github.com/jinzhu/gorm"

func Migrate(db *gorm.DB) {
	db.LogMode(true)

	db.AutoMigrate(
		&AppointmentDeletedDate{},
		&NoteDeletedDate{},
		&Alarm{},
		&Appointment{},
		&Note{},
		&Option{},
		&Calendar{},
	)
}
