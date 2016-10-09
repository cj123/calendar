<?php

namespace CalendarBundle\Tests\Formatting\ICal\Lexer;

use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;

/**
 * Class ICalLexerTest
 * @package CalendarBundle\Tests\Formatting\ICal\Lexer
 * @author Callum Jones <cj@icj.me>
 */
class ICalLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexOnConstruct()
    {
        $lexer = new ICalLexer("some data");

        $this->assertEquals(0, $lexer->index());
    }

    public function testLengthOnConstruct()
    {
        $data = "this is some data 9321";

        $lexer = new ICalLexer($data);

        $this->assertEquals(strlen($data), $lexer->length());
    }

    public function testPeek()
    {
        $data = "Hello World";

        $lexer = new ICalLexer($data);
        $char = $lexer->peek();

        $this->assertEquals($data[0], $char);

        // test it doesn't increase index
        $char = $lexer->peek();
        $this->assertEquals($data[0], $char);
    }

    public function testPeekEmptyString()
    {
        $data = "";

        $lexer = new ICalLexer($data);
        $char = $lexer->peek();

        $this->assertEquals("", $char);
    }

    public function testNext()
    {
        $data = "Hello World";

        $lexer = new ICalLexer($data);

        for ($i = 0; $i < strlen($data); $i++) {
            $char = $lexer->next();
            $this->assertEquals($data[$i], $char);
        }
    }

    public function testNextLimitExceeded()
    {
        $data = "Hello World";

        $lexer = new ICalLexer($data);

        for ($i = 0; $i < strlen($data); $i++) {
            $char = $lexer->next();
            $this->assertEquals($data[$i], $char);
        }

        $this->assertEquals("", $lexer->next());
        $this->assertGreaterThanOrEqual($lexer->index(), $lexer->length());
    }

    public function testStatus()
    {
        $data = "Hello World";

        $lexer = new ICalLexer($data);
        $this->assertEquals(ICalLexer::VALID, $lexer->status());

        for ($i = 0; $i < strlen($data); $i++) {
            $lexer->next();
        }

        $this->assertEquals(ICalLexer::EOF, $lexer->status());

        $lexer->next();
        $lexer->next();

        $this->assertEquals(ICalLexer::EOF, $lexer->status());

        $lexer->reset(strlen($data) + 1);

        $this->assertEquals(ICalLexer::ERROR, $lexer->status());
    }

    public function testAdvance()
    {
        $data = "Hello World";

        $lexer = new ICalLexer($data);

        $this->assertEquals($data[1], $lexer->advance());
        $this->assertEquals(1, $lexer->index());
    }

    public function testAdvanceCannotAdvance()
    {
        $data = "Hello World";

        $lexer = new ICalLexer($data);

        for ($i = 0; $i < strlen($data); $i++) {
            $lexer->next();
        }

        $this->assertEquals("", $lexer->advance());
        $this->assertEquals($lexer->length(), $lexer->index());
    }

    public function testSkip()
    {
        $data = "this is a test of skipping";

        $lexer = new ICalLexer($data);

        $lexer->skip("t");
        $this->assertEquals($data[1], $lexer->next());
    }

    public function testSkipLetterNotFound()
    {
        $data = "this is a test of skipping";

        $lexer = new ICalLexer($data);

        try {
            $lexer->skip("h"); // should fail
        } catch (LexerException $e) {
            return;
        }

        $this->fail("exception not caught");
    }

    public function testSkipWord()
    {
        $data = "this is a test of skipping";

        $lexer = new ICalLexer($data);

        try {
            $lexer->skip("this");
        } catch (LexerException $e) {
            $this->fail("exception caught");
        }
    }

    public function testSkipWordNotAtIndex()
    {
        $data = "-this is a test of skipping";

        $lexer = new ICalLexer($data);

        try {
            $lexer->skip("this");
        } catch (LexerException $e) {
            return;
        }

        $this->fail("exception not caught");
    }


    public function testSkipWordNotFound()
    {
        $data = "this is a test of skipping";

        $lexer = new ICalLexer($data);

        try {
            $lexer->skip("word"); // should fail
        } catch (LexerException $e) {
            return;
        }

        $this->fail("exception not caught");
    }

    public function testSkipWhitespace()
    {
        $data = "                      ";

        $lexer = new ICalLexer($data);

        $lexer->skipWhitespace();

        $this->assertEquals($lexer->length(), $lexer->index());
    }

    public function testSkipWhitespaceCharacters()
    {
        $data = "dd              5asdasd";

        $lexer = new ICalLexer($data);

        // skip over "dd"
        $lexer->next();
        $lexer->next();

        // now skip whitespace
        $lexer->skipWhitespace();

        $this->assertEquals("5", $lexer->peek());
    }

    public function testGetUntil()
    {
        $data = "This is a test sentence. 0000000. 1234. 123333999.";

        $lexer = new ICalLexer($data);

        $this->assertEquals("This is a test sentence", $lexer->getUntil("."));
        $lexer->next(); // pop over the "."

        $this->assertEquals(" 0000000", $lexer->getUntil("."));
    }

    public function testGetUntilNoOccurrence()
    {
        $data = "This is a test sentence. 0000000. 1234. 123333999.";
        $lexer = new ICalLexer($data);

        $this->assertEquals($data, $lexer->getUntil(":"));
    }

    public function testGetUntilAtEndOfString()
    {
        $data = "This is a test sentence. 0000000. 1234. 123333999.";
        $lexer = new ICalLexer($data);

        $this->lexerToEnd($lexer);

        $this->assertEquals("", $lexer->getUntil("."));
    }

    /**
     * move a lexer to the end.
     *
     * @param LexerInterface $lexer
     */
    private function lexerToEnd(LexerInterface $lexer) {
        // move to end of string
        while ($lexer->index() < $lexer->length()) {
            $lexer->next();
        }
    }

    public function testGetId()
    {
        $data = "Owner [callum]";

        $lexer = new ICalLexer($data);
        $this->assertEquals("Owner", $lexer->getId());
    }

    public function testGetIdFirstCharNotLetter()
    {
        $data = "1Owner [callum]";

        try {
            $lexer = new ICalLexer($data);
            $lexer->getId();
        } catch (LexerException $e) {
            return;
        }

        $this->fail("exception not caught for invalid char");
    }

    public function testGetIdLexerAtEnd()
    {
        $data = "1Owner [callum]";
        $lexer = new ICalLexer($data);

        $this->lexerToEnd($lexer);

        $this->assertEquals("", $lexer->getId());
    }

    public function testReset()
    {
        $data = "1Owner [callum]";
        $lexer = new ICalLexer($data);

        $rand = random_int(1, strlen($data));

        for ($i = 0; $i < $rand; $i++) {
            $lexer->next();
        }

        $lexer->reset(2);
        $this->assertEquals(2, $lexer->index());

        $lexer->reset(0);
        $this->assertEquals(0, $lexer->index());
    }

    public function testPutString()
    {
        $str = "";
        $add = "This is my string adsd           2222222&&&&@^^^@^!";

        $this->assertEquals($str . $add, ICalLexer::putString($str, $add));
    }

    public function testPutStringWithEscape()
    {
        $str = "test: ";
        $add = "This is my string adsd           222222[2&&&&@^^^@^!\\";
        $expected = "test: This is my string adsd           222222\\[2&&&&@^^^@^!\\\\";

        $this->assertEquals($expected, ICalLexer::putString($str, $add));
    }

    public function testGetString()
    {
        $data = "Owner [callum]";

        $lexer = new ICalLexer($data);
        $lexer->getId(); // skip identifier
        $lexer->skipWhitespace();
        $lexer->skip("[");

        $this->assertEquals("callum", $lexer->getString());
    }

    public function testGetStringEscaped()
    {
        $data = "Owner [\\callum]";

        $lexer = new ICalLexer($data);
        $lexer->getId(); // skip identifier
        $lexer->skipWhitespace();
        $lexer->skip("[");

        $this->assertEquals("callum", $lexer->getString());
    }

    public function testGetStringEscapedAtEnd()
    {
        $data = "Owner [callum\\";

        $lexer = new ICalLexer($data);
        $lexer->getId(); // skip identifier
        $lexer->skipWhitespace();
        $lexer->skip("[");

        $this->assertEquals("", $lexer->getString());
    }

    public function testGetStringAtEndOfBuffer()
    {
        $data = "Owner [callum]";

        $lexer = new ICalLexer($data);
        $this->lexerToEnd($lexer);

        $this->assertEquals("", $lexer->getString());
    }

    public function testGetNumber()
    {
        $data = "Start
        [510]";

        $lexer = new ICalLexer($data);
        $lexer->getId(); // skip identifier
        $lexer->skipWhitespace();
        $lexer->skip("[");

        $this->assertEquals(510, $lexer->getNumber());
    }

    public function testGetNumberNoNumber()
    {
        $data = "Start
        [tomorrow]";

        $lexer = new ICalLexer($data);
        $lexer->getId(); // skip identifier
        $lexer->skipWhitespace();
        $lexer->skip("[");

        $this->assertEquals(0, $lexer->getNumber());
    }
}
