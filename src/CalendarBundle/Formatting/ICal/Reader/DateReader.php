<?php

namespace CalendarBundle\Formatting\ICal\Reader;

use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;

/**
 * Class DateReader
 * @package CalendarBundle\Formatting\ICal\Reader
 * @author Callum Jones <cj@icj.me>
 */
class DateReader
{
    const DATE_FORMAT = "j/n/Y";

    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * DateReader constructor.
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @throws LexerException
     */
    public function read()
    {
        $this->lexer->skipWhitespace();
        $dateType = $this->lexer->getId();
        $this->lexer->skipWhitespace();



        switch ($dateType) {
            case "Empty":
                // not sure.

                break;

            case "Single":
                // event only occurs once.
                // pull the single date out.


                break;

            case "Days":
                // the date occurs every x days

                break;

            case "Months":
                // the date occurs every x months

                break;

            case "ComplexMonths":
                // not sure

                break;

            case "WeekDays":
                // the event occurs on weekdays

                break;

            case "MonthDays":
                // not sure

                break;
            default:
                throw new \Exception("unknown date type: " . $dateType);
        }

        // read the rest of the definition

        while (true) {
            $this->lexer->skipWhitespace();
            $keyword = $this->lexer->getId();



        }
    }

    /**
     * @param string $str
     * @return \DateTime
     */
    private function createDateTimeFromString(string $str)
    {
        $date = \DateTime::createFromFormat(static::DATE_FORMAT, $str);
        $date->setTime(0, 0); // reset to midnight
        return $date;
    }
}
