<?php

namespace CalendarBundle\Tests\Formatting\ICal\Parser;

use CalendarBundle\Entity\Note;
use CalendarBundle\Formatting\ICal\Parser\NoteParser;
use PHPUnit\Framework\TestCase;

/**
 * Class NoteParserTest
 * @package CalendarBundle\Tests\Formatting\ICal\Parser
 * @author Callum Jones <cj@icj.me>
 */
class NoteParserTest extends TestCase
{
    public function testNoteParser()
    {
        $noteParser = new NoteParser();
        $this->assertInstanceOf(Note::class, $noteParser->getNote());
        $this->assertInstanceOf(Note::class, $noteParser->getItem());
    }
}
