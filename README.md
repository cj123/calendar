Calendar [![Build Status](https://travis-ci.com/cj123/calendar.svg?token=bhCpeedhGSkmpodsxVUZ&branch=development)](https://travis-ci.com/cj123/calendar)
========

A modern calendar application in the style of [ical-tcl](https://launchpad.net/ical-tcl), written in Go and AngularJS 1.

## Requirements

* Go 1.7+
* NodeJS 4.x and yarnpkg
* A browser :P

## Installation

Frontend:

```
$ nvm use 4
$ npm install -g yarnpkg
$ cd frontend/
$ yarn install
$ node_modules/.bin/gulp build
```

```sh
$ cd cmd/web
$ go build .
$ ./web
```

## Example data files

See `_data/`
