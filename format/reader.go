package format

import (
	"errors"

	"github.com/cj123/calendar/entity"
	"github.com/cj123/calendar/format/ical-tcl"
	"github.com/cj123/calendar/format/ics"
)

type calendarType string

const (
	CalendarICS     calendarType = "ics"
	CalendarICalTCL calendarType = "ical-tcl"
)

var (
	invalidCalendarTypeError = errors.New("invalid calendar type")

	validCalendarTypes = map[calendarType]bool{
		CalendarICS:     true,
		CalendarICalTCL: true,
	}
)

type Reader interface {
	Read() (*entity.Calendar, error)
}

func ReadCalendar(b []byte, calType string) (*entity.Calendar, error) {
	var reader Reader

	if calType == "ical-tcl" {
		reader = icaltcl.NewCalendarReader(icaltcl.NewICalLexer(string(b)))
	} else if calType == "ics" {
		reader = ics.NewICSReader(string(b))
	} else {
		return nil, invalidCalendarTypeError
	}

	return reader.Read()
}

func IsValidCalendarType(cType string) bool {
	_, ok := validCalendarTypes[calendarType(cType)]

	return ok
}
