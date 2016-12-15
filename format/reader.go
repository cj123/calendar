package format

import "github.com/cj123/calendar/entity"

type Reader interface {
	Read() (*entity.Calendar, error)
}
