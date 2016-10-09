<?php

namespace CalendarBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Calendar
 *
 * @ORM\Table(name="calendar")
 * @ORM\Entity(repositoryClass="CalendarBundle\Repository\CalendarRepository")
 */
class Calendar
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
     * @var float
     *
     * @ORM\Column(name="version", type="float")
     */
    private $version;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="importedDate", type="datetime")
     */
    private $importedDate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Appointment", mappedBy="calendar",cascade={"persist"})
     */
    private $appointments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Note", mappedBy="calendar",cascade={"persist"})
     */
    private $notes;

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
     * Set version
     *
     * @param float $version
     *
     * @return Calendar
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return float
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set importedDate
     *
     * @param \DateTime $importedDate
     *
     * @return Calendar
     */
    public function setImportedDate($importedDate)
    {
        $this->importedDate = $importedDate;

        return $this;
    }

    /**
     * Get importedDate
     *
     * @return \DateTime
     */
    public function getImportedDate()
    {
        return $this->importedDate;
    }

    /**
     * @param ArrayCollection $appointments
     * @return Calendar
     */
    public function setAppointments($appointments)
    {
        $this->appointments = $appointments;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAppointments()
    {
        return $this->appointments;
    }

    /**
     * @param ArrayCollection $notes
     * @return Calendar
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotes()
    {
        return $this->notes;
    }
}

