package entity

import (
	"time"
	"errors"
	"math/rand"

	"github.com/heindl/caldav-go/icalendar"
	"github.com/heindl/caldav-go/icalendar/values"
)

func init() {
	rand.Seed(time.Now().UnixNano())
}

var (
	letterRunes = []rune("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890")

	invalidRecurrenceRuleError = errors.New("invalid recurrence rule")
)

type Model struct {
	ID        uint      `gorm:"primary_key" json:"id" validator:"len=0"`
	CreatedAt time.Time `json:"created_at" validator:"len=0"`
	UpdatedAt time.Time `json:"updated_at" validator:"len=0"`
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
	Text           string        `json:"text" validator:"min=1"`
	Owner          string        `json:"owner"`
	UID            string        `json:"uid" validator:"len=0"`
	UIDPersistent  bool          `json:"uid_persistent" gorm:"column:uid_persistent"`
	RemindStart    int64         `json:"remind_start"`
	Hilite         string        `json:"hilite"`
	Todo           bool          `json:"todo"`
	Done           bool          `json:"done"`
	Start          time.Time     `json:"start"`
	Finish         time.Time     `json:"finish"`
	RecurrenceRule string        `json:"recurrence_rule"`
	CalendarID     uint          `json:"calendar_id"`
	Deleted        []DeletedDate `json:"deleted"`
}

func (i *Item) BeforeCreate() error {
	i.UID = generateUID()
	i.UIDPersistent = true

	// validate recurrence rule
	if i.RecurrenceRule != "" {
		var rrule values.RecurrenceRule

		err := icalendar.Unmarshal(i.RecurrenceRule, &rrule)

		if err != nil {
			return invalidRecurrenceRuleError
		}
	}

	return nil
}

type Appointment struct {
	Item
	Timezone string  `json:"timezone"`
	Alarms   []Alarm `json:"alarms"`
}

type Note struct {
	Item
}

type Alarm struct {
	Model
	Time          int64
	AppointmentID uint
}

type DeletedDate struct {
	Model
	Date time.Time
}

type Option struct {
	Model
	Name  string
	Value string
}

type Calendar struct {
	Model
	Version      float64
	Appointments []Appointment
	Notes        []Note
	Options      []Option
	Hidden       bool
}
