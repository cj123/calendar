<?php declare(strict_types=1);

namespace CalendarBundle\Formatting\ICal\Reader;

use CalendarBundle\Entity\DateSet;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use Recurr\Frequency;
use Recurr\Rule;

/**
 * Class DateReader
 * @package CalendarBundle\Formatting\ICal\Reader
 * @author Callum Jones <cj@icj.me>
 */
class DateReader
{
    /**
     * Given Date format.
     * @var string
     */
    const DATE_FORMAT = "j/n/Y";

    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * Valid Date Types.
     *
     * @var array
     */
    public static $validDateTypes = [
        "Empty",
        "Single",
        "Days",
        "Months",
        "ComplexMonths",
        "WeekDays",
        "MonthDays",
    ];

    /**
     * Week Days. Note the week in ical-tcl starts on a Sunday.
     *
     * @var array
     */
    private static $days = [
        1 => "SU",
        2 => "MO",
        3 => "TU",
        4 => "WE",
        5 => "TH",
        6 => "FR",
        7 => "SA",
    ];

    /**
     * DateReader constructor.
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @return DateSet
     * @throws DateReaderException
     */
    public function read(): DateSet
    {
        $this->lexer->skipWhitespace();
        $dateType = $this->lexer->getId();

        $recurrenceRule = new Rule();

        switch ($dateType) {
            case "Single":
                // single occurrence. no rule required.
                break;

            case "Days":
                $this->lexer->skipWhitespace();
                $anchor = $this->createDateTimeFromString($this->lexer->getUntil(" "));
                $this->lexer->skipWhitespace();
                $interval = $this->lexer->getNumber();

                $recurrenceRule->setFreq(Frequency::DAILY);
                $recurrenceRule->setStartDate($anchor);
                $recurrenceRule->setInterval($interval);

                break;

            case "Months":
                $this->lexer->skipWhitespace();
                $anchor = $this->createDateTimeFromString($this->lexer->getUntil(" "));
                $this->lexer->skipWhitespace();
                $interval = $this->lexer->getNumber();

                $recurrenceRule->setFreq(Frequency::MONTHLY);
                $recurrenceRule->setStartDate($anchor);
                $recurrenceRule->setInterval($interval);

                break;

            case "ComplexMonths":
                // month based date set
                $this->lexer->skipWhitespace();
                $interval = (int) $this->lexer->getUntil(" ");

                $this->lexer->skipWhitespace();
                $count = (int) $this->lexer->getUntil(" ");

                $this->lexer->skipWhitespace();
                $anchor = $this->createDateTimeFromString($this->lexer->getUntil(" "));

                $this->lexer->skipWhitespace();
                $direction = $this->lexer->getId();

                if ($direction === "Backward") {
                    // we're counting back from the end of the month
                    $sign = -1;
                } elseif ($direction === "Forward") {
                    // we're counting from the beginning of the month
                    $sign = +1;
                } else {
                    throw new DateReaderException("ComplexMonths format must be either Forward or Backward. Neither were found");
                }

                $this->lexer->skipWhitespace();
                $repetition = $this->lexer->getId();

                $recurrenceRule->setFreq(Frequency::MONTHLY);

                if ($repetition === "ByDay") {
                    $recurrenceRule->setBySetPosition([ $sign * $count ]);
                } elseif ($repetition === "ByWorkDay") {
                    $recurrenceRule->setByDay([ "MO", "TU", "WE", "TH", "FR" ]);
                    $recurrenceRule->setBySetPosition([ $sign * $count ]);
                } elseif ($repetition === "ByWeek") {
                    // set based on a day of the week, e.g. 3rd last tuesday.
                    $weekDay = (int) $this->lexer->getNumber();

                    if ($weekDay > 7 || $weekDay < 1) {
                        throw new DateReaderException("invalid weekday, must be in range 1 <= weekday <= 7");
                    }

                    $recurrenceRule->setByDay([($sign * $count) . self::$days[$weekDay]]);
                } else {
                    throw new DateReaderException("unsupported repetition type");
                }

                $recurrenceRule->setStartDate($anchor);
                $recurrenceRule->setInterval($interval);

                break;

            case "WeekDays":
            case "MonthDays":
                $this->lexer->skipWhitespace();
                $days = $this->readDays();

                $this->lexer->skipWhitespace();
                $keyword = $this->lexer->getId();

                if ($keyword !== "Months") {
                    throw new DateReaderException("invalid weekdays identifier");
                }

                $this->lexer->skipWhitespace();
                $months = $this->readMonths();

                if ($keyword === "WeekDays") {
                    $recurrenceRule->setFreq(Frequency::WEEKLY);
                } elseif ($keyword ===  "MonthDays") {
                    $recurrenceRule->setFreq(Frequency::MONTHLY);
                }

                $recurrenceRule->setByDay($days);
                $recurrenceRule->setByMonth($months);

                break;

            default:
                throw new DateReaderException("invalid date type: " . $dateType);
        }

        $dateSet = new DateSet();

        // read the recurrence rule out to a string as it can be parsed back later
        $dateSet->setRecurrenceRule($recurrenceRule->getString());

        // read the rest of the definition
        while (true) {
            $this->lexer->skipWhitespace();
            try {
                $keyword = $this->lexer->getId();
            } catch (LexerException $e) {
                // it is likely that we're at the end of the current one. move on.
                break;
            }

            switch ($keyword) {
                case "End":
                    return $dateSet;
                case "Start":
                    $this->lexer->skipWhitespace();
                    $dateSet->setStart(
                        $this->createDateTimeFromString($this->lexer->getUntil(" "))
                    );
                    break;

                case "Finish":
                    $this->lexer->skipWhitespace();
                    $dateSet->setFinish(
                        $this->createDateTimeFromString($this->lexer->getUntil(" "))
                    );
                    break;

                case "Deleted":
                    $this->lexer->skipWhitespace();
                    $dateSet->addDeleted(
                        $this->createDateTimeFromString($this->lexer->getUntil(" "))
                    );
                    break;

                default:
                    throw new DateReaderException("unrecognised DateSet keyword " . $keyword);
            }
        }

        return $dateSet;
    }

    /**
     * Create a DateTime instance from string representation of that date.
     *
     * Set to midnight by default.
     *
     * @param string $str
     *
     * @return \DateTime
     */
    private function createDateTimeFromString(string $str): \DateTime
    {
        $date = \DateTime::createFromFormat(static::DATE_FORMAT, $str);
        $date->setTime(0, 0); // reset to midnight, times are handled elsewhere.
        return $date;
    }

    /**
     * Read Days given the current lexer position
     *
     * @return array
     */
    private function readDays(): array
    {
        $days = [];

        while (true) {
            $this->lexer->skipWhitespace();

            $peek = $this->lexer->peek();

            if (!preg_match('/[0-9]/', $peek)) {
                break;
            }

            $day = (int) $this->lexer->getUntil(" ");

            if ($day > 7 || $day < 1) {
                break;
            }

            $days[] = self::$days[$day];
        }

        return $days;
    }

    /**
     * Read Months given the current lexer position
     *
     * @return array
     */
    private function readMonths(): array
    {
        $months = [];

        while (true) {
            $this->lexer->skipWhitespace();

            $peek = $this->lexer->peek();

            if (!preg_match('/[0-9]/', $peek)) {
                break;
            }

            $month = (int) $this->lexer->getUntil(" ");

            if ($month > 12 || $month < 1) {
                break;
            }

            $months[] = $month;
        }

        return $months;
    }
}
