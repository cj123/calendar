<?php

namespace CalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Item
 *
 * @ORM\MappedSuperclass
 */
class Item
{
    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255)
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=255, unique=true)
     */
    protected $uid;

    /**
     * @var bool
     *
     * @ORM\Column(name="uid_persistent", type="boolean")
     */
    protected $uidPersistent;

    /**
     * @var int
     *
     * @ORM\Column(name="remindStart", type="integer")
     */
    protected $remindStart = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="hilite", type="string", length=255)
     */
    protected $hilite = "";

    /**
     * @var bool
     *
     * @ORM\Column(name="todo", type="boolean", nullable=true)
     */
    protected $todo;

    /**
     * @var bool
     *
     * @ORM\Column(name="done", type="boolean", nullable=true)
     */
    protected $done;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    protected $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="finish", type="datetime", nullable=true)
     */
    protected $finish;

    /**
     * @var array
     *
     * @ORM\Column(name="deleted", type="array")
     */
    protected $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="recurrence_rule", type="string", length=255)
     */
    protected $recurrenceRule = "";


    /**
     * Set start
     *
     * @param \DateTime $start
     *
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
     */
    public function setRecurrenceRule($recurrenceRule)
    {
        $this->recurrenceRule = $recurrenceRule;

        return $this;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Item
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set owner
     *
     * @param string $owner
     *
     * @return Item
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set uid
     *
     * @param string $uid
     *
     * @return Item
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set uidPersistent
     *
     * @param boolean $uidPersistent
     *
     * @return Item
     */
    public function setUidPersistent($uidPersistent)
    {
        $this->uidPersistent = $uidPersistent;

        return $this;
    }

    /**
     * Get uidPersistent
     *
     * @return bool
     */
    public function getUidPersistent()
    {
        return $this->uidPersistent;
    }

    /**
     * Set remindStart
     *
     * @param int $remindStart
     *
     * @return Item
     */
    public function setRemindStart($remindStart)
    {
        $this->remindStart = $remindStart;

        return $this;
    }

    /**
     * Get remindStart
     *
     * @return int
     */
    public function getRemindStart()
    {
        return $this->remindStart;
    }

    /**
     * Set hilite
     *
     * @param string $hilite
     *
     * @return Item
     */
    public function setHilite($hilite)
    {
        $this->hilite = $hilite;

        return $this;
    }

    /**
     * Get hilite
     *
     * @return string
     */
    public function getHilite()
    {
        return $this->hilite;
    }

    /**
     * Set todo
     *
     * @param boolean $todo
     *
     * @return Item
     */
    public function setTodo($todo)
    {
        $this->todo = $todo;
        $this->done = !$todo;

        return $this;
    }

    /**
     * Get todo
     *
     * @return bool
     */
    public function getTodo()
    {
        return $this->todo;
    }

    /**
     * Set done
     *
     * @param boolean $done
     *
     * @return Item
     */
    public function setDone($done)
    {
        $this->done = $done;
        $this->todo = !$done;

        return $this;
    }

    /**
     * Get done
     *
     * @return bool
     */
    public function getDone()
    {
        return $this->done;
    }
}

