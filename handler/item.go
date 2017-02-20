package handler

import (
	"encoding/json"
	"log"
	"net/http"
	"strconv"
	"time"

	"github.com/cj123/calendar/model/repository"

	"github.com/gorilla/mux"
	"github.com/jinzhu/gorm"
	"gopkg.in/bluesuncorp/validator.v9"
)

// itemCreateHandler returns a http HandlerFunc which can be used to create
// a given item, e.g. Appointment or Note.
func itemCreateHandler(repo repository.ItemRepository) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		item := repo.Model()

		err := unmarshalRequest(r, &item)

		if err != nil {
			http.Error(w, "Unable to unmarshal request", http.StatusInternalServerError)
			return
		}

		validate := validator.New()

		if err := validate.Struct(item); err != nil {
			http.Error(w, err.Error(), http.StatusBadRequest)
			return
		}

		err = repo.Create(item)

		if err != nil {
			http.Error(w, "Unable to create "+item.Name(), http.StatusInternalServerError)
			return
		}

		log.Printf("Created %s with ID: %d\n", item.Name(), item.GetID())

		w.WriteHeader(http.StatusCreated)
	}
}

func parseQueryDate(d string) (time.Time, error) {
	return time.Parse(requestDateFormat, d)
}

func itemGetHandler(repo repository.ItemRepository) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		startDate, err := parseQueryDate(r.URL.Query().Get("start"))

		if err != nil {
			http.Error(w, "bad start date", http.StatusBadRequest)
			return
		}

		finishDate, err := parseQueryDate(r.URL.Query().Get("finish"))

		if err != nil {
			http.Error(w, "bad finish date", http.StatusBadRequest)
			return
		}

		items, err := repo.FindBetweenDates(startDate, finishDate)

		if err != nil {
			http.Error(w, "Internal server error", http.StatusInternalServerError)
			return
		}

		b, err := json.Marshal(&items)

		if err != nil {
			http.Error(w, "Internal server error", http.StatusInternalServerError)
			return
		}

		w.Write(b)
	}
}

type deleteItemRequest struct {
	Date      time.Time `json:"date"`
	DeleteAll bool      `json:"delete_all"`
}

func itemDeleteHandler(repo repository.ItemRepository) http.HandlerFunc {
	modelName := repo.Model().Name()

	return func(w http.ResponseWriter, r *http.Request) {
		id := mux.Vars(r)["id"]

		var (
			request deleteItemRequest
			err     error
			uid     uint64
		)

		uid, err = strconv.ParseUint(id, 0, 10)

		if err == nil {
			err = unmarshalRequest(r, &request)
		}

		if err != nil {
			log.Print(err)
			http.Error(w, "Unable to unmarshal request", http.StatusBadRequest)
			return
		}

		if request.DeleteAll {
			log.Printf("Deleting all %ss with id: %s\n", modelName, id)

			// no specific date, delete all occurrences
			err = repo.DeleteItem(uint(uid))
		} else {
			log.Printf("Deleting %s recurrence at %s with id: %s\n", modelName, request.Date, id)

			err = repo.DeleteRecurrence(uint(uid), request.Date)
		}

		if err != nil {
			log.Print(err)
			http.Error(w, "Unable to delete "+modelName+": "+id, http.StatusInternalServerError)
			return
		}

		w.WriteHeader(http.StatusOK)
	}
}

func itemUpdateHandler(repo repository.ItemRepository) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		id := mux.Vars(r)["id"]

		updateRequest := repo.Model()

		err := unmarshalRequest(r, &updateRequest)

		if err != nil {
			log.Println(err)
			http.Error(w, "Unable to unmarshal request", http.StatusInternalServerError)
			return
		}

		validate := validator.New()

		if err := validate.Struct(updateRequest); err != nil {
			log.Println(err)
			http.Error(w, err.Error(), http.StatusBadRequest)
			return
		}

		uid, err := strconv.ParseUint(id, 0, 10)

		err = repo.Update(uint(uid), updateRequest)

		if err == gorm.ErrRecordNotFound {
			http.Error(w, "not found", http.StatusNotFound)
			return
		} else if err != nil {
			http.Error(w, "internal server error", http.StatusInternalServerError)
			return
		}

		log.Printf("Updated %s with ID: %d", updateRequest.Name(), uid)

		w.WriteHeader(http.StatusOK)
	}
}
