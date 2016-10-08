<?php

namespace CalendarBundle\Formatting\ICal\Parser;

use CalendarBundle\Entity\Note;

/**
 * Class NoteParser
 * @package CalendarBundle\Formatting\ICal\Parser
 * @author Callum Jones <cj@icj.me>
 */
class NoteParser extends ItemParser
{
    /**
     * NoteParser constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->item = new Note();
    }

    /**
     * Get the note.
     *
     * @return Note
     */
    public function getNote(): Note
    {
        return $this->getItem();
    }
}
