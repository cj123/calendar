<?php

namespace CalendarBundle\Formatting\ICal\Writer;

use CalendarBundle\Entity\Item;
use CalendarBundle\Entity\Note;

/**
 * Class NoteWriter
 * @package CalendarBundle\Formatting\ICal\Writer
 * @author Callum Jones <cj@icj.me>
 */
class NoteWriter extends ItemWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(Item $note): string
    {
        if (!$note instanceof Note) {
            throw new WriterException("invalid item, was expecting Note");
        }

        return parent::write($note);
    }
}
