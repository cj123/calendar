package format

import (
	"errors"

	"github.com/cj123/calendar/format/ical-tcl"
	"github.com/cj123/calendar/format/ics"
	"github.com/cj123/calendar/model"
)

type CalendarType string

const (
	CalendarICS     CalendarType = "ics"
	CalendarICalTCL CalendarType = "ical-tcl"
)

var (
	invalidCalendarTypeError = errors.New("invalid calendar type")

	validCalendarTypes = map[CalendarType]bool{
		CalendarICS:     true,
		CalendarICalTCL: true,
	}
)

type Reader interface {
	Read() (*model.Calendar, error)
}

func ReadCalendar(b []byte, calType CalendarType) (*model.Calendar, error) {
	var reader Reader

	switch calType {
	case CalendarICalTCL:
		reader = icaltcl.NewCalendarReader(icaltcl.NewICalLexer(string(b)))
		break
	case CalendarICS:
		reader = ics.NewICSReader(string(b))
		break
	default:
		return nil, invalidCalendarTypeError
	}

	return reader.Read()
}

func IsValidCalendarType(cType string) bool {
	_, ok := validCalendarTypes[CalendarType(cType)]

	return ok
}
