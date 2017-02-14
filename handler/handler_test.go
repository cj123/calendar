package handler

import (
	"bytes"
	"encoding/json"
	"errors"
	"io"
	"mime/multipart"
	"net/http"
	"net/http/httptest"
	"os"
	"testing"

	"github.com/cj123/calendar/config"
	"github.com/cj123/calendar/format"
	"github.com/cj123/calendar/model"
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

	model.Migrate(db)
	calendars := map[format.CalendarType]string{
		format.CalendarICalTCL: icalTest,
		format.CalendarICS:     uniTimetable,
	}

	for calType, data := range calendars {
		cal, err := format.ReadCalendar([]byte(data), calType)

		if err != nil {
			panic(err)
		}

		if err := db.Create(&cal).Error; err != nil {
			panic(err)
		}
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

func makeFileUploadRequest(url string, params map[string]string, paramName, fileName string, file []byte) (*http.Response, error) {
	body := new(bytes.Buffer)

	writer := multipart.NewWriter(body)
	part, err := writer.CreateFormFile(paramName, fileName)

	if err != nil {
		return nil, err
	}

	_, err = part.Write(file)

	if err != nil {
		return nil, err
	}

	for key, val := range params {
		err = writer.WriteField(key, val)

		if err != nil {
			return nil, err
		}
	}

	err = writer.Close()

	if err != nil {
		return nil, err
	}

	req, err := http.NewRequest("POST", server.URL+url, body)
	req.Header.Set("Content-Type", writer.FormDataContentType())

	res, err := http.DefaultClient.Do(req)

	if err != nil {
		return nil, err
	}

	defer res.Body.Close()

	return res, err
}

func encodeJSON(u interface{}) io.Reader {
	j, _ := json.Marshal(u)

	return bytes.NewReader(j)
}
