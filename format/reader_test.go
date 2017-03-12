package format

import "testing"

func TestIsValidCalendarType(t *testing.T) {
	t.Run("Valid", func(t *testing.T) {
		valid := IsValidCalendarType("ics")

		if !valid {
			t.Fail()
		}

		valid = IsValidCalendarType("ical-tcl")

		if !valid {
			t.Fail()
		}
	})

	t.Run("Invalid", func(t *testing.T) {
		valid := IsValidCalendarType("calendar")

		if valid {
			t.Fail()
		}
	})
}