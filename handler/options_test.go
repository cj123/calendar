package handler

import (
	"github.com/cj123/calendar/model/repository"
	"net/http"
	"testing"
	//"github.com/cj123/calendar/model"
	"reflect"

	"time"
)

func TestHandler_GetOptionsHandler(t *testing.T) {
	var out map[string]interface{}

	res, err := makeRequest("GET", "/calendar/1/options", nil, &out, nil)

	if err != nil {
		t.Error(err)
	}

	if res.StatusCode != http.StatusOK {
		t.Fail()
	}

	if !out["MondayFirst"].(bool) {
		t.Fail()
	}
}

func TestHandler_UpdateOptionsHandler(t *testing.T) {
	repo := repository.NewOptionsRepository(db)

	opts, err := repo.FindByCalendarID(1, false)

	if err != nil {
		t.Error(err)
	}

	t.Run("Valid update", func(t *testing.T) {
		opts.MondayFirst = false
		opts.SoftDelete = true
		opts.AmPm = true
		//opts.DefaultAlarms = append(opts.DefaultAlarms, model.DefaultAlarm{Alarm: model.Alarm{Time: 30}})

		res, err := makeRequest("PUT", "/calendar/1/options", &opts, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusOK {
			t.Fail()
		}

		updatedOpts, err := repo.FindByCalendarID(1, false)

		if err != nil {
			t.Error(err)
		}

		// clear out settings so we can compare
		tm := time.Now()

		opts.UpdatedAt = tm
		opts.CreatedAt = tm
		updatedOpts.CreatedAt = tm
		updatedOpts.UpdatedAt = tm

		if !reflect.DeepEqual(opts, updatedOpts) {
			t.Fail()
		}
	})

	t.Run("not found", func(t *testing.T) {
		opts.MondayFirst = false
		opts.SoftDelete = true
		opts.AmPm = true
		//opts.DefaultAlarms = append(opts.DefaultAlarms, model.DefaultAlarm{Alarm: model.Alarm{Time: 30}})

		res, err := makeRequest("PUT", "/calendar/10/options", &opts, nil, nil)

		if err != nil {
			t.Error(err)
		}

		if res.StatusCode != http.StatusNotFound {
			t.Fail()
		}
	})
}
