package icaltcl

import (
	"testing"
	"time"
)

func TestICalLexer_Index(t *testing.T) {
	t.Run("On Construct", func(t *testing.T) {
		l := NewICalLexer("some data")

		if l.Index() != 0 {
			t.Fail()
		}
	})
}

func TestICalLexer_Len(t *testing.T) {
	t.Run("On Construct", func(t *testing.T) {
		l := NewICalLexer("this is some data 9321")

		if l.Len() != 22 {
			t.Fail()
		}
	})
}

func TestICalLexer_Peek(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		l := NewICalLexer("Hello World")

		if l.Peek() != "H" {
			t.Fail()
		}

		if l.Peek() != "H" {
			t.Fail()
		}
	})

	t.Run("Empty string", func(t *testing.T) {
		l := NewICalLexer("")

		if l.Peek() != "" {
			t.Fail()
		}
	})
}

func TestICalLexer_Next(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		str := "Hello World"
		l := NewICalLexer(str)
		r := []rune(str)

		for i := 0; i < len(r); i++ {
			if l.Next() != string(r[i]) {
				t.Fail()
			}
		}
	})

	t.Run("Limit Exceeded", func(t *testing.T) {
		str := "Hello World"
		l := NewICalLexer(str)
		r := []rune(str)

		for i := 0; i < len(r); i++ {
			if l.Next() != string(r[i]) {
				t.Fail()
			}
		}

		if l.Next() != "" {
			t.Fail()
		}

		if l.Index() < l.Len() || l.Status() != EOF {
			t.Fail()
		}
	})
}

func TestICalLexer_Status(t *testing.T) {
	data := "Hello World"
	r := []rune(data)

	l := NewICalLexer(data)

	if l.Status() != VALID {
		t.Fail()
	}

	for i := 0; i < len(r); i++ {
		l.Next()
	}

	if l.Status() != EOF {
		t.Fail()
	}

	l.Next()
	l.Next()

	if l.Status() != EOF {
		t.Fail()
	}

	l.Reset(len(r) + 1)

	if l.Status() != ERROR {
		t.Fail()
	}
}

func TestICalLexer_Advance(t *testing.T) {
	data := "Hello World"
	r := []rune(data)

	t.Run("Default", func(t *testing.T) {
		l := NewICalLexer(data)

		if l.Advance() != string(r[1]) || l.Index() != 1 {
			t.Fail()
		}
	})

	t.Run("Cannot advance", func(t *testing.T) {
		l := NewICalLexer(data)

		for i := 0; i < len(r); i++ {
			l.Next()
		}

		if l.Advance() != "" {
			t.Fail()
		}

		if l.Len() != l.Index() {
			t.Fail()
		}
	})
}

func TestICalLexer_Skip(t *testing.T) {
	data := "this is a test of skipping"
	r := []rune(data)

	t.Run("Default", func(t *testing.T) {
		l := NewICalLexer(data)

		l.Skip("t")

		if string(r[1]) != l.Next() {
			t.Fail()
		}
	})

	t.Run("Letter Not Found", func(t *testing.T) {
		l := NewICalLexer(data)

		err := l.Skip("h") // should fail

		if err == nil {
			t.Fail()
		}
	})

	t.Run("Skip Word", func(t *testing.T) {
		l := NewICalLexer(data)

		err := l.Skip("this")

		if err != nil {
			t.Fail()
		}
	})

	t.Run("Skip Word Wrong Index", func(t *testing.T) {
		l := NewICalLexer(data)

		err := l.Skip("of")

		if err == nil {
			t.Fail()
		}
	})

	t.Run("Skip Word Not Found", func(t *testing.T) {
		l := NewICalLexer(data)

		err := l.Skip("word")

		if err == nil {
			t.Fail()
		}
	})
}

func TestICalLexer_SkipWhitespace(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		data := "                  "

		l := NewICalLexer(data)

		l.SkipWhitespace()

		if l.Len() != l.Index() {
			t.Fail()
		}
	})

	t.Run("Characters", func(t *testing.T) {
		data := "dd                 34234324"

		l := NewICalLexer(data)

		// skip 'dd'
		l.Next()
		l.Next()

		l.SkipWhitespace()

		if l.Peek() != "3" {
			t.Fail()
		}
	})
}

func lexerToEnd(l Lexer) {
	for l.Index() < l.Len() {
		l.Next()
	}
}

func TestICalLexer_GetUntil(t *testing.T) {
	data := "This is a test sentence. 0000000. 1234. 123333999."

	t.Run("Default", func(t *testing.T) {
		l := NewICalLexer(data)

		if l.GetUntil('.') != "This is a test sentence" {
			t.Fail()
		}
	})

	t.Run("No Occurrence", func(t *testing.T) {
		l := NewICalLexer(data)

		if l.GetUntil(':') != data {
			t.Fail()
		}
	})

	t.Run("End of string", func(t *testing.T) {
		l := NewICalLexer(data)
		lexerToEnd(l)

		if l.GetUntil('.') != "" {
			t.Fail()
		}
	})
}

func TestICalLexer_GetID(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		data := "Owner [callum]"

		l := NewICalLexer(data)

		id, err := l.GetID()

		if err != nil || id != "Owner" {
			t.Fail()
		}
	})

	t.Run("First char not letter", func(t *testing.T) {
		data := "1Owner [callum]"

		l := NewICalLexer(data)

		_, err := l.GetID()

		if err == nil {
			t.Fail()
		}
	})

	t.Run("Lexer at end", func(t *testing.T) {
		data := "1Owner [callum]"

		l := NewICalLexer(data)

		lexerToEnd(l)

		if id, err := l.GetID(); id != "" || err != nil {
			t.Fail()
		}
	})
}

func TestICalLexer_Reset(t *testing.T) {
	data := "1Owner [callum]"

	l := NewICalLexer(data)

	for i := 0; i < 10; i++ {
		l.Next()
	}

	l.Reset(2)

	if l.Index() != 2 {
		t.Fail()
	}

	l.Reset(0)

	if l.Index() != 0 {
		t.Fail()
	}
}

func TestPutString(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		add := "This is my string adsd           2222222&&&&@^^^@^!"

		if add != PutString(add) {
			t.Fail()
		}
	})

	t.Run("With Escape", func(t *testing.T) {
		add := "This is my string adsd           222222[2&&&&@^^^@^!\\"
		expected := "This is my string adsd           222222\\[2&&&&@^^^@^!\\\\"

		if expected != PutString(add) {
			t.Fail()
		}
	})
}

func TestICalLexer_GetString(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		data := "Owner [callum]"

		l := NewICalLexer(data)
		_, err := l.GetID() // skip ID

		if err != nil {
			t.Fail()
		}

		l.SkipWhitespace()
		err = l.SkipOpeningDelimiter()

		if err != nil {
			t.Fail()
		}

		if l.GetString() != "callum" {
			t.Fail()
		}
	})

	t.Run("Escaped", func(t *testing.T) {
		data := "Owner [\\callum]"

		l := NewICalLexer(data)

		l.GetID()
		l.SkipWhitespace()
		l.SkipOpeningDelimiter()

		if l.GetString() != "callum" {
			t.Fail()
		}
	})

	t.Run("Escaped at end", func(t *testing.T) {
		data := "Owner [callum\\"

		l := NewICalLexer(data)
		l.GetID()
		l.SkipWhitespace()
		l.SkipOpeningDelimiter()

		if l.GetString() != "" {
			t.Fail()
		}
	})

	t.Run("End of buffer", func(t *testing.T) {
		data := "Owner [callum]"

		l := NewICalLexer(data)

		lexerToEnd(l)

		if l.GetString() != "" {
			t.Fail()
		}
	})
}

func TestICalLexer_GetNumber(t *testing.T) {
	data := `Start
	[510]`

	t.Run("Default", func(t *testing.T) {
		l := NewICalLexer(data)

		l.GetID()
		l.SkipWhitespace()
		l.SkipOpeningDelimiter()

		num, err := l.GetNumber()

		if err != nil || num != 510 {
			t.Fail()
		}
	})

	t.Run("Index past length", func(t *testing.T) {
		l := NewICalLexer(data)
		l.GetID()
		l.SkipWhitespace()
		l.SkipOpeningDelimiter()

		l.Reset(200)

		_, err := l.GetNumber()

		if err == nil {
			t.Fail()
		}
	})

	t.Run("No number", func(t *testing.T) {
		d := `Start

		[Tomorrow]`

		l := NewICalLexer(d)
		l.GetID()
		l.SkipWhitespace()
		l.SkipOpeningDelimiter()

		_, err := l.GetNumber()

		if err == nil {
			t.Fail()
		}
	})
}

func TestICalLexer_GetDate(t *testing.T) {
	t.Run("Default", func(t *testing.T) {
		data := "17/01/1995"

		l := NewICalLexer(data)

		te, err := l.GetDate()

		if err != nil {
			t.Fail()
		}

		if te.Month() != time.January || te.Year() != 1995 || te.Day() != 17 {
			t.Fail()
		}
	})

	t.Run("Invalid date", func(t *testing.T) {
		data := "42/10/1995"

		l := NewICalLexer(data)

		_, err := l.GetDate()

		if err == nil {
			t.Fail()
		}
	})
}
