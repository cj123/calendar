package config

import (
	"errors"
	"fmt"
	"io/ioutil"
	"os"

	"github.com/jinzhu/gorm"
	"gopkg.in/yaml.v2"

	_ "github.com/go-sql-driver/mysql"
	_ "github.com/mattn/go-sqlite3"
	"os/user"
	"strings"
)

type Config struct {
	Database  Database `yaml:"database"`
	Web       Web      `yaml:"web"`
	LogOutput string   `yaml:"log_output"`
}

type Database struct {
	Username string `yaml:"username"`
	Password string `yaml:"password"`
	Hostname string `yaml:"hostname"`
	Database string `yaml:"database"`
	Port     int    `yaml:"port"`
	Dialect  string `yaml:"dialect"`
	Location string `yaml:"location"`
}

type Web struct {
	Address     string `yaml:"address"`
	Port        int    `yaml:"port"`
	StaticFiles string `yaml:"static_files"`
}

func Parse(location string) (*Config, error) {
	usr, err := user.Current()

	if err != nil {
		return nil, err
	}

	configFile, err := os.Open(strings.Replace(location, "~", usr.HomeDir, -1))

	if err != nil {
		return nil, err
	}

	bytes, err := ioutil.ReadAll(configFile)

	if err != nil {
		return nil, err
	}

	conf := &Config{}
	err = yaml.Unmarshal(bytes, &conf)

	if err != nil {
		return nil, err
	}

	conf.Database.Location = strings.Replace(conf.Database.Location, "~", usr.HomeDir, -1)

	return conf, err
}

func (c *Config) OpenDatabaseConnection() (*gorm.DB, error) {
	var connection string

	if c.Database.Dialect == "mysql" {
		connection = fmt.Sprintf(
			"%s:%s@tcp(%s:%d)/%s?charset=utf8&parseTime=True",
			c.Database.Username,
			c.Database.Password,
			c.Database.Hostname,
			c.Database.Port,
			c.Database.Database,
		)
	} else if c.Database.Dialect == "sqlite3" && c.Database.Location != "" {
		connection = c.Database.Location
	} else {
		return nil, errors.New("invalid database driver specified: " + c.Database.Dialect)
	}

	return gorm.Open(c.Database.Dialect, connection)
}

// ConfigTest returns the test configuration file
func ConfigTest() *Config {
	return &Config{
		Database: Database{
			Dialect:  "sqlite3",
			Location: "/tmp/calendar_test.db",
		},
		Web: Web{
			Address: "0.0.0.0",
			Port:    8000,
		},
	}
}
