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
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     * @return DateSet
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFinish()
    {
        return $this->finish;
    }

    /**
     * @param \DateTime $finish
     * @return DateSet
     */
    public function setFinish($finish)
    {
        $this->finish = $finish;

        return $this;
    }

    /**
     * Get Deleted Dates.
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
}
