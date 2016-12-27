package ics

import (
	"strconv"

	"github.com/cj123/calendar/model"

	"github.com/heindl/caldav-go/icalendar"
	"github.com/heindl/caldav-go/icalendar/components"
	"strings"
)

var escapeChars = [...]string{
	",",
}

type ICSReader struct {
	str string
}

func NewICSReader(str string) *ICSReader {
	return &ICSReader{str: str}
}

func stripEscapeChars(data string) string {
	for _, c := range escapeChars {
		data = strings.Replace(data, "\\"+c, c, -1)
	}

	return data
}

func (r *ICSReader) Read() (*model.Calendar, error) {
	cal := &model.Calendar{}

	ical := new(components.Calendar)

	err := icalendar.Unmarshal(r.str, ical)

	if err != nil {
		return nil, err
	}

	err = r.mapToCalendar(ical, cal)

	return cal, err
}

func (r *ICSReader) mapToCalendar(icsCal *components.Calendar, cal *model.Calendar) error {
	appointments := make([]model.Appointment, 10)

	for _, event := range icsCal.Events {
		appt := model.Appointment{}

		appt.Text = stripEscapeChars(event.Summary + "\n" + event.Description)
		appt.Start = event.DateStart.NativeTime()
		appt.Finish = event.DateEnd.NativeTime()

		// @TODO timezones

		if len(event.RecurrenceRules) > 0 {
			// hack: only use the 1st recurrence rule, no others
			rule := event.RecurrenceRules[0]

			ruleStr, err := rule.EncodeICalValue()

			if err != nil {
				return err
			}

			appt.RecurrenceRule = ruleStr

			if event.ExceptionDateTimes != nil && len(*event.ExceptionDateTimes) > 0 {
				appt.DeletedDates = make([]model.AppointmentDeletedDate, 10)

				for _, t := range *event.ExceptionDateTimes {
					appt.DeletedDates = append(appt.DeletedDates, model.AppointmentDeletedDate{
						DeletedDate: model.DeletedDate{
							Date: t.NativeTime(),
						},
					})
				}
			}
		}

		appt.UID = event.UID
		appt.UIDPersistent = true

		if event.Organizer == nil {
			appt.Owner = "unknown"
		} else {
			owner, err := event.Organizer.EncodeICalValue()

			if err != nil {
				return err
			}

			appt.Owner = stripEscapeChars(owner)
		}

		appointments = append(appointments, appt)
	}

	cal.Appointments = appointments

	v, err := strconv.ParseFloat(icsCal.Version, 10)

	if err != nil {
		return err
	}

	cal.Version = v

	return err
}
