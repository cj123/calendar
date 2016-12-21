package entity

import (
	"errors"
	"math/rand"
	"strings"
	"time"

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
	ID        uint      `gorm:"primary_key" json:"id" validate:"len=0"`
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
	Text           string        `json:"text" validate:"required"`
	Owner          string        `json:"owner"`
	UID            string        `json:"uid" validate:"len=0"`
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
	i.UID, i.UIDPersistent = generateUID(), true

	// validate recurrence rule
	if i.RecurrenceRule != "" {
		i.RecurrenceRule = strings.Replace(i.RecurrenceRule, "RRULE:", "", -1)

		var rrule values.RecurrenceRule

		err := icalendar.Unmarshal("RRULE:"+i.RecurrenceRule, &rrule)

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
	Date          time.Time
	AppointmentID uint
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
