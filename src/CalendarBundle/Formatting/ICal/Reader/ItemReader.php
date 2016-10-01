<?php

namespace CalendarBundle\Formatting\ICal\Reader;

use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Parser\ParserInterface;

/**
 * Class ItemReader
 * @package CalendarBundle\Formatting\ICal\Reader
 * @author Callum Jones <cj@icj.me>
 */
class ItemReader
{
    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(LexerInterface $lexer, ParserInterface $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }

    /**
     * Read from an input.
     *
     * @throws ReaderException
     * @throws LexerException
     */
    public function read()
    {
        while (true) {
            $this->lexer->skipWhitespace();

            if (!$char = $this->lexer->peek()) {
                throw new ReaderException("incomplete item");
            }

            // end of input?
            if ($char = "]") {
                return;
            }

            // get property name
            $keyword = $this->lexer->getId();
            $this->lexer->skipWhitespace();
            $this->lexer->skip("[");

            // read property
            $this->parser->parse($this->lexer, $keyword);
        }
    }
}
