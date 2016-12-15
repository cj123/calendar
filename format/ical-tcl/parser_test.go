package icaltcl

import (
	"fmt"
	"github.com/cj123/calendar/entity"
	"testing"
)

func getKeyword(l Lexer) (string, error) {
	keyword, err := l.GetID()

	if err != nil {
		return "", nil
	}

	l.SkipWhitespace()
	err = l.SkipOpeningDelimiter()

	return keyword, err
}

func doParse(t *testing.T, input string, shouldErr bool) entity.Item {
	l := NewICalLexer(input)
	keyword, err := getKeyword(l)

	if err != nil {
		t.Error(err)
	}

	p := ItemParser{}
	item := entity.Item{}

	err = p.Parse(l, &item, keyword, nil)

	if err != nil && !shouldErr {
		t.Error(err)
	} else if err == nil && shouldErr {
		t.Fail()
	}

	return item
}

func doApptParse(t *testing.T, input string, shouldErr bool) (entity.Appointment, *dateSet) {
	l := NewICalLexer(input)
	keyword, err := getKeyword(l)

	if err != nil {
		t.Error(err)
	}

	set := &dateSet{}
	p := AppointmentParser{&ItemParser{}}
	item := entity.Appointment{}

	err = p.Parse(l, &item, keyword, set)

	if err != nil && !shouldErr {
		t.Error(err)
	} else if err == nil && shouldErr {
		t.Fail()
	}

	return item, set
}

func TestItemParser_Parse(t *testing.T) {
	t.Run("Remind", func(t *testing.T) {
		if doParse(t, "Remind [1]", false).RemindStart != 1 {
			t.Fail()
		}
	})

	t.Run("Remind No Start", func(t *testing.T) {
		doParse(t, "Remind []", true)
	})

	t.Run("Owner", func(t *testing.T) {
		if doParse(t, "Owner [callum]", false).Owner != "callum" {
			t.Fail()
		}
	})

	t.Run("Owner No Owner", func(t *testing.T) {
		doParse(t, "Owner []", true)
	})

	t.Run("UID", func(t *testing.T) {
		i := doParse(t, "Uid [vbox_7f0101_cc2_2]", false)

		if i.UID != "vbox_7f0101_cc2_2" || !i.UIDPersistent {
			t.Fail()
		}
	})

	t.Run("UID No UID", func(t *testing.T) {
		doParse(t, "Uid []", true)
	})

	t.Run("Contents", func(t *testing.T) {
		str := `Are these notes? what is this for?
and here's some text on a newline but with some trailing newlines too

`
		if doParse(t, fmt.Sprintf("Contents [%s]", str), false).Text != str {
			t.Fail()
		}
	})

	t.Run("Contents empty", func(t *testing.T) { // is allowed
		if doParse(t, "Contents []", false).Text != "" {
			t.Fail()
		}
	})

	t.Run("Hilite", func(t *testing.T) {
		if doParse(t, "Hilite [always]", false).Hilite != "always" {
			t.Fail()
		}
	})

	t.Run("Hilite no Hilite", func(t *testing.T) {
		doParse(t, "Hilite []", true)
	})

	t.Run("Todo", func(t *testing.T) {
		if !doParse(t, "Todo []", false).Todo {
			t.Fail()
		}
	})

	t.Run("Done", func(t *testing.T) {
		if !doParse(t, "Done []", false).Done {
			t.Fail()
		}
	})
}

func TestAppointmentParser_Parse(t *testing.T) {
	t.Run("Parse Start", func(t *testing.T) {
		_, set := doApptParse(t, "Start [750]", false)

		if set.StartTime != 750 {
			t.Fail()
		}
	})

	t.Run("Parse Start Allow 0", func(t *testing.T) {
		_, set := doApptParse(t, "Start [0]", false)

		if set.StartTime != 0 {
			t.Fail()
		}
	})

	t.Run("Parse no start time", func(t *testing.T) {
		doApptParse(t, "Start []", true)
	})

	t.Run("Parse Length", func(t *testing.T) {
		_, set := doApptParse(t, "Length [250]", false)

		if set.Length != 250 {
			t.Fail()
		}
	})

	t.Run("Length invalid number", func(t *testing.T) {
		doApptParse(t, "Length [sadasdas]", true)
	})

	t.Run("Timezone", func(t *testing.T) {
		appt, _ := doApptParse(t, "Timezone [America/Argentina/Mendoza]", false)

		if appt.Timezone != "America/Argentina/Mendoza" {
			t.Fail()
		}
	})

	t.Run("Timezone Invalid", func(t *testing.T) {
		doApptParse(t, "Timezone []", true)
	})

	t.Run("Alarms", func(t *testing.T) {
		appt, _ := doApptParse(t, "Alarms [ 1 7 12 18]", false)

		if len(appt.Alarms) < 4 || appt.Alarms[0].Time != 1 || appt.Alarms[2].Time != 12 || appt.Alarms[3].Time != 18 {
			t.Fail()
		}
	})
}
