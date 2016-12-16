package main

import (
	"flag"
	"io/ioutil"
	"log"
	"os"

	"github.com/cj123/calendar/config"
	"github.com/cj123/calendar/entity"
	"github.com/cj123/calendar/format"
	"github.com/cj123/calendar/format/ical-tcl"
	"github.com/cj123/calendar/format/ics"
)

var (
	importPath     string
	configLocation string
	calendarType   string
)

func init() {
	flag.StringVar(&configLocation, "c", "./config.yml", "the configuration file location")
	flag.StringVar(&calendarType, "t", "", "calendar type [ics or ical-tcl]")
	flag.StringVar(&importPath, "i", "", "the import path for the calendar")
	flag.Parse()
}

func main() {
	if importPath == "" {
		log.Printf("Import path cannot be empty\n")
		flag.PrintDefaults()
		os.Exit(1)
	} else if calendarType != "ics" && calendarType != "ical-tcl" {
		log.Printf("Select a calendar type, ical-tcl/ics\n")
		flag.PrintDefaults()
		os.Exit(1)
	}

	c, err := config.Parse(configLocation)

	if err != nil {
		log.Printf("Could not read config, %s\n", err.Error())
		os.Exit(1)
	}

	db, err := c.OpenDatabaseConnection()

	if err != nil {
		log.Printf("Could not connect to database, %s\n", err.Error())
		os.Exit(1)
	}

	defer db.Close()

	entity.Migrate(db)

	file, err := ioutil.ReadFile(importPath)

	if err != nil {
		log.Printf("Could not open path: %s\n", importPath)
		os.Exit(1)
	}

	var cal *entity.Calendar
	var reader format.Reader

	if calendarType == "ical-tcl" {
		reader = icaltcl.NewCalendarReader(icaltcl.NewICalLexer(string(file)))
	} else if calendarType == "ics" {
		reader = ics.NewICSReader(string(file))
	}

	cal, err = reader.Read()

	if err != nil {
		log.Printf("Could not read calendar: %s\n", err.Error())
		os.Exit(1)
	}

	err = db.Create(cal).Error

	if err != nil {
		panic(err)
	}
}
