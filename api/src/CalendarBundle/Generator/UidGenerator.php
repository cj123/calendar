<?php

namespace CalendarBundle\Generator;

/**
 * Class UidGenerator
 * @package CalendarBundle\Generator
 * @author Callum Jones <cj@icj.me>
 */
class UidGenerator
{
    /**
     * Generate a UID
     *
     * @return string
     */
    public function generate(): string
    {
        return uniqid("myCal_") . time();
    }
}
