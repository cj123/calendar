<?php

namespace CalendarBundle\Formatting\ICal;

/**
 * Class DateSet
 * @package CalendarBundle\Formatting\ICal
 * @author Callum Jones <cj@icj.me>
 */
class DateSet
{
    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var \DateTime
     */
    private $finish;

    /**
     * @var array
     */
    private $deleted;

    /**
     * @var string
     */
    private $recurrenceRule;

    /**
     * @var int
     */
    private $length = 0;

    /**
     * @var int
     */
    private $startTime = 0;


    /**
     * Set start
     *
     * @param \DateTime $start
     *
     * @return DateSet
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set finish
     *
     * @param \DateTime $finish
     *
     * @return DateSet
     */
    public function setFinish($finish)
    {
        $this->finish = $finish;

        return $this;
    }

    /**
     * Get finish
     *
     * @return \DateTime
     */
    public function getFinish()
    {
        return $this->finish;
    }

    /**
     * Set deleted
     *
     * @param array $deleted
     *
     * @return DateSet
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return array
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Add a deleted date to the array set
     *
     * @param \DateTime $date
     */
    public function addDeleted(\DateTime $date)
    {
        $this->deleted[] = $date;
    }

    /**
     * Remove a deleted date from the array set
     *
     * @param \DateTime $date
     */
    public function removeDeleted(\DateTime $date)
    {
        foreach ($this->deleted as $index => $deleted) {
            if ($deleted === $date) {
                unset($this->deleted[$index]);
            }
        }
    }

    /**
     * Get Recurrence Rule
     *
     * @return string
     */
    public function getRecurrenceRule()
    {
        return $this->recurrenceRule;
    }

    /**
     * Set Recurrence Rule
     *
     * @param string $recurrenceRule
     *
     * @return DateSet
     */
    public function setRecurrenceRule($recurrenceRule)
    {
        $this->recurrenceRule = $recurrenceRule;

        return $this;
    }

    /**
     * @param int $length
     * @return DateSet
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $startTime
     * @return DateSet
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
