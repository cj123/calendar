package repository

import (
	"errors"
	"time"

	"github.com/cj123/calendar/model"
)

type NoteRepository struct {
	Repository
}

func (r *NoteRepository) Model() Model {
	return &model.Note{}
}

func (r *NoteRepository) Create(m Model) error {
	if note, ok := m.(*model.Note); ok {
		return r.DB.Create(&note).Error
	}

	return errors.New("invalid note type")
}

func (r *NoteRepository) FindByID(id interface{}) (model.Note, error) {
	var note model.Note

	err := r.DB.Preload("DeletedDates").First(&note, "id = ?", id).Error

	return note, err
}

func (r *NoteRepository) modelSlice(notes []*model.Note) []Model {
	models := make([]Model, len(notes))

	for i, note := range notes {
		models[i] = note
	}

	return models
}

func (r *NoteRepository) FindBetweenDates(start, finish time.Time) ([]Model, error) {
	finish = finish.Add((time.Hour * 24) - time.Second)

	var notes []*model.Note

	err := r.DB.
		Preload("DeletedDates").
		Where(`
				start IS NOT NULL AND start <= ?
				AND (recurrence_rule != '' OR (start <= ? AND start >= ?))
			`, finish, finish, start).
		Order("start asc").
		Find(&notes).
		Error

	return r.modelSlice(notes), err
}

func (r *NoteRepository) DeleteItem(uid uint) error {
	return r.DB.Delete(model.Note{}, "id = ?", uid).Error
}

func (r *NoteRepository) DeleteRecurrence(itemID uint, date time.Time) error {
	deletedDate := model.NoteDeletedDate{
		DeletedDate: model.DeletedDate{
			Date: date,
		},
		NoteID: itemID,
	}

	return r.DB.Create(&deletedDate).Error
}

func (r *NoteRepository) Update(itemID uint, new Model) error {
	note, err := r.FindByID(itemID)

	if err != nil {
		return err
	}

	if updates, ok := new.(*model.Note); ok {
		return r.DB.Model(&note).Updates(updates).Error
	}

	return InvalidAppointmentAssertionError
}
