<?php

namespace CalendarBundle\Tests\Formatting\ICal\Reader;

use CalendarBundle\Entity\Appointment;
use CalendarBundle\Entity\Calendar;
use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Reader\CalendarReader;
use CalendarBundle\Formatting\ICal\Reader\ReaderException;
use PHPUnit\Framework\TestCase;

/**
 * Class CalendarReaderTest
 * @package CalendarBundle\Tests\Formatting\ICal\Reader
 */
class CalendarReaderTest extends TestCase
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

    public function testRead()
    {
        $calendar = <<<EOF
Calendar [v2.0]
MondayFirst [1]
Appt [
Start [870]
Length [30]
Uid [eskimo_0_f57_d]
Owner [cj]
Contents [complex by months 15th last day
]
Remind [1]
Hilite [always]
Dates [ComplexMonths 1 15 16/11/2016 Backward ByDay
Start 16/11/2016 End
]
]
Note [
Uid [vbox_7f0101_cc2_6]
Owner [callum]
Contents [Are these notes? what is this for?


]
Remind [1]
Hilite [always]
Dates [Single 30/9/2016 End
]
]
EOF;

        $lexer = new ICalLexer($calendar);
        $reader = new CalendarReader($lexer);
        $calendar = $reader->read();

        $this->assertInstanceOf(Calendar::class, $calendar);
        $this->assertEquals(1, $calendar->getAppointments()->count());
        $this->assertEquals(1, $calendar->getNotes()->count());
        $this->assertEquals(1, $calendar->getOptions()->count());
        $this->assertEquals(2, $calendar->getVersion());

        /** @var Appointment $appt */
        $appt = $calendar->getAppointments()->get(0);
        $this->assertEquals("cj", $appt->getOwner());
        $this->assertEquals(1, $appt->getRemindStart());
        $actual = \DateTime::createFromFormat(LexerInterface::DATE_FORMAT, "16/11/2016")->setTime(0, 0);
        $this->assertEquals($actual, $appt->getStart());
    }
}
