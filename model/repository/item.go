package repository

import (
	"time"

	"github.com/jinzhu/gorm"
)

type Model interface {
	GetID() uint
	Name() string
}

type ItemRepository interface {
	// Create an item
	Create(calID uint, m Model) error

	// Model gives a new instance of the model that is used in the repository
	// e.g. Note or Appointment
	Model() Model

	// Find Items between two given dates.
	FindBetweenDates(calID uint, start, finish time.Time) ([]Model, error)

	// Delete an entire Item, recurrences and all
	DeleteItem(calID uint, id uint) error

	// Delete a single recurrence from an item (create a DeletedDate)
	DeleteRecurrence(itemID uint, date time.Time) error

	// Update an item given by its ID by the values in "new"
	Update(calID uint, itemID uint, new Model) error
}

type Repository struct {
	db *gorm.DB
}

