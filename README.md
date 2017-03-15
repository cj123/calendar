Calendar [![Build Status](https://travis-ci.com/cj123/calendar.svg?token=bhCpeedhGSkmpodsxVUZ&branch=development)](https://travis-ci.com/cj123/calendar)
========

A modern calendar application in the style of [ical-tcl](https://launchpad.net/ical-tcl), written in Go and AngularJS 1.

## Requirements

* Go 1.7+
* NodeJS 4.x (stable) (recommended install via [nvm (node version manager)](https://github.com/creationix/nvm))
* Make
* A browser :P

## Building (Development)

```sh
# install yarn for package management
$ npm install -g yarnpkg
$ cd frontend/

# install node dependencies
$ yarn install

# run the gulp build task. "build" can be replaced with "watch" to watch & recompile files as they change
$ node_modules/.bin/gulp build

$ go generate
$ go build .
$ ./calendar
```

Also, see `config.yml` for configuration

## Building (Deployment)

```sh
$ make clean
$ make
```

This will compile a single binary output at `./calendar` which includes all frontend assets in `frontend/` (with some sensible exclusions)

## Testing
```sh
$ make test
```

## Example data files

See `_data/`
