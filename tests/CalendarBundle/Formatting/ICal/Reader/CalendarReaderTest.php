<?php

namespace CalendarBundle\Tests\Formatting\ICal\Reader;

use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Reader\CalendarReader;
use CalendarBundle\Formatting\ICal\Reader\ReaderException;

/**
 * Class CalendarReaderTest
 * @package CalendarBundle\Tests\Formatting\ICal\Reader
 */
class CalendarReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckCalendarVersion()
    {
        $reader = new CalendarReader(new ICalLexer("Calendar [v2.0]"));

        $this->assertEquals(2.0, $reader->checkCalendarVersion());
    }

    public function testCheckCalendarVersionUnsupportedMaxVersion()
    {
        $reader = new CalendarReader(new ICalLexer("Calendar [v3.0]"));

        try {
            $reader->checkCalendarVersion();
        } catch (ReaderException $e) {
            return;
        }

        $this->fail("reader exception not caught");
    }

    public function testCheckCalendarVersionUnsupportedMinVersion()
    {
        $reader = new CalendarReader(new ICalLexer("Calendar [v1.99]"));

        try {
            $reader->checkCalendarVersion();
        } catch (ReaderException $e) {
            return;
        }

        $this->fail("reader exception not caught");
    }

    public function testCheckCalendarVersionInvalidFormat()
    {
        $reader = new CalendarReader(new ICalLexer("Caendar v1.99]"));

        try {
            $reader->checkCalendarVersion();
        } catch (ReaderException $e) {
            return;
        }

        $this->fail("reader exception not caught");
    }
}
