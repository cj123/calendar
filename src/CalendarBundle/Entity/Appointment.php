<?php

namespace CalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Appointment
 *
 * @ORM\Table(name="appointment")
 * @ORM\Entity(repositoryClass="CalendarBundle\Repository\AppointmentRepository")
 */
class Appointment extends Item
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="length", type="integer")
     */
    private $length;

    /**
     * @var array
     *
     * @ORM\Column(name="alarms", type="array")
     */
    private $alarms;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=255, nullable=true)
     */
    private $timezone = null;

    /**
     * @var Calendar
     *
     * @ORM\ManyToOne(targetEntity="Calendar", inversedBy="appointments")
     * @ORM\JoinColumn(name="calendar_id", referencedColumnName="id")
     * @Serializer\Exclude
     */
    private $calendar;

    /**
     * @var int
     *
     * @ORM\Column(name="startTime", type="integer")
     */
    private $startTime;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set length
     *
     * @param integer $length
     *
     * @return Appointment
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set alarms
     *
     * @param array $alarms
     *
     * @return Appointment
     */
    public function setAlarms($alarms)
    {
        $this->alarms = $alarms;

        return $this;
    }

    /**
     * Get alarms
     *
     * @return array
     */
    public function getAlarms()
    {
        return $this->alarms;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     *
     * @return Appointment
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param Calendar $calendar
     * @return Appointment
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param int $startTime
     * @return Appointment
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }
}

