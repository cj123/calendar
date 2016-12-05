<?php declare(strict_types=1);

namespace CalendarBundle\Formatting\ICal\Parser;

use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;

/**
 * Interface ParserInterface
 * @package CalendarBundle\Formatting\ICal\Parser
 */
interface ParserInterface
{
    /**
     * Parse from a lexer given a keyword. Populates an item in the parser, which can be retrieved
     * when parsing is complete.
     *
     * @param LexerInterface $lexer
     * @param string $keyword
     *
     * @throws ParserException
     * @throws LexerException
     *
     * @return void
     */
    public function parse(LexerInterface $lexer, string $keyword);

    /**
     * Take out the iCal formatted date, put it into a more standard format on self::$item;
     *
     * @return void
     */
    public function extractDateSet();
}
