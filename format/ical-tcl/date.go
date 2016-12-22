package icaltcl

import (
	"errors"
	"regexp"
	"time"

	"github.com/cj123/calendar/entity"
	ical "github.com/heindl/caldav-go/icalendar/values"
)

type dateType string
type day string

type dateSet struct {
	Start          time.Time
	Finish         time.Time
	Deleted        []time.Time
	RecurrenceRule *ical.RecurrenceRule
	Length         int64
	StartTime      int64
}

func newDateSet() *dateSet {
	return &dateSet{
		Deleted: make([]time.Time, 0, 10),
	}
}

// @TODO map deleted dates to something too.
func (d *dateSet) mapToItem(i *entity.Item) error {
	i.Start = d.Start.Add(time.Duration(d.StartTime) * time.Minute)
	i.Finish = d.Start.Add(time.Duration(d.StartTime+d.Length) * time.Minute)

	if d.RecurrenceRule != nil {
		if !d.Finish.IsZero() {
			d.RecurrenceRule.Until = ical.NewDateTime(d.Finish.Add(time.Duration(d.StartTime+d.Length) * time.Minute))
		}

		rrule, err := d.RecurrenceRule.EncodeICalValue()

		if err != nil {
			return err
		}

		i.RecurrenceRule = rrule
	}

	return nil
}

func (d *dateSet) mapToAppointment(a *entity.Appointment) error {
	err := d.mapToItem(&a.Item)

	if err != nil {
		return err
	}

	if a.DeletedDates == nil {
		a.DeletedDates = make([]entity.AppointmentDeletedDate, 0, 10)
	}

	// deleted dates must be separate
	for _, deleted := range d.Deleted {
		a.DeletedDates = append(a.DeletedDates, entity.AppointmentDeletedDate{
			DeletedDate: entity.DeletedDate{
				Date: deleted,
			},
		})
	}

	return err
}

func (d *dateSet) mapToNote(n *entity.Note) error {
	err := d.mapToItem(&n.Item)

	if err != nil {
		return err
	}

	if n.DeletedDates == nil {
		n.DeletedDates = make([]entity.NoteDeletedDate, 0, 10)
	}

	// deleted dates must be separate
	for _, deleted := range d.Deleted {
		n.DeletedDates = append(n.DeletedDates, entity.NoteDeletedDate{
			DeletedDate: entity.DeletedDate{
				Date: deleted,
			},
		})
	}

	return err
}

var days = map[int]ical.RecurrenceWeekday{
	1: ical.SundayRecurrenceWeekday,
	2: ical.MondayRecurrenceWeekday,
	3: ical.TuesdayRecurrenceWeekday,
	4: ical.WednesdayRecurrenceWeekday,
	5: ical.ThursdayRecurrenceWeekday,
	6: ical.FridayRecurrenceWeekday,
	7: ical.SaturdayRecurrenceWeekday,
}

type dateReader struct {
	l Lexer
	d *dateSet
}

func newDateReader(l Lexer, d *dateSet) *dateReader {
	return &dateReader{l: l, d: d}
}

func (r *dateReader) read() error {
	r.l.SkipWhitespace()

	dateType, err := r.l.GetID()

	if err != nil {
		return err
	}

	r.d.RecurrenceRule = &ical.RecurrenceRule{}

	switch dateType {
	case "Single":
		err = r.readSingle()
		break
	case "Days":
		err = r.readDays()
		break
	case "Months":
		err = r.readMonths()
		break
	case "ComplexMonths":
		err = r.readComplexMonths()
		break
	case "WeekDays", "MonthDays":
		err = r.readWeekOrMonthDays(dateType)
		break
	default:
		return errors.New("invalid date type: " + dateType)
	}

	if err != nil {
		return err
	}

	for {
		r.l.SkipWhitespace()
		keyword, err := r.l.GetID()

		if err != nil {
			// likely we're at the end, move on
			break
		}

		switch keyword {
		case "End":
			return nil
		case "Start":
			r.l.SkipWhitespace()

			date, err := r.l.GetDate()

			if err != nil {
				return err
			}

			r.d.Start = *date

			break
		case "Finish":
			r.l.SkipWhitespace()

			date, err := r.l.GetDate()

			if err != nil {
				return err
			}

			r.d.Finish = *date

			break

		case "Deleted":
			r.l.SkipWhitespace()

			date, err := r.l.GetDate()

			if err != nil {
				return err
			}

			r.d.Deleted = append(r.d.Deleted, *date)

			break

		default:
			return errors.New("unrecognised dateset keyword: " + keyword)
		}
	}

	return err
}

func (r *dateReader) readSingle() error {
	r.l.SkipWhitespace()
	date, err := r.l.GetDate()

	if err != nil {
		return err
	}

	r.d.RecurrenceRule = nil
	r.d.Start = *date

	return err
}

func (r *dateReader) readDays() error {
	r.l.SkipWhitespace()
	anchor, err := r.l.GetDate()

	if err != nil {
		return err
	}

	r.l.SkipWhitespace()

	interval, err := r.l.GetNumber()

	if err != nil {
		return err
	}

	r.d.RecurrenceRule.Frequency = ical.DayRecurrenceFrequency
	r.d.RecurrenceRule.Interval = int(interval)
	r.d.Start = *anchor

	return err
}

func (r *dateReader) readMonths() error {
	r.l.SkipWhitespace()
	anchor, err := r.l.GetDate()

	if err != nil {
		return err
	}

	r.l.SkipWhitespace()

	interval, err := r.l.GetNumber()

	if err != nil {
		return err
	}

	r.d.RecurrenceRule.Frequency = ical.MonthRecurrenceFrequency
	r.d.RecurrenceRule.Interval = int(interval)
	r.d.Start = *anchor

	return err
}

func (r *dateReader) readComplexMonths() error {
	r.l.SkipWhitespace()

	interval, err := r.l.GetNumber()

	if err != nil {
		return err
	}

	r.l.SkipWhitespace()
	count, err := r.l.GetNumber()

	if err != nil {
		return err
	}

	anchor, err := r.l.GetDate()

	if err != nil {
		return err
	}

	r.l.SkipWhitespace()
	direction, err := r.l.GetID()

	if err != nil {
		return err
	}

	var sign int

	if direction == "Backward" {
		// count from the end of the month
		sign = -1
	} else if direction == "Forward" {
		// count from the beginning of the month
		sign = +1
	} else {
		return errors.New("ComplexMonths format must be either Forward or Backward. Neither were foun")
	}

	r.l.SkipWhitespace()
	repetition, err := r.l.GetID()

	if err != nil {
		return err
	}

	r.d.RecurrenceRule.Frequency = ical.MonthRecurrenceFrequency

	if repetition == "ByDay" {
		r.d.RecurrenceRule.BySetPosition = []int{sign * int(count)}
	} else if repetition == "ByWorkDay" {
		r.d.RecurrenceRule.ByDay = []ical.RecurrenceWeekday{
			ical.MondayRecurrenceWeekday,
			ical.TuesdayRecurrenceWeekday,
			ical.WednesdayRecurrenceWeekday,
			ical.ThursdayRecurrenceWeekday,
			ical.FridayRecurrenceWeekday,
		}
		r.d.RecurrenceRule.BySetPosition = []int{sign * int(count)}
	} else if repetition == "ByWeek" {
		r.l.SkipWhitespace()

		weekDay, err := r.l.GetNumber()

		if err != nil {
			return err
		}

		if weekDay > 7 || weekDay < 1 {
			return errors.New("invalid weekday, must be in range 1 <= weekday <= 7")
		}

		r.d.RecurrenceRule.ByDay = []ical.RecurrenceWeekday{days[int(weekDay)]}
		r.d.RecurrenceRule.BySetPosition = []int{sign * int(count)}
	} else {
		return errors.New("unsupported repetition type: " + repetition)
	}

	r.d.Start = *anchor
	r.d.RecurrenceRule.Interval = int(interval)

	return err
}

func (r *dateReader) readWeekOrMonthDays(dateType string) error {
	r.l.SkipWhitespace()

	days, err := r.parseDays()

	if err != nil {
		return err
	}

	r.l.SkipWhitespace()

	keyword, err := r.l.GetID()

	if err != nil {
		return err
	}

	if keyword != "Months" {
		return errors.New("invalid keyword: " + keyword)
	}

	r.l.SkipWhitespace()
	months, err := r.parseMonths()

	if err != nil {
		return err
	}

	if dateType == "WeekDays" {
		r.d.RecurrenceRule.Frequency = ical.WeekRecurrenceFrequency
	} else if dateType == "MonthDays" {
		r.d.RecurrenceRule.Frequency = ical.MonthRecurrenceFrequency
	}

	r.d.RecurrenceRule.ByDay = days
	r.d.RecurrenceRule.ByMonth = months

	return err
}

func (r *dateReader) parseDays() ([]ical.RecurrenceWeekday, error) {
	recurringDays := make([]ical.RecurrenceWeekday, 0, 10)

	for {
		r.l.SkipWhitespace()

		peek := r.l.Peek()

		match, err := regexp.MatchString("[0-9]", peek)

		if err != nil {
			return nil, err
		}

		if !match {
			break
		}

		day, err := r.l.GetNumber()

		if err != nil {
			return nil, err
		}

		if day > 7 || day < 1 {
			continue
		}

		recurringDays = append(recurringDays, days[int(day)])
	}

	return recurringDays, nil
}

func (r *dateReader) parseMonths() ([]int, error) {
	months := make([]int, 0, 10)

	for {
		r.l.SkipWhitespace()

		peek := r.l.Peek()

		match, err := regexp.MatchString("[0-9]", peek)

		if err != nil {
			return nil, err
		}

		if !match {
			break
		}

		day, err := r.l.GetNumber()

		if err != nil {
			return nil, err
		}

		if day > 12 || day < 1 {
			continue
		}

		months = append(months, int(day))
	}

	return months, nil
}
