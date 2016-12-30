package config

import (
	"gopkg.in/yaml.v2"
	"io/ioutil"
	"os"
	"path"
	"reflect"
	"testing"
)

func TestParse(t *testing.T) {
	wantConfig := ConfigTest()

	b, err := yaml.Marshal(wantConfig)

	if err != nil {
		t.Error(err)
	}

	dir, err := ioutil.TempDir("", "config")

	if err != nil {
		t.Error(err)
	}

	defer os.RemoveAll(dir)

	loc := path.Join(dir, "calendar-config.yml")

	err = ioutil.WriteFile(loc, b, 0644)

	if err != nil {
		t.Fail()
	}

	gotConfig, err := Parse(loc)

	if err != nil {
		t.Error(err)
	}

	if !reflect.DeepEqual(gotConfig, wantConfig) {
		t.Fail()
	}
}

func TestConfig_OpenDatabaseConnection(t *testing.T) {
	config := ConfigTest()

	db, err := config.OpenDatabaseConnection()

	if err != nil || db == nil {
		t.Fail()
	}
}
