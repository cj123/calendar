<?php

namespace CalendarBundle\Tests\Formatting\ICal\Reader;

use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Reader\DateReader;
use CalendarBundle\Formatting\ICal\Reader\DateReaderException;
use Recurr\Frequency;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;

/**
 * Class DateReaderTest
 * @package CalendarBundle\Tests\Formatting\ICal\Reader
 */
class DateReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testReadSingleDate()
    {
        $lexer = new ICalLexer("Single 20/9/2016 End");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $this->assertEquals("", $dateSet->getRecurrenceRule()); // single dates should never recur.
        $this->assertInstanceOf("\\DateTime", $dateSet->getStart());
        $this->assertNull($dateSet->getFinish());
    }

    public function testReadDays()
    {
        $lexer = new ICalLexer("
            Days 8/10/2016 1
            Start 8/10/2016
            Finish 31/12/2016 End");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $this->assertInstanceOf("\\DateTime", $dateSet->getStart());
        $this->assertInstanceOf("\\DateTime", $dateSet->getFinish());
        $this->assertNotEmpty($dateSet->getRecurrenceRule());
    }

    public function testReadMonths()
    {
        $lexer = new ICalLexer("
            Months 8/10/2016 3
            Start 8/10/2016 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $this->assertInstanceOf("\\DateTime", $dateSet->getStart());
        $this->assertNull($dateSet->getFinish());
        $this->assertNotEmpty($dateSet->getRecurrenceRule());
    }

    public function testReadWeekDays()
    {
        $lexer = new ICalLexer("
            WeekDays  1 2 3 4 7 Months  1 2 3 4 5 6 7 8 9 10 11 12
            Start 8/10/2016 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $this->assertInstanceOf("\\DateTime", $dateSet->getStart());
        $this->assertNull($dateSet->getFinish());
        $this->assertNotEmpty($dateSet->getRecurrenceRule());

        $rrule = $dateSet->getRecurrenceRule();
        $rule = new Rule($rrule);

        $this->assertEquals(Frequency::WEEKLY, $rule->getFreq());
    }

    public function testReadWeekDaysExtraDays()
    {
        $lexer = new ICalLexer("
            WeekDays  1 2 3 4 7 8 9 10 Months  1 2 3 4 5 6 7 8 9 10 11 12
            Start 8/10/2016 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        // these extra days will just be ignored, so everything should look the same.
        $this->assertInstanceOf("\\DateTime", $dateSet->getStart());
        $this->assertNull($dateSet->getFinish());
        $this->assertNotEmpty($dateSet->getRecurrenceRule());
    }

    public function testReadWeekDaysExtraMonths()
    {
        $lexer = new ICalLexer("
            WeekDays  1 2 3  Months  1 14 20 333
            Start 8/10/2016 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        // these extra months will just be ignored, so everything should look the same.
        $this->assertInstanceOf("\\DateTime", $dateSet->getStart());
        $this->assertNull($dateSet->getFinish());
        $this->assertNotEmpty($dateSet->getRecurrenceRule());
    }

    public function testReadMonthDays()
    {
        $lexer = new ICalLexer("
            MonthDays  1 2 3 4 7 Months  1 2 3 4
            Start 8/10/2016
            Finish 8/10/2020 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $this->assertInstanceOf("\\DateTime", $dateSet->getStart());
        $this->assertInstanceOf("\\DateTime", $dateSet->getFinish());
        $this->assertNotEmpty($dateSet->getRecurrenceRule());

        $rrule = $dateSet->getRecurrenceRule();
        $rule = new Rule($rrule);

        $this->assertEquals(Frequency::MONTHLY, $rule->getFreq());
    }

    public function testReadMonthDaysInvalidKeyword()
    {
        $lexer = new ICalLexer("
            MonthDays  1 2 3 4 7 Years  1 2 3 4
            Start 8/10/2016
            Finish 8/10/2020 End
        ");
        $reader = new DateReader($lexer);

        try {
            $reader->read();
        } catch (DateReaderException $e) {
            return;
        }

        $this->fail("invalid keyword was allowed");
    }

    public function testReadComplexMonthsBackward()
    {
        $lexer = new ICalLexer("
            ComplexMonths 1 3 8/10/2016 Backward ByWeek 7
            Start 8/10/2016 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $rrule = $dateSet->getRecurrenceRule();
        $this->assertNotEmpty($rrule);

        $rule = new Rule($rrule);
        $this->assertEquals(Frequency::MONTHLY, $rule->getFreq());

        $transformer = new ArrayTransformer();
        $dates = $transformer->transform($rule);

        $this->assertGreaterThan(0, count($dates));
    }

    public function testReadComplexMonthsInvalidWeekDay()
    {
        $lexer = new ICalLexer("
            ComplexMonths 1 3 8/10/2016 Backward ByWeek 9
            Start 8/10/2016 End
        ");
        $reader = new DateReader($lexer);

        try {
            $reader->read();
        } catch (DateReaderException $e) {
            return;
        }

        $this->fail("did not catch exception for invalid weekday");
    }

    public function testReadComplexMonthsInvalidRepetitionType()
    {
        $lexer = new ICalLexer("
            ComplexMonths 1 3 8/10/2016 Backward ByAnnum 5
            Start 8/10/2016 End
        ");
        $reader = new DateReader($lexer);

        try {
            $reader->read();
        } catch (DateReaderException $e) {
            return;
        }

        $this->fail("did not catch exception for invalid repetition type");
    }

    public function testReadComplexMonthsForwardWeekday()
    {
        $lexer = new ICalLexer("
            ComplexMonths 1 12 16/11/2016 Forward ByWorkDay
            Start 16/11/2016 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $rrule = $dateSet->getRecurrenceRule();
        $this->assertNotEmpty($rrule);

        $rule = new Rule($rrule);
        $this->assertEquals(Frequency::MONTHLY, $rule->getFreq());

        $transformer = new ArrayTransformer();
        $dates = $transformer->transform($rule);

        $this->assertGreaterThan(0, count($dates));
    }

    public function testReadComplexMonthsBackwardByDay()
    {
        $lexer = new ICalLexer("
            ComplexMonths 1 15 16/11/2016 Backward ByDay
            Start 16/11/2016 End
        ");
        $reader = new DateReader($lexer);
        $dateSet = $reader->read();

        $rrule = $dateSet->getRecurrenceRule();
        $this->assertNotEmpty($rrule);

        $rule = new Rule($rrule);
        $this->assertEquals(Frequency::MONTHLY, $rule->getFreq());

        $transformer = new ArrayTransformer();
        $dates = $transformer->transform($rule);

        $this->assertGreaterThan(0, count($dates));
    }

    public function testReadComplexMonthsInvalidDirection()
    {
        $lexer = new ICalLexer("
            ComplexMonths 1 12 16/11/2016 Left ByWorkDay
            Start 16/11/2016 End
        ");
        $reader = new DateReader($lexer);

        try {
            $reader->read();
        } catch (DateReaderException $e) {
            return;
        }

        $this->fail("invalid direction did not cause exception");
    }

    public function testReadInvalidDateType()
    {
        $lexer = new ICalLexer("
            DifficultYears 1 12 16/11/2016 Left ByWorkDay
            Start 16/11/2016 End
        ");
        $reader = new DateReader($lexer);

        try {
            $reader->read();
        } catch (DateReaderException $e) {
            return;
        }

        $this->fail("invalid date type did not cause exception");
    }
}
