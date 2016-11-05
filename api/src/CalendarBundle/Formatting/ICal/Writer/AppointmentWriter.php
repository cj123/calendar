<?php declare(strict_types = 1);

namespace CalendarBundle\Formatting\ICal\Writer;

use CalendarBundle\Entity\Appointment;
use CalendarBundle\Entity\Item;

/**
 * Class AppointmentWriter
 * @package CalendarBundle\Formatting\ICal\Writer
 * @author Callum Jones <cj@icj.me>
 */
class AppointmentWriter extends ItemWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(Item $appointment): string
    {
        if (!$appointment instanceof Appointment) {
            throw new WriterException("invalid item, was expecting Appointment");
        }

        $text = $this->writeProperty("Start", (string) $appointment->getStart(), false);
        $text .= $this->writeProperty("Length", (string) $appointment->getLength(), false);

        if ($timezone = $appointment->getTimezone()) {
            $text .= $this->writeProperty("Timezone", $timezone, false);
        }

        $alarms = $appointment->getAlarms();

        if (count($alarms) > 0) {
            $text .= $this->writeProperty("Alarms", explode(" ", $alarms), false);
        }

        $text .= parent::write($appointment);

        return $text;
    }
}
