package handler

import (
	"bytes"
	"encoding/json"
	"errors"
	"io"
	"net/http"
	"net/http/httptest"
	"os"
	"testing"

	"github.com/cj123/calendar/config"
	"github.com/cj123/calendar/entity"
	"github.com/cj123/calendar/format/ics"
	"github.com/jinzhu/gorm"
)

var (
	server *httptest.Server
	db     *gorm.DB
)

func TestMain(m *testing.M) {
	c := config.ConfigTest()

	// delete old database
	os.Remove(c.Database.Location)

	var err error
	db, err = c.OpenDatabaseConnection()

	if err != nil {
		panic(err)
	}

	defer db.Close()

	entity.Migrate(db)

	// import a calendar
	r := ics.NewICSReader(uniTimetable)

	cal, err := r.Read()

	if err != nil {
		panic(err)
	}

	if err := db.Create(&cal).Error; err != nil {
		panic(err)
	}

	handler := NewHandler(db)

	router := handler.Router()
	server = httptest.NewServer(router)

	os.Exit(m.Run())
}

func makeRequest(method, url string, body interface{}, output interface{}, headers map[string]string) (*http.Response, error) {
	var b io.Reader

	if method != "GET" {
		b = encodeJSON(body)
	} else if method == "GET" && body != nil {
		return nil, errors.New("GET requests cannot have a body")
	}

	request, err := http.NewRequest(method, server.URL+url, b)

	if err != nil {
		return nil, err
	}

	request.Header.Add("Accepts", "application/json")
	request.Header.Add("Content-Type", "application/json")

	for key, val := range headers {
		request.Header.Add(key, val)
	}

	res, err := http.DefaultClient.Do(request)

	if err != nil {
		return nil, err
	}

	defer res.Body.Close()

	if output != nil {
		buf := new(bytes.Buffer)
		buf.ReadFrom(res.Body)

		err = json.Unmarshal(buf.Bytes(), &output)
	}

	return res, err
}

func encodeJSON(u interface{}) io.Reader {
	j, _ := json.Marshal(u)

	return bytes.NewReader(j)
}
