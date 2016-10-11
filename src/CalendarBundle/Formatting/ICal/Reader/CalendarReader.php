<?php declare(strict_types=1);

namespace CalendarBundle\Formatting\ICal\Reader;

use CalendarBundle\Entity\Appointment;
use CalendarBundle\Entity\Calendar;
use CalendarBundle\Entity\CalendarOption;
use CalendarBundle\Entity\Note;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Parser\AppointmentParser;
use CalendarBundle\Formatting\ICal\Parser\NoteParser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class CalendarReader
 * @package CalendarBundle\Formatting\ICal\Reader
 * @author Callum Jones <cj@icj.me>
 */
class CalendarReader
{
    /**
     * Minimum version supported
     * @var float
     */
    const MIN_VERSION = 2.0;

    /**
     * Maximum version supported (version must be less than this)
     * @var float
     */
    const MAX_VERSION = 3.0;

    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * CalendarReader constructor.
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Check the iCal calendar version.
     *
     * If unsupported, reading should not continue.
     *
     * @throws ReaderException
     */
    public function checkCalendarVersion(): float
    {
        // skip preamble
        try {
            $this->lexer->skipWhitespace();
            $this->lexer->skip("Calendar");
        } catch (\Exception $e) {
            throw new ReaderException("invalid calendar format");
        }

        try {
            $this->lexer->skipWhitespace();
            $this->lexer->skipOpeningDelimiter();
            $this->lexer->skip("v");

            $version = $this->lexer->getNumber();

            if ($version < static::MIN_VERSION || $version >= static::MAX_VERSION) {
                throw new LexerException(); // we only support v2.x
            }

            $this->lexer->getUntil(LexerInterface::CLOSE_STRING);
            $this->lexer->skipClosingDelimiter();

            return $version;
        } catch (LexerException $e) {
            throw new ReaderException("unsupported/invalid ical version");
        }
    }

    /**
     * Read an iCal Calendar.
     *
     * @throws ReaderException
     */
    public function read(): Calendar
    {
        if ($this->lexer->status() === LexerInterface::ERROR) {
            throw new ReaderException("invalid calendar input");
        }

        $calendar = new Calendar();
        $calendar->setVersion($this->checkCalendarVersion());

        $appointments = [];
        $notes        = [];
        $options      = [];

        while (true) {
            try {
                $this->lexer->skipWhitespace();
                $this->lexer->peek();

                $keyword = $this->lexer->getId();

                $this->lexer->skipWhitespace();
                $this->lexer->skipOpeningDelimiter();
            } catch (LexerException $e) {
                if ($this->lexer->status() === LexerInterface::EOF) {
                    // end of file. stop reading.
                    break;
                }

                throw new ReaderException("caught lexer exception: {$e->getMessage()}");
            }

            switch ($keyword) {
                case "Appt":
                    $parser = new AppointmentParser();
                    $reader = new ItemReader($this->lexer, $parser);
                    $reader->read();

                    $appointments[] = $parser->getAppointment();

                    break;

                case "Note":
                    $parser = new NoteParser();
                    $reader = new ItemReader($this->lexer, $parser);
                    $reader->read();

                    $notes[] = $parser->getNote();

                    break;

                case "IncludeCalendar":
                    // @TODO include calendars not yet supported
                    break;

                case "Hide":
                    // @TODO hide not yet supported

                    break;

                default:
                    $option = new CalendarOption();

                    $option->setName($keyword);
                    $option->setValue($this->lexer->getString());

                    $options[] = $option;

                    break;
            }

            try {
                $this->lexer->skipWhitespace();
                $this->lexer->skipClosingDelimiter();
            } catch (LexerException $e) {
                throw new ReaderException("incomplete item");
            }
        } // while (true)

        foreach ($notes as $note) {
            /** @var $note Note */
            $note->setCalendar($calendar);
        }

        foreach ($appointments as $appointment) {
            /** @var $appointment Appointment */
            $appointment->setCalendar($calendar);
        }

        foreach ($options as $option) {
            /** @var $option CalendarOption */
            $option->setCalendar($calendar);
        }

        $calendar->setImportedDate(new \DateTime());
        $calendar->setAppointments(new ArrayCollection($appointments));
        $calendar->setNotes(new ArrayCollection($notes));
        $calendar->setOptions(new ArrayCollection($options));

        return $calendar;
    }
}
