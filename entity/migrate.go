package entity

import "github.com/jinzhu/gorm"

func Migrate(db *gorm.DB) {
	db.AutoMigrate(&Calendar{})
	db.AutoMigrate(&Appointment{})
	db.AutoMigrate(&Note{})
	db.AutoMigrate(&Option{})
	db.AutoMigrate(&Alarm{})
	db.AutoMigrate(&DeletedDate{})
}
