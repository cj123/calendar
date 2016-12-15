package icaltcl

import (
	"errors"
	"regexp"

	"github.com/cj123/calendar/entity"
)

type Parser interface {
	Parse(Lexer, interface{}, string, *dateSet) error
}

type ItemParser struct{}

func (i *ItemParser) Parse(l Lexer, s interface{}, keyword string, set *dateSet) error {
	item := s.(*entity.Item)

	switch keyword {
	case "Remind":
		l.SkipWhitespace()

		num, err := l.GetNumber()

		if err != nil {
			return err
		}

		item.RemindStart = int64(num)
		break

	case "Owner":
		l.SkipWhitespace()
		owner := l.GetString()

		if owner == "" {
			return errors.New("unable to read owner information")
		}

		item.Owner = owner

		break

	case "Uid":
		l.SkipWhitespace()
		uid := l.GetUntil(CLOSE_STRING)

		if uid == "" {
			return errors.New("unable to read UID")
		}

		item.UID = uid
		item.UIDPersistent = true

		break

	case "Contents":
		item.Text = l.GetString()

		break

	case "Dates":
		r := newDateReader(l, set)
		r.read()

		l.GetUntil(CLOSE_STRING)

		break

	case "Hilite":
		hilite := l.GetString()

		if hilite == "" {
			return errors.New("unable to read hilite")
		}

		item.Hilite = hilite

		break

	case "Todo":
		item.Todo = true
		break

	case "Done":
		item.Done = true
		break
	}

	return nil
}

type AppointmentParser struct {
	*ItemParser
}

func (a *AppointmentParser) Parse(l Lexer, s interface{}, keyword string, set *dateSet) error {
	item := s.(*entity.Appointment)

	a.ItemParser.Parse(l, &item.Item, keyword, set)

	switch keyword {
	case "Start":
		l.SkipWhitespace()
		start, err := l.GetNumber()

		if err != nil {
			return err
		}

		set.StartTime = int64(start)

		break

	case "Length":
		l.SkipWhitespace()
		len, err := l.GetNumber()

		if err != nil {
			return err
		}

		set.Length = int64(len)

		break

	case "Timezone":
		l.SkipWhitespace()
		timezone := l.GetString()

		if timezone == "" {
			return errors.New("unable to read appointment timezone")
		}

		item.Timezone = timezone

		break

	case "Alarms":
		alarms := make([]entity.Alarm, 0, 10)

		for {
			l.SkipWhitespace()
			c := l.Peek()

			match, err := regexp.MatchString("[0-9]", c)

			if err != nil {
				return err
			}

			if !match {
				break
			}

			num, err := l.GetNumber()

			if err != nil {
				return err
			}

			alarms = append(alarms, entity.Alarm{
				Time: int64(num),
			})
		}

		item.Alarms = alarms

		break
	}

	return nil
}

type NoteParser struct {
	*ItemParser
}

func (n *NoteParser) Parse(l Lexer, s interface{}, keyword string, set *dateSet) error {
	note := s.(*entity.Note)

	return n.ItemParser.Parse(l, &note.Item, keyword, set)
}
