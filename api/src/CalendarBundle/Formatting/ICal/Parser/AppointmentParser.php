<?php declare(strict_types=1);

namespace CalendarBundle\Formatting\ICal\Parser;

use CalendarBundle\Entity\Appointment;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;

/**
 * Class AppointmentParser
 * @package CalendarBundle\Formatting\ICal\Parser
 * @author Callum Jones <cj@icj.me>
 */
class AppointmentParser extends ItemParser
{
    /**
     * AppointmentParser constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // override Item with Appointment.
        $this->item = new Appointment();
    }

    /**
     * Parse from a lexer given a keyword. Populates an item in the parser, which can be retrieved
     * when parsing is complete.
     *
     * @param LexerInterface $lexer
     * @param string $keyword
     *
     * @throws ParserException
     * @throws LexerException
     *
     * @return void
     */
    public function parse(LexerInterface $lexer, string $keyword)
    {
        parent::parse($lexer, $keyword);

        switch ($keyword) {
            case "Start":
                $lexer->skipWhitespace();
                $start = $lexer->getNumber();
                $this->dateSet->setStartTime($start);

                break;

            case "Length":
                $lexer->skipWhitespace();
                $length = $lexer->getNumber();

                $this->dateSet->setLength($length);

                break;

            case "Timezone":
                $lexer->skipWhitespace();
                $timezone = $lexer->getString();

                if (!$timezone) {
                    throw new ParserException("unable to read appointment timezone");
                }

                $this->item->setTimezone($timezone);

                break;

            case "Alarms":
                $alarms = [];

                while (true) {
                    $lexer->skipWhitespace();
                    $char = $lexer->peek();

                    if (!preg_match("/[0-9]+/", $char)) {
                        break;
                    }

                    $alarms[] = $lexer->getNumber();
                }

                $this->item->setAlarms($alarms);

                break;
        }
    }

    /**
     * Get Appointment
     *
     * @return Appointment
     */
    public function getAppointment(): Appointment
    {
        return $this->item;
    }
}
