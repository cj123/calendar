all: .deps test
	$(MAKE) -C frontend all
	go generate ./frontend
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
	go generate ./frontend
	go vet $$(glide novendor)
	go test -cover $$(glide novendor)

cross: all
	xgo --targets=windows/amd64,windows/386,linux/386,linux/amd64,darwin/386,darwin/amd64 .
