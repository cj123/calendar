<?php

namespace CalendarBundle\Tests\Formatting\ICal\Parser;

use CalendarBundle\Entity\DateSet;
use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Parser\ItemParser;
use CalendarBundle\Formatting\ICal\Parser\ParserException;

/**
 * Class ItemParserTest
 * @package CalendarBundle\Tests\Formatting\ICal\Parser
 * @author Callum Jones <cj@icj.me>
 */
class ItemParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * get a keyword from the lexer.
     *
     * @param LexerInterface $lexer
     * @return string
     */
    private function getKeyword(LexerInterface $lexer): string
    {
        $keyword = $lexer->getId();
        $lexer->skipWhitespace();
        $lexer->skipOpeningDelimiter();

        return $keyword;
    }

    public function testParseRemind()
    {
        $lexer = new ICalLexer("Remind [1]");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals(1, $parser->getItem()->getRemindStart());
    }

    public function testParseRemindNoRemindStart()
    {
        $lexer = new ICalLexer("Remind []");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        try {
            $parser->parse($lexer, $keyword);
        } catch (\Exception $e) {
            $this->assertInstanceOf(LexerException::class, $e);
            return;
        }

        $this->fail("no exception caught");
    }

    public function testParseOwner()
    {
        $lexer = new ICalLexer("Owner [callum]");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals("callum", $parser->getItem()->getOwner());
    }

    public function testParseOwnerNoOwner()
    {
        $lexer = new ICalLexer("Owner []");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        try {
            $parser->parse($lexer, $keyword);
        } catch (ParserException $e) {
            return;
        }

        $this->fail("did not catch parser exception for invalid owner value");
    }

    public function testParseUid()
    {
        $lexer = new ICalLexer("Uid [vbox_7f0101_cc2_2]");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals("vbox_7f0101_cc2_2", $parser->getItem()->getUid());
        $this->assertTrue($parser->getItem()->getUidPersistent());
    }

    public function testParseUidNoUid()
    {
        $lexer = new ICalLexer("Uid []");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();

        try {
            $parser->parse($lexer, $keyword);
        } catch (ParserException $e) {
            return;
        }

        $this->fail("did not catch parser exception for invalid uid value");
    }

    public function testParseContents()
    {
        $lexer = new ICalLexer("Contents [Are these notes? what is this for?

and here's some text on a newline but with some trailing newlines too



]");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals("Are these notes? what is this for?

and here's some text on a newline but with some trailing newlines too", $parser->getItem()->getText());
    }

    public function testParseContentsEmpty()
    {
        $lexer = new ICalLexer("Contents []"); // this is allowed

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals("", $parser->getItem()->getText());
    }

    public function testParseDates()
    {
        $lexer = new ICalLexer("Dates [Single 30/9/2016 End    ]");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $dateSet = $parser->getItem()->getDate();

        $this->assertInstanceOf(DateSet::class, $dateSet);
        $this->assertEquals($dateSet->getStart()->format(LexerInterface::DATE_FORMAT), "30/9/2016");
    }

    public function testParseHilite()
    {
        $lexer = new ICalLexer("Hilite [always]");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals("always", $parser->getItem()->getHilite());
    }

    public function testParseHiliteNoHilite()
    {
        $lexer = new ICalLexer("Hilite []");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        try {
            $parser->parse($lexer, $keyword);
        } catch (ParserException $e) {
            return;
        }

        $this->fail("did not catch parser exception for invalid Hilite value");
    }

    public function testParseTodo()
    {
        $lexer = new ICalLexer("Todo []");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertTrue($parser->getItem()->getTodo());
    }

    public function testParseDone()
    {
        $lexer = new ICalLexer("Done []");

        $keyword = $this->getKeyword($lexer);
        $parser = new ItemParser();
        $parser->parse($lexer, $keyword);

        $this->assertTrue($parser->getItem()->getDone());
    }
}
