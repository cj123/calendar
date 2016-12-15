package main

import (
	"flag"
	"fmt"
	"log"
	"net/http"
	"os"
	"time"

	"github.com/cj123/calendar/config"
	"github.com/cj123/calendar/entity"
	"github.com/cj123/calendar/handler"
)

var (
	configLocation string
)

func init() {
	flag.StringVar(&configLocation, "c", "./config.yml", "the configuration file location")
	flag.Parse()
}

func main() {
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

	h := handler.NewHandler(db)

	srv := &http.Server{
		Handler:      h.Router(),
		Addr:         fmt.Sprintf("%s:%d", c.Web.Address, c.Web.Port),
		WriteTimeout: 15 * time.Second,
		ReadTimeout:  15 * time.Second,
	}

	log.Printf("Starting web server on: %s:%d\n", c.Web.Address, c.Web.Port)
	log.Fatal(srv.ListenAndServe())
}
