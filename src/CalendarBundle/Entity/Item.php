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
     * @ORM\Column(name="uid", type="string", length=255)
     */
    protected $uid;

    /**
     * @var bool
     *
     * @ORM\Column(name="uid_persistent", type="boolean")
     */
    protected $uidPersistent;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    protected $deleted = false;

    /**
     * @var int
     *
     * @ORM\Column(name="remindStart", type="integer")
     */
    protected $remindStart;

    /**
     * @var DateSet
     *
     * @ORM\ManyToOne(targetEntity="DateSet", cascade={"persist"})
     * @ORM\JoinColumn(name="date_set_id", referencedColumnName="id")
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(name="hilite", type="string", length=255)
     */
    protected $hilite;

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
     * Set deleted
     *
     * @param boolean $deleted
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
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
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
     * Set date
     *
     * @param DateSet $date
     *
     * @return Item
     */
    public function setDate(DateSet $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return DateSet
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

