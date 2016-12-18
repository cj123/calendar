package format

import (
	"github.com/cj123/calendar/entity"
	"github.com/cj123/calendar/format/ical-tcl"
	"github.com/cj123/calendar/format/ics"
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
	}

	return reader.Read()
}
