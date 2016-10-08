<?php

namespace CalendarBundle\Formatting\ICal\Reader;

use CalendarBundle\Formatting\ICal\DateSet;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;

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
    private static $validDateTypes = [
        "Empty",
        "Single",
        "Days",
        "Months",
        "ComplexMonths",
        "WeekDays",
        "MonthDays",
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
    public function read()
    {
        $this->lexer->skipWhitespace();
        $dateType = $this->lexer->getId();

        if (!in_array($dateType, static::$validDateTypes)) {
            throw new DateReaderException("invalid date type: " . $dateType);
        }

        $dateSet = new DateSet();

        // read the date
        $this->lexer->skipWhitespace();
        $dateSet->setStart($this->createDateTimeFromString($this->lexer->getUntil(" ")));

        // read the rest of the definition
        while (true) {
            $this->lexer->skipWhitespace();
            $keyword = $this->lexer->getId();

            switch ($keyword) {
                case "End":
                    return $dateSet;
                case "Start":
                    $this->lexer->skipWhitespace();
                    $dateSet->setStart($this->createDateTimeFromString($this->lexer->getUntil(" ")));
                    break;

                case "Finish":
                    $this->lexer->skipWhitespace();
                    $dateSet->setFinish($this->createDateTimeFromString($this->lexer->getUntil(" ")));
                    break;

                case "Deleted":
                    $this->lexer->skipWhitespace();
                    $dateSet->addDeleted($this->createDateTimeFromString($this->lexer->getUntil(" ")));
                    break;

                default:
                    throw new DateReaderException("unrecognised DateSet keyword " . $keyword);
            }
        }

        return $dateSet;
    }

    /**
     * @param string $str
     * @return \DateTime
     */
    private function createDateTimeFromString(string $str)
    {
        $date = \DateTime::createFromFormat(static::DATE_FORMAT, $str);
        $date->setTime(0, 0); // reset to midnight, times are handled elsewhere.
        return $date;
    }
}
