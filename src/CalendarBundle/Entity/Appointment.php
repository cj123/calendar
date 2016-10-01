<?php

namespace CalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Appointment
 *
 * @ORM\Table(name="appointment")
 * @ORM\Entity(repositoryClass="CalendarBundle\Repository\AppointmentRepository")
 */
class Appointment
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
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=255)
     */
    private $uid;

    /**
     * @var bool
     *
     * @ORM\Column(name="uid_persistent", type="boolean")
     */
    private $uidPersistent;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="remindStart", type="datetime")
     */
    private $remindStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="hilite", type="string", length=255)
     */
    private $hilite;

    /**
     * @var bool
     *
     * @ORM\Column(name="todo", type="boolean")
     */
    private $todo;

    /**
     * @var bool
     *
     * @ORM\Column(name="done", type="boolean")
     */
    private $done;


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
     * Set text
     *
     * @param string $text
     *
     * @return Appointment
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
     * @return Appointment
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
     * @return Appointment
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
     * @return Appointment
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
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return Appointment
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set remindStart
     *
     * @param \DateTime $remindStart
     *
     * @return Appointment
     */
    public function setRemindStart($remindStart)
    {
        $this->remindStart = $remindStart;

        return $this;
    }

    /**
     * Get remindStart
     *
     * @return \DateTime
     */
    public function getRemindStart()
    {
        return $this->remindStart;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Appointment
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set hilite
     *
     * @param string $hilite
     *
     * @return Appointment
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
     * @return Appointment
     */
    public function setTodo($todo)
    {
        $this->todo = $todo;

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
     * @return Appointment
     */
    public function setDone($done)
    {
        $this->done = $done;

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

