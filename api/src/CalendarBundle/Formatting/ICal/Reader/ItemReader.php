<?php declare(strict_types=1);

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

    /**
     * ItemReader constructor.
     * @param LexerInterface $lexer
     * @param ParserInterface $parser
     */
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
            if ($char === LexerInterface::CLOSE_STRING) {
                break;
            }

            // get property name
            $keyword = $this->lexer->getId();
            $this->lexer->skipWhitespace();
            $this->lexer->skipOpeningDelimiter();

            // read property
            $this->parser->parse($this->lexer, $keyword);
            $this->parser->extractDateSet();

            $this->lexer->skipWhitespace();
            $this->lexer->skipClosingDelimiter();
        }
    }
}
