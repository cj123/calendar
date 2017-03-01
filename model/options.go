package model

import (
	"log"
	"strconv"
	"strings"
	"time"
)

type CalendarOptions struct {
	Model
	CalendarID uint

	DefaultEarlyWarning uint    // how many days in advance should an appointment be alerted by?
	DefaultAlarms       []Alarm // what alarms are defaults?
	DayviewTimeStart    uint    // when should the calendar view start from by default?
	DayviewTimeFinish   uint    // when should the calendar view finish by default?
	ItemWidth           uint    // how wide is an item (deprecated)
	NoticeHeight        uint    // how tall is a notice (deprecated)
	AmPm                bool    // display times in AM/PM?
	MondayFirst         bool    // show monday first?
	AllowOverflow       bool    // allow overflow? not sure. deprecated
	Visible             bool    // is the calendar visible
	IgnoreAlarms        bool    // ignore alarm notifications
	Color               string  // default color
	Timezone            string  // timezone of calendar (by default)
}

var defaultCalendarOptions = CalendarOptions{
	DefaultEarlyWarning: 1,
	DefaultAlarms:       []Alarm{{Time: 0}, {Time: 5}, {Time: 10}, {Time: 15}},
	DayviewTimeStart:    8,
	DayviewTimeFinish:   18,
	ItemWidth:           9,
	NoticeHeight:        6,
	AmPm:                false,
	MondayFirst:         true,
	AllowOverflow:       true,
	Visible:             true,
	IgnoreAlarms:        false,
	Color:               "<Default> <Default>",
	Timezone:            "<Local>",
}

// DefaultCalendarOptions returns the default options with the current timezone (system time) patched in
// This is naive if this were to become a website but for now this is fine.
func DefaultCalendarOptions() CalendarOptions {
	opts := defaultCalendarOptions

	opts.Timezone = time.Local.String()

	return opts
}

func (c *CalendarOptions) Set(name, val string) error {
	var err error

	switch name {
	// uints
	case "DefaultEarlyWarning":
		err = setUint(&c.DefaultEarlyWarning, val)
		break
	case "DayviewTimeStart":
		err = setUint(&c.DayviewTimeStart, val)
		break
	case "DayviewTimeFinish":
		err = setUint(&c.DayviewTimeFinish, val)
		break
	case "ItemWidth":
		err = setUint(&c.ItemWidth, val)
		break
	case "NoticeHeight":
		err = setUint(&c.NoticeHeight, val)
		break

	// bools
	case "AmPm":
		setBool(&c.AmPm, val)
		break
	case "MondayFirst":
		setBool(&c.MondayFirst, val)
		break
	case "AllowOverflow":
		setBool(&c.AllowOverflow, val)
		break
	case "Visible":
		setBool(&c.Visible, val)
		break
	case "IgnoreAlarms":
		setBool(&c.IgnoreAlarms, val)
		break

	// strings
	case "Color":
		c.Color = val
		break
	case "Timezone":
		c.Timezone = val
		break

	default:
		log.Printf("Unsupported option key: %s, val: %s", name, val)
		break
	}

	return err
}

func setUint(field *uint, val string) error {
	v, err := strconv.ParseUint(val, 10, 0)

	if err != nil {
		return nil
	}

	*field = uint(v)

	return nil
}

func setBool(field *bool, val string) {
	*field = val == "1" || strings.ToLower(val) == "true" || strings.ToLower(val) == "yes"
}
