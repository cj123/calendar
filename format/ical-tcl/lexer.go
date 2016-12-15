package icaltcl

import (
	"errors"
	"fmt"
	"strconv"
	"time"
	"unicode"
	"unicode/utf8"
)

const (
	EOF = iota
	ERROR
	VALID

	OPEN_STRING  = '['
	CLOSE_STRING = ']'

	icalDateFormat = "02/01/2006"
)

var escapeChars = map[rune]bool{
	OPEN_STRING:  true,
	CLOSE_STRING: true,
	'\\':         true,
}

type Lexer interface {
	Status() int
	Peek() string
	Next() string
	Advance() string
	Skip(string) error
	SkipOpeningDelimiter() error
	SkipClosingDelimiter() error
	SkipWhitespace()
	GetID() (string, error)
	GetUntil(rune) string
	GetNumber() (float64, error)
	GetDate() (*time.Time, error)
	GetString() string
	Index() int
	Reset(int)
	Len() int
}

type ICalLexer struct {
	buf   []rune
	len   int
	index int
}

func NewICalLexer(str string) *ICalLexer {
	chars := []rune(str)
	return &ICalLexer{
		buf:   chars,
		len:   len(chars),
		index: 0,
	}
}

func (l *ICalLexer) Status() int {
	if l.index == l.len {
		return EOF
	} else if l.index > l.len {
		return ERROR
	} else {
		return VALID
	}
}

func (l *ICalLexer) Peek() string {
	if l.index < l.len {
		return string(l.buf[l.index])
	}

	return ""
}

func (l *ICalLexer) Next() string {
	if l.index < l.len {
		c := l.buf[l.index]
		l.index++
		return string(c)
	}

	return ""
}

func (l *ICalLexer) Advance() string {
	l.index++

	if l.index < l.len {
		return string(l.buf[l.index])
	}

	l.index--

	return ""
}

func (l *ICalLexer) Skip(s string) error {
	skipLen := utf8.RuneCountInString(s)

	str := string(l.buf[l.index : l.index+skipLen])

	if l.index < l.len && str == s {
		l.index += skipLen
		return nil
	}

	return errors.New("unexpected string: " + s + ", found " + str + " at index " + string(l.index))
}

func (l *ICalLexer) SkipWhitespace() {
	for l.index < l.len {
		c := l.buf[l.index]

		if !unicode.IsSpace(c) {
			return
		}

		l.index++
	}
}

func (l *ICalLexer) GetID() (string, error) {
	if l.index >= l.len {
		return "", nil
	}

	l.SkipWhitespace()

	if !unicode.IsLetter(l.buf[l.index]) {
		return "", errors.New("illegal character, was expecting ID, got: " + string(l.buf[l.index]))
	}

	begin := l.index

	for l.index < l.len && (unicode.IsNumber(l.buf[l.index]) || unicode.IsLetter(l.buf[l.index])) {
		l.index++
	}

	return string(l.buf[begin:l.index]), nil
}

func (l *ICalLexer) GetUntil(ch rune) string {
	if l.index >= l.len {
		return ""
	}

	begin := l.index

	for l.index < l.len {
		if l.buf[l.index] == ch {
			break
		}

		l.index++
	}

	return string(l.buf[begin : begin+l.index-begin])
}

func (l *ICalLexer) GetNumber() (float64, error) {
	if l.index >= l.len {
		return -1, errors.New("invalid index")
	}

	out := ""

	for l.index < l.len && unicode.IsNumber(l.buf[l.index]) {
		out = out + string(l.buf[l.index])
		l.index++
	}

	if out == "" {
		return -1, errors.New("no number found")
	}

	return strconv.ParseFloat(out, 10)
}

func (l *ICalLexer) GetString() string {
	if l.index >= l.len {
		return ""
	}

	out := ""

	for l.index < l.len && l.buf[l.index] != CLOSE_STRING {
		if l.buf[l.index] == '\\' {
			l.index++

			if l.index >= l.len {
				return ""
			}
		}

		out = out + string(l.buf[l.index])
		l.index++
	}

	return out
}

func PutString(append string) string {
	runes := []rune(append)
	str := ""

	for i := 0; i < len(runes); i++ {
		char := runes[i]

		if _, ok := escapeChars[char]; ok {
			str += "\\"
		}

		str += string(char)
	}

	return str
}

func (l *ICalLexer) SkipOpeningDelimiter() error {
	return l.Skip(string(OPEN_STRING))
}

func (l *ICalLexer) SkipClosingDelimiter() error {
	return l.Skip(string(CLOSE_STRING))
}

func (l *ICalLexer) GetDate() (*time.Time, error) {
	l.SkipWhitespace()

	day, err := l.GetNumber()

	if err != nil {
		return nil, err
	}

	err = l.Skip("/")

	if err != nil {
		return nil, err
	}

	month, err := l.GetNumber()

	if err != nil {
		return nil, err
	}

	err = l.Skip("/")

	if err != nil {
		return nil, err
	}

	year, err := l.GetNumber()

	if err != nil {
		return nil, err
	}

	if month > 12 || month < 1 || day < 1 || day > 31 {
		return nil, errors.New("invalid date")
	}

	t, err := time.Parse(icalDateFormat, fmt.Sprintf("%02.0f/%02.0f/%04.0f", day, month, year))

	return &t, err
}

func (l *ICalLexer) Index() int {
	return l.index
}

func (l *ICalLexer) Reset(pos int) {
	l.index = pos
}

func (l *ICalLexer) Len() int {
	return l.len
}
