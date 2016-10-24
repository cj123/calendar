<?php

namespace CalendarBundle\Formatting\ICS\Reader;

use CalendarBundle\Formatting\ICal\Reader\ReaderException;
use CalendarBundle\Entity\Appointment;
use CalendarBundle\Entity\Calendar;
use Doctrine\Common\Collections\ArrayCollection;
use ICal\ICal as ICalParser;
use Recurr\Rule;

/**
 * Class CalendarReader
 * @package CalendarBundle\Formatting\ICS\Reader
 * @author Callum Jones <cj@icj.me>
 */
class CalendarReader
{
    /**
     * @var ICalParser
     */
    private $parser;

    /**
     * CalendarReader constructor.
     * @param ICalParser $parser
     */
    public function __construct(ICalParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return Calendar
     * @throws ReaderException
     */
    public function read(): Calendar
    {
        $calendar = new Calendar();

        $appointments = [];
        $uniqueIds = [];

        foreach ($this->parser->events() as $event) {
            // filter out already added events!
            if (in_array($event->uid, $uniqueIds)) {
                continue;
            }

            $uniqueIds[] = $event->uid;

            $appointment = new Appointment();

            $appointment->setText($event->summary . " " . $event->description);

            $start = $this->parseDateTime($event->dtstart);
            $end   = $this->parseDateTime($event->dtend);

            $appointment->setStartTime($this->getMinutesPastMidnight($start));
            $appointment->setLength(($end->getTimestamp() - $start->getTimestamp()) / 60);

            // our data structure just stores dates, times are stored elsewhere
            $appointment->setStart($start->setTime(0, 0));
            $appointment->setFinish($end->setTime(0, 0));

            if (property_exists($event, "tzid")) {
                $appointment->setTimezone($event->tzid);
            }

            if (property_exists($event, "rrule")) {
                $appointment->setRecurrenceRule($event->rrule);

                if (property_exists($event, "exdate")) {
                    // we need to deal with recurrences.
                    $exclusionDates = array_filter(
                        array_map(
                            function ($date) {
                                try {
                                    return $this->parseDateTime($date)->setTime(0, 0);
                                } catch (\Exception $e) {
                                    return null;
                                }
                            },
                            explode(",", $event->exdate)
                        )
                    );

                    $appointment->setDeleted($exclusionDates);
                }

                // exdates, rrules, etc.
                $rrule = new Rule($event->rrule);

                if ($rrule->getUntil()) {
                    $appointment->setFinish($rrule->getUntil()->setTime(0, 0));
                }
            }

            $appointment->setUid($event->uid);
            $appointment->setUidPersistent(true);
            $appointment->setOwner($event->organizer ?: "unknown");
            $appointment->setCalendar($calendar);

            $appointments[] = $appointment;
        }

        $calendar->setImportedDate(new \DateTime());
        $calendar->setVersion(2.0);
        $calendar->setAppointments(new ArrayCollection($appointments));

        return $calendar;
    }

    /**
     * @param string $str
     * @return \DateTime
     * @throws ReaderException
     */
    private function parseDateTime(string $str): \DateTime
    {
        $date = \DateTime::createFromFormat('Ymd\THis', $str);

        if (!$date) {
            throw new ReaderException("invalid datetime");
        }

        return $date;
    }

    /**
     * Get number of minutes since midnight.
     *
     * @param \DateTime $dateTime
     * @return int
     */
    private function getMinutesPastMidnight(\DateTime $dateTime): int
    {
        $midnight = clone $dateTime;
        $midnight->setTime(0, 0);

        return ($dateTime->getTimestamp() - $midnight->getTimestamp()) / 60;
    }
}
