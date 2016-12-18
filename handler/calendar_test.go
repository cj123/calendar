package handler

import (
	"net/http"
	"testing"
)

func TestOptionsHandler(t *testing.T) {
	var out map[string]interface{}

	res, err := makeRequest("GET", "/calendar/options", nil, &out, nil)

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
