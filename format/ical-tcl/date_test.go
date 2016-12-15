package icaltcl

import (
	"testing"
	"time"

	"github.com/cj123/calendar/entity"
	ical "github.com/heindl/caldav-go/icalendar/values"
)

func TestReadSingleDate(t *testing.T) {
	l := NewICalLexer("Single 20/9/2016")
	d := newDateSet()
	r := newDateReader(l, d)

	r.read()

	if d.RecurrenceRule != nil || d.Start.IsZero() || !d.Finish.IsZero() {
		t.Fail()
	}
}

func TestReadDays(t *testing.T) {
	l := NewICalLexer(`
            Days 8/10/2016 1
            Start 8/10/2016
            Finish 31/12/2016 End`)
	d := newDateSet()
	r := newDateReader(l, d)

	r.read()

	if d.RecurrenceRule == nil || d.Start.IsZero() || d.Finish.IsZero() {
		t.Fail()
	}
}

func TestReadMonths(t *testing.T) {
	l := NewICalLexer(`

            Months 8/10/2016 3
            Start 8/10/2016 End
    `)
	d := newDateSet()
	r := newDateReader(l, d)

	r.read()

	if d.RecurrenceRule == nil || d.Start.IsZero() || !d.Finish.IsZero() {
		t.Fail()
	}
}

func TestReadWeekDays(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		l := NewICalLexer(`
            WeekDays  1 2 3 4 7 Months  1 2 3 4 5 6 7 8 9 10 11 12
            Start 8/10/2016 End`)
		d := newDateSet()
		r := newDateReader(l, d)

		r.read()

		if d.RecurrenceRule == nil || d.Start.IsZero() || !d.Finish.IsZero() {
			t.Fail()
		}

		if d.RecurrenceRule.Frequency != ical.WeekRecurrenceFrequency {
			t.Fail()
		}
	})

	t.Run("Too many week days", func(t *testing.T) {
		l := NewICalLexer(`
            WeekDays  1 2 3 4 7 8 9 10 Months  1 2 3 4 5 6 7 8 9 10 11 12
            Start 8/10/2016 End`)
		d := newDateSet()
		r := newDateReader(l, d)

		r.read()

		// extra days should be ignored, the result will be exactly the same as the test above
		if d.RecurrenceRule == nil || d.Start.IsZero() || !d.Finish.IsZero() {
			t.Fail()
		}

		if d.RecurrenceRule.Frequency != ical.WeekRecurrenceFrequency {
			t.Fail()
		}
	})

	t.Run("Too many months", func(t *testing.T) {
		l := NewICalLexer(`
            WeekDays  1 2 3 4 7 8 9 10 Months    1 14 20 333
            Start 8/10/2016 End`)
		d := newDateSet()
		r := newDateReader(l, d)

		r.read()

		// extra days should be ignored, the result will be exactly the same as the test above
		if d.RecurrenceRule == nil || d.Start.IsZero() || !d.Finish.IsZero() {
			t.Fail()
		}

		if d.RecurrenceRule.Frequency != ical.WeekRecurrenceFrequency {
			t.Fail()
		}
	})
}

func TestReadMonthDays(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		l := NewICalLexer(`
            MonthDays  1 2 3 4 7 Months  1 2 3 4
            Start 8/10/2016
            Finish 8/10/2020 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		r.read()

		if d.Start.IsZero() || d.Finish.IsZero() || d.RecurrenceRule == nil {
			t.Fail()
		}

		if d.RecurrenceRule.Frequency != ical.MonthRecurrenceFrequency {
			t.Fail()
		}
	})

	t.Run("Invalid keyword", func(t *testing.T) {
		l := NewICalLexer(`
            MonthDays  1 2 3 4 7 Years  1 2 3 4
            Start 8/10/2016
            Finish 8/10/2020 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err == nil {
			t.Fail()
		}
	})
}

func TestReadComplexMonths(t *testing.T) {
	t.Run("Backward", func(t *testing.T) {
		l := NewICalLexer(`
            ComplexMonths 1 3 8/10/2016 Backward ByWeek 7
            Start 8/10/2016 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err != nil {
			t.Error(err)
		}

		if d.RecurrenceRule == nil || d.RecurrenceRule.Frequency != ical.MonthRecurrenceFrequency {
			t.Fail()
		}
	})

	t.Run("Backward Invalid Weekday", func(t *testing.T) {
		l := NewICalLexer(`
            ComplexMonths 1 3 8/10/2016 Backward ByWeek 9
            Start 8/10/2016 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err == nil {
			t.Fail()
		}
	})

	t.Run("Backward Invalid Repetition Type", func(t *testing.T) {
		l := NewICalLexer(`
            ComplexMonths 1 3 8/10/2016 Backward ByAnnum 5
            Start 8/10/2016 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err == nil {
			t.Fail()
		}
	})

	t.Run("Forward Weekday", func(t *testing.T) {
		l := NewICalLexer(`
            ComplexMonths 1 12 16/11/2016 Forward ByWorkDay
            Start 16/11/2016 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err != nil {
			t.Error(err)
		}

		if d.RecurrenceRule == nil || d.RecurrenceRule.Frequency != ical.MonthRecurrenceFrequency {
			t.Fail()
		}
	})

	t.Run("Backward byday", func(t *testing.T) {
		l := NewICalLexer(`
            ComplexMonths 1 15 16/11/2016 Backward ByDay
            Start 16/11/2016 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err != nil {
			t.Error(err)
		}

		if d.RecurrenceRule == nil || d.RecurrenceRule.Frequency != ical.MonthRecurrenceFrequency {
			t.Fail()
		}
	})

	t.Run("Invalid direction", func(t *testing.T) {
		l := NewICalLexer(`
            ComplexMonths 1 12 16/11/2016 Left ByWorkDay
            Start 16/11/2016 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err == nil {
			t.Fail()
		}
	})

	t.Run("Invalid date type", func(t *testing.T) {
		l := NewICalLexer(`
            DifficultYears 1 12 16/11/2016 Left ByWorkDay
            Start 16/11/2016 End
        `)
		d := newDateSet()
		r := newDateReader(l, d)

		err := r.read()

		if err == nil {
			t.Fail()
		}
	})
}

func TestDateSetMapToItem(t *testing.T) {
	var err error

	d := newDateSet()
	d.Start, err = time.Parse(icalDateFormat, "16/11/2016")

	if err != nil {
		t.Error(err)
	}

	d.StartTime = 600
	d.Length = 60

	item := entity.Item{}

	d.mapToItem(&item)

	h, m, s := item.Start.Clock()

	if h != 10 || m != 0 || s != 0 {
		t.Fail()
	}

	t.Run("With Recurrence Rule", func(t *testing.T) {
		d.RecurrenceRule = &ical.RecurrenceRule{
			Frequency: ical.MonthRecurrenceFrequency,
			Interval:  2,
		}

		d.Finish = d.Start.Add(time.Hour)

		d.mapToItem(&item)

		if item.RecurrenceRule == "" {
			t.Fail()
		}
	})
}
