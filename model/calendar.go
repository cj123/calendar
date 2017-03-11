package model

import (
	"errors"
	"math/rand"
	"strings"
	"time"

	"github.com/heindl/caldav-go/icalendar"
	"github.com/heindl/caldav-go/icalendar/values"
)

const (
	AppointmentItemType = "appointment"
	NoteItemType        = "note"
)

func init() {
	rand.Seed(time.Now().UnixNano())
}

var (
	letterRunes = []rune("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890")

	invalidRecurrenceRuleError = errors.New("invalid recurrence rule")
)

type Model struct {
	ID        uint      `gorm:"primary_key" json:"id"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

func generateUID() string {
	b := make([]rune, 40)
	for i := range b {
		b[i] = letterRunes[rand.Intn(len(letterRunes))]
	}

	return "myCal_" + string(b)
}

type Item struct {
	Model
	Text           string    `json:"text" validate:"required" gorm:"type:text"`
	Owner          string    `json:"owner"`
	UID            string    `json:"uid" gorm:"unique_index:idx_calid_uid"`
	UIDPersistent  bool      `json:"uid_persistent" gorm:"column:uid_persistent"`
	RemindStart    int64     `json:"remind_start"`
	Hilite         string    `json:"hilite"`
	Todo           bool      `json:"todo"`
	Done           bool      `json:"done"`
	Start          time.Time `json:"start"`
	Finish         time.Time `json:"finish"`
	RecurrenceRule string    `json:"recurrence_rule"`
	CalendarID     uint      `json:"calendar_id" gorm:"unique_index:idx_calid_uid"`
	DataType       string    `json:"data_type"`
}

func (i *Item) GetID() uint {
	return i.Model.ID
}

func (i *Item) BeforeCreate() error {
	if i.UID == "" {
		i.UID, i.UIDPersistent = generateUID(), true
	}

	if i.Hilite == "" {
		i.Hilite = "always"
	}

	// validate recurrence rule
	if i.RecurrenceRule != "" {
		i.RecurrenceRule = strings.TrimSpace(strings.Replace(i.RecurrenceRule, "RRULE:", "", -1))

		var rrule values.RecurrenceRule

		err := icalendar.Unmarshal("RRULE:"+i.RecurrenceRule, &rrule)

		if err != nil {
			return invalidRecurrenceRuleError
		}
	}

	return nil
}

type Note struct {
	Item

	// DeletedDates cannot be inherited since we need to use gorm's preloading
	// which does not support preloading on anonymous structs.
	DeletedDates []NoteDeletedDate `json:"deleted"`
}

func (n *Note) Name() string {
	return "note"
}

func (n *Note) BeforeCreate() error {
	err := n.Item.BeforeCreate()

	if err != nil {
		return err
	}

	n.Item.DataType = NoteItemType

	return nil
}

type Alarm struct {
	Model
	Time uint `json:"time"`
}

type DefaultAlarm struct {
	Alarm

	CalendarOptionsID uint `json:"calendar_id"`
}

type DeletedDate struct {
	Model
	Date time.Time `json:"date"`
}

type NoteDeletedDate struct {
	DeletedDate

	NoteID uint `json:"-"`
}

type Calendar struct {
	Model
	Name         string
	DeletedFor   uint      // which calendar is this the deleted calendar for?
	Version      float64
	Appointments []Appointment
	Notes        []Note
	Options      CalendarOptions
	Hidden       bool
}
