<?php

namespace CalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;

/**
 * DateSet
 *
 * @ORM\Table(name="date_set")
 * @ORM\Entity(repositoryClass="CalendarBundle\Repository\DateSetRepository")
 */
class DateSet
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
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="finish", type="datetime", nullable=true)
     */
    private $finish;

    /**
     * @var array
     *
     * @ORM\Column(name="deleted", type="array")
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="recurrence_rule", type="string", length=255)
     */
    private $recurrenceRule;


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
}

