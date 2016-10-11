<?php

namespace CalendarBundle\Tests\Formatting\ICal\Reader;

use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Parser\ParserInterface;
use CalendarBundle\Formatting\ICal\Reader\ItemReader;
use CalendarBundle\Formatting\ICal\Reader\ReaderException;
use PHPUnit\Framework\TestCase;

/**
 * Class ItemReaderTest
 * @package CalendarBundle\Tests\Formatting\ICal\Reader
 * @author Callum Jones <cj@icj.me>
 */
class ItemReaderTest extends TestCase
{
    public function testRead()
    {
        $data = "TestParser [d]]"; // second ] to signal end of input

        $lexer = new ICalLexer($data);

        $parser = new class($this) implements ParserInterface {
            private $c;

            public function __construct(TestCase $c) {
                $this->c = $c;
            }

            public function parse(LexerInterface $lexer, string $keyword)
            {
                $this->c->assertEquals("d", $lexer->next());
            }
        };

        $reader = new ItemReader($lexer, $parser);
        $reader->read();

        $lexer->next(); // move along, now we should be at the end of the buffer

        $this->assertEquals(LexerInterface::EOF, $lexer->status());
    }

    public function testReadIncompleteItem()
    {
        $data = "TestParser []";

        $lexer = new ICalLexer($data);

        $mock = $this->getMockBuilder(ParserInterface::class)->getMock();
        $mock->method("parse")->willReturn(null);

        $reader = new ItemReader($lexer, $mock);

        try {
            $reader->read();
        } catch (\Exception $e) {
            $this->assertInstanceOf(ReaderException::class, $e);
            return;
        }

        $this->fail("no exception caught on reading incomplete item");
    }
}
