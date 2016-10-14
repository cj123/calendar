<?php

namespace CalendarBundle\Formatting\ICal\Writer;

use CalendarBundle\Entity\Item;

/**
 * Interface WriterInterface
 * @package CalendarBundle\Formatting\ICal\Writer
 * @author Callum Jones <cj@icj.me>
 */
interface WriterInterface
{
    /**
     * Write an Item.
     *
     * @param Item $item
     *
     * @return string
     *
     * @throws WriterException
     */
    public function write(Item $item): string;
}
