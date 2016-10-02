<?php

namespace CalendarBundle\Formatting\ICal\Reader;

use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Parser\AppointmentParser;

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
    private function checkCalendarVersion()
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
            $this->lexer->skip("[");
            $this->lexer->skip("v");

            $version = $this->lexer->getNumber();

            if ($version < static::MIN_VERSION || $version > static::MAX_VERSION) {
                throw new LexerException(); // we only support v2.x
            }

            $this->lexer->getUntil("]");
            $this->lexer->skip("]");
        } catch (LexerException $e) {
            throw new ReaderException("unsupported/invalid ical version");
        }
    }

    /**
     * Read an iCal Calendar.
     *
     * @throws ReaderException
     */
    public function read()
    {
        if ($this->lexer->status() === LexerInterface::ERROR) {
            throw new ReaderException("invalid calendar input");
        }

        $this->checkCalendarVersion();

        while (true) {
            try {
                $this->lexer->skipWhitespace();
                $this->lexer->peek();

                $keyword = $this->lexer->getId();

                $this->lexer->skipWhitespace();
                $this->lexer->skip("[");
            } catch (LexerException $e) {
                throw new ReaderException("caught lexer exception: {$e->getMessage()}");
            }

            switch ($keyword) {
                case "Appt":
                    $parser = new AppointmentParser();
                    $reader = new ItemReader($this->lexer, $parser);
                    $reader->read();

                    break;
            }

            try {
                $this->lexer->skipWhitespace();
                $this->lexer->skip("]");
            } catch (LexerException $e) {
                throw new ReaderException("incomplete item");
            }
        }
    }
}
