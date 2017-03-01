package icaltcl

import (
	"github.com/cj123/calendar/model"
	"testing"
)

type testParser struct {
	t          *testing.T
	checkLexer bool
}

func (p *testParser) Parse(l Lexer, s interface{}, keyword string, set *dateSet) error {
	if p.checkLexer && l.Next() != "d" {
		p.t.Fail()
	}

	return nil
}

func TestItemReader_Read(t *testing.T) {
	t.Run("Read", func(t *testing.T) {
		l := NewICalLexer("TestParser [d]]") // second ] to signal end of input
		item := &model.Item{}
		r := NewItemReader(l, &testParser{t: t, checkLexer: true}, &item, nil)
		r.Read()

		l.Next()

		if l.Status() != EOF {
			t.Fail()
		}
	})

	t.Run("Incomplete Item", func(t *testing.T) {
		l := NewICalLexer("TestParser []")
		item := &model.Item{}
		r := NewItemReader(l, &testParser{t: t}, &item, nil)
		err := r.Read()

		if err == nil {
			t.Fail()
		}
	})
}

func TestCalendarReader_GetVersion(t *testing.T) {
	t.Run("Valid version", func(t *testing.T) {
		l := NewICalLexer("Calendar [v2.0]")
		r := NewCalendarReader(l)

		version, err := r.getVersion()

		if err != nil {
			t.Error(err)
		}

		if version != 2.0 {
			t.Fail()
		}
	})

	t.Run("Unsupported max", func(t *testing.T) {
		l := NewICalLexer("Calendar [v3.0]")
		r := NewCalendarReader(l)

		version, err := r.getVersion()

		if version != 3.0 || err == nil {
			t.Fail()
		}
	})

	t.Run("Unsupported min", func(t *testing.T) {
		l := NewICalLexer("Calendar [v1.99]")
		r := NewCalendarReader(l)

		version, err := r.getVersion()

		if version != 1.99 || err == nil {
			t.Fail()
		}
	})

	t.Run("Invalid Format", func(t *testing.T) {
		l := NewICalLexer("Caendar [v1.99]")
		r := NewCalendarReader(l)

		_, err := r.getVersion()

		if err == nil {
			t.Fail()
		}
	})
}

const cal = `Calendar [v2.0]
MondayFirst [1]
DefaultAlarms [0 10 20 30 800]
Appt [
Start [630]
Length [30]
Uid [eskimo_0_f57_d]
Owner [cj]
Contents [complex by months 15th last day
]
Remind [1]
Hilite [always]
Dates [ComplexMonths 1 15 16/11/2016 Backward ByDay
Start 16/11/2016 End
]
]
Note [
Uid [vbox_7f0101_cc2_6]
Owner [callum]
Contents [Are these notes? what is this for?
]
Remind [1]
Hilite [always]
Dates [Single 30/9/2016 End
]
]`

func TestCalendarReader_Read(t *testing.T) {
	l := NewICalLexer(cal)
	r := NewCalendarReader(l)

	cal, err := r.Read()

	if err != nil {
		t.Error(err)
	}

	if cal == nil {
		t.Fail()
	}

	if len(cal.Appointments) != 1 || len(cal.Notes) != 1 || cal.Version != 2.0 {
		t.Fail()
	}

	appt := cal.Appointments[0]

	if appt.Owner != "cj" || appt.RemindStart != 1 {
		t.Fail()
	}

	h, m, s := appt.Start.Clock()
	y, mo, d := appt.Start.Date()

	if y != 2016 || mo != 11 || d != 16 {
		t.Fail()
	}

	if h != 10 || m != 30 || s != 0 {
		t.Fail()
	}

	if cal.Options.MondayFirst != true {
		t.Fail()
	}

	var alarmIndexToExpected = map[int]uint{
		0: 0,
		1: 10,
		2: 20,
		3: 30,
		4: 800,
	}

	for index, expected := range alarmIndexToExpected {
		if cal.Options.DefaultAlarms[index].Time != expected {
			t.Fail()
		}
	}
}
