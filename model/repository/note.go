package repository

import (
	"errors"
	"time"

	"github.com/cj123/calendar/model"
	"github.com/jinzhu/gorm"
)

type NoteRepository struct {
	Repository
}

func NewNoteRepository(db *gorm.DB) *NoteRepository {
	return &NoteRepository{
		Repository{
			db: db,
		},
	}
}

func (r *NoteRepository) Model() Model {
	return &model.Note{}
}

func (r *NoteRepository) Create(calID uint, m Model) error {
	if note, ok := m.(*model.Note); ok {
		note.CalendarID = calID

		return r.db.Create(&note).Error
	}

	return errors.New("invalid note type")
}

func (r *NoteRepository) FindByID(calID, uid uint) (model.Note, error) {
	var note model.Note

	err := r.db.Preload("DeletedDates").First(&note, "id = ? AND calendar_id = ?", uid, calID).Error

	return note, err
}

func (r *NoteRepository) modelSlice(notes []*model.Note) []Model {
	models := make([]Model, len(notes))

	for i, note := range notes {
		models[i] = note
	}

	return models
}

func (r *NoteRepository) FindBetweenDates(calID uint, start, finish time.Time) ([]Model, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var notes []*model.Note

	err := r.db.
		Preload("DeletedDates").
		Where(`
				calendar_id = ? AND
				start IS NOT NULL AND start <= ?
				AND (recurrence_rule != '' OR (start <= ? AND start >= ?))
			`, calID, finish, finish, start).
		Order("id asc").
		Find(&notes).
		Error

	return r.modelSlice(notes), err
}

func (r *NoteRepository) DeleteItem(calID, uid uint) error {
	return r.db.Delete(model.Note{}, "id = ? AND calendar_id = ?", uid, calID).Error
}

func (r *NoteRepository) DeleteRecurrence(itemID uint, date time.Time) error {
	deletedDate := model.NoteDeletedDate{
		DeletedDate: model.DeletedDate{
			Date: date,
		},
		NoteID: itemID,
	}

	return r.db.Create(&deletedDate).Error
}

func (r *NoteRepository) Update(calID, itemID uint, new Model) error {
	note, err := r.FindByID(calID, itemID)

	if err != nil {
		return err
	}

	if updates, ok := new.(*model.Note); ok {
		return r.db.Model(&note).Updates(updates).Error
	}

	return InvalidAppointmentAssertionError
}
