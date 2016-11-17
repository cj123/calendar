<?php

namespace CalendarBundle\Tests\Formatting\ICal\Parser;

use CalendarBundle\Entity\Appointment;
use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Parser\AppointmentParser;
use CalendarBundle\Formatting\ICal\Parser\ParserException;
use PHPUnit\Framework\TestCase;

/**
 * Class AppointmentParserTest
 * @package CalendarBundle\Tests\Formatting\ICal\Parser
 * @author Callum Jones <cj@icj.me>
 */
class AppointmentParserTest extends TestCase
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

    public function testIsAppointment()
    {
        $appointmentParser = new AppointmentParser();
        $this->assertInstanceOf(Appointment::class, $appointmentParser->getItem());
        $this->assertInstanceOf(Appointment::class, $appointmentParser->getAppointment());
    }

    public function testParseStart()
    {
        $lexer = new ICalLexer("Start [750]");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals(750, $parser->getAppointment()->getStartTime());
    }

    public function testParseStartAllowZero()
    {
        $lexer = new ICalLexer("Start [0]");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals(0, $parser->getAppointment()->getStartTime());
    }

    public function testParseStartNoTime()
    {
        $lexer = new ICalLexer("Start []");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();

        try {
            $parser->parse($lexer, $keyword);
        } catch (\Exception $e) {
            $this->assertInstanceOf(LexerException::class, $e);
            return;
        }

        $this->fail("no exception thrown for invalid start time");
    }

    public function testParseLength()
    {
        $lexer = new ICalLexer("Length [250]");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals(250, $parser->getAppointment()->getLength());
    }

    public function testParseLengthInvalidNumber()
    {
        $lexer = new ICalLexer("Length [dfsdfgdsj]");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();

        try {
            $parser->parse($lexer, $keyword);
        } catch (\Exception $e) {
            $this->assertInstanceOf(LexerException::class, $e);
            return;
        }

        $this->fail("no exception thrown for invalid length");
    }

    public function testParseTimezone()
    {
        $lexer = new ICalLexer("Timezone [America/Argentina/Mendoza]");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();
        $parser->parse($lexer, $keyword);

        $this->assertEquals("America/Argentina/Mendoza", $parser->getAppointment()->getTimezone());
    }


    // timezone can be omitted, but if the key is there it must be filled.
    public function testParseTimezoneInvalid()
    {
        $lexer = new ICalLexer("Timezone []");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();

        try {
            $parser->parse($lexer, $keyword);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ParserException::class, $e);
            return;
        }

        $this->fail("no exception thrown for invalid timezone");
    }

    public function testParseAlarms()
    {
        $lexer = new ICalLexer("Alarms [ 1 7 12 18]");

        $keyword = $this->getKeyword($lexer);
        $parser = new AppointmentParser();
        $parser->parse($lexer, $keyword);

        $alarms = $parser->getAppointment()->getAlarms();

        $this->assertTrue(is_array($alarms));
        $this->assertEquals(4, count($alarms));
    }
}
