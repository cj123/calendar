package icaltcl

import (
	"errors"
	"strconv"

	"github.com/cj123/calendar/model"
)

const (
	MIN_VERSION = 2.0
	MAX_VERSION = 3.0
)

type ItemReader struct {
	l   Lexer
	p   Parser
	i   interface{}
	set *dateSet
}

func NewItemReader(l Lexer, p Parser, i interface{}, set *dateSet) *ItemReader {
	return &ItemReader{
		l:   l,
		p:   p,
		i:   i,
		set: set,
	}
}

func (r *ItemReader) Read() error {
	for {
		r.l.SkipWhitespace()

		char := r.l.Peek()

		if char == "" {
			return errors.New("incomplete item")
		}

		if char == string(CLOSE_STRING) {
			break
		}

		// get property name
		keyword, err := r.l.GetID()

		if err != nil {
			return err
		}

		r.l.SkipWhitespace()
		r.l.SkipOpeningDelimiter()

		// read property
		r.p.Parse(r.l, r.i, keyword, r.set)

		r.l.SkipWhitespace()
		r.l.SkipClosingDelimiter()
	}

	return nil
}

type CalendarReader struct {
	l Lexer
}

func NewCalendarReader(l Lexer) *CalendarReader {
	return &CalendarReader{l: l}
}

func (c *CalendarReader) getVersion() (float64, error) {
	c.l.SkipWhitespace()

	err := c.l.Skip("Calendar")

	if err != nil {
		return -1, err
	}

	c.l.SkipWhitespace()

	err = c.l.SkipOpeningDelimiter()
	if err != nil {
		return -1, err
	}

	err = c.l.Skip("v")
	if err != nil {
		return -1, err
	}

	versionStr := c.l.GetUntil(']')

	version, err := strconv.ParseFloat(versionStr, 10)

	if err != nil {
		return -1, err
	}

	if version < MIN_VERSION || version >= MAX_VERSION {
		return version, errors.New("invalid calendar version")
	}

	c.l.GetUntil(CLOSE_STRING)
	c.l.SkipClosingDelimiter()

	return version, err
}

func (c *CalendarReader) Read() (*model.Calendar, error) {
	if c.l.Status() == ERROR {
		return nil, errors.New("Bad lexer status")
	}

	calendar := new(model.Calendar)
	calendar.Options = model.DefaultCalendarOptions()

	version, err := c.getVersion()

	if err != nil {
		return nil, err
	}

	calendar.Version = version

	calendar.Appointments = make([]model.Appointment, 0, 10)
	calendar.Notes = make([]model.Note, 0, 10)

	for {
		c.l.SkipWhitespace()

		keyword, err := c.l.GetID()

		if err != nil {
			return nil, err
		}

		c.l.SkipWhitespace()
		err = c.l.SkipOpeningDelimiter()

		if err != nil {
			if c.l.Status() == EOF {
				break
			}

			return nil, err
		}

		switch keyword {
		case "Appt":

			parser := new(AppointmentParser)
			item := model.Appointment{Item: model.Item{}}
			set := newDateSet()
			reader := NewItemReader(c.l, parser, &item, set)

			err := reader.Read()

			if err != nil {
				return nil, err
			}

			err = set.mapToAppointment(&item)

			if err != nil {
				return nil, err
			}

			calendar.Appointments = append(calendar.Appointments, item)

			break

		case "Note":
			parser := new(NoteParser)
			item := model.Note{Item: model.Item{}}
			set := newDateSet()
			reader := NewItemReader(c.l, parser, &item, set)

			err := reader.Read()

			if err != nil {
				return nil, err
			}

			err = set.mapToNote(&item)

			if err != nil {
				return nil, err
			}

			calendar.Notes = append(calendar.Notes, item)

			break
		case "IncludeCalendar":
			break

		case "Hide":
			calendar.Hidden = true
			break

		case "DefaultAlarms":
			alarmUints, err := parseUintList(c.l)

			if err != nil {
				return nil, err
			}

			alarms := make([]model.Alarm, 0, 10)

			for _, alarm := range alarmUints {
				alarms = append(alarms, model.Alarm{
					Time: alarm,
				})
			}

			calendar.Options.DefaultAlarms = alarms

			break
		default:
			calendar.Options.Set(keyword, c.l.GetString())
		}

		c.l.SkipWhitespace()
		err = c.l.SkipClosingDelimiter()

		if err != nil {
			return nil, err
		}
	}

	return calendar, err
}
