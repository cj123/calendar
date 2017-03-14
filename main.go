package main

// build files in frontend/ (excluding non-compiled dependencies) into static.go
//go:generate esc -o static.go -ignore=".npm|node_modules" -prefix="frontend" frontend

import (
	"flag"
	"fmt"
	"log"
	"net/http"
	"path/filepath"
	"time"

	"github.com/cj123/calendar/config"
	"github.com/cj123/calendar/handler"
	"github.com/cj123/calendar/model"
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
		log.Fatalf("Could not read config, %s\n", err.Error())
	}

	log.Printf("Read configuration file at %s\n", configLocation)

	db, err := c.OpenDatabaseConnection()

	if err != nil {
		log.Fatalf("Could not connect to database, %s\n", err.Error())
	}

	defer db.Close()

	err = model.Migrate(db)

	if err != nil {
		log.Fatalf("Could not migrate entities: %s\n", err.Error())
	}

	log.Println("Successfully connected to database and ran migrations")

	router := handler.NewHandler(db).Router()

	// create a file server for the static files on the frontend
	var dir http.FileSystem

	if c.Web.StaticFiles == "" {
		// use inbuilt files (esc)
		dir = FS(false)
	} else {
		// dev mode?
		dir = http.Dir(filepath.Join(filepath.Dir(configLocation), c.Web.StaticFiles))
	}


	fs := http.FileServer(dir)

	router.PathPrefix("/").Handler(fs)

	srv := &http.Server{
		Handler:      router,
		Addr:         fmt.Sprintf("%s:%d", c.Web.Address, c.Web.Port),
		WriteTimeout: 15 * time.Second,
		ReadTimeout:  15 * time.Second,
	}

	log.Printf("Starting web server on: %s:%d\n", c.Web.Address, c.Web.Port)
	log.Fatal(srv.ListenAndServe())
}
