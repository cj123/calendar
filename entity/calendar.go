package entity

import "time"

type Model struct {
	ID        uint      `gorm:"primary_key" json:"id"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

type Item struct {
	Model
	Text           string        `json:"text"`
	Owner          string        `json:"owner"`
	UID            string        `json:"uid"`
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
