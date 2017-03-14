all: .deps test
	$(MAKE) -C frontend all
	go generate
	go build -o calendar
	$(MAKE) -C cmd/import all

.deps:
	go get -u github.com/Masterminds/glide
	go get -u github.com/mjibson/esc
	glide install

clean:
	$(MAKE) -C frontend clean
	$(MAKE) -C cmd/import clean
	rm -rf calendar vendor/ static.go

test: .deps
	go generate
	go vet $$(glide novendor)
	go test -cover $$(glide novendor)
