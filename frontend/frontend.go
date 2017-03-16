package frontend

// Pack frontend compiled assets into this package
//go:generate esc -o static.go -ignore=".npm|node_modules|src" -pkg=frontend .
