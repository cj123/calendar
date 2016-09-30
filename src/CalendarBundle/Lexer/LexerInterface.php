<?php declare(strict_types=1);

namespace CalendarBundle\Lexer;

/**
 * Lexer Interface
 *
 * This interface has been extracted from the ical-3.0.2 package.
 * See source below.
 *
 * Copyright (c) 1993 by Sanjay Ghemawat
 *
 * @source https://fossies.org/dox/ical-3.0.2/lexer_8h_source.html (accessed 30th September 2016)
 * @package CalendarBundle\Lexer
 *
 * @author Sanjay Ghemawat
 * @author Callum Jones <cj@icj.me>
 */
interface LexerInterface
{
    const VALID = 0;
    const EOF = 1;
    const ERROR = 2;

    /**
     * Get the current status
     *
     * @return int
     */
    public function status(): int;

    /**
     * Return next character without advancing
     *
     * @return string
     */
    public function peek(): string;

    /**
     * Return next character and advance over it
     *
     * @return string
     */
    public function next(): string;

    /**
     * Advance and then peek()
     *
     * @return string
     */
    public function advance(): string;

    /**
     * Move position over expected text (character only)
     *
     * @param string $char
     *
     * @throws LexerException
     */
    public function skip(string $char);

    /**
     * Skip all whitespace
     *
     * @return void
     */
    public function skipWhitespace();

    /**
     * Read an identifier.
     *
     * An identifier is a non-empty sequence of alphanumeric characters
     * (including underscores), where the first character is not a digit.
     *
     * @throws LexerException
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Read text until the specified character is hit
     *
     * @param string $char
     * @return string
     */
    public function getUntil(string $char): string;

    /**
     * Read Number.
     *
     * @return int
     */
    public function getNumber(): int;

    /**
     * Read a string. This is terminated at the first ']'. Backslashes can be used
     * to quote chars, so if the input contains "\]", this will be read as "]".
     *
     * If the input contains "\\", that will be read as a single backslash. The closing "]"
     * is not included in the returned string and is not consumed from the input.
     *
     * @return string
     */
    public function getString(): string;

    /**
     * Write a (safe, escaped) string to output string so that it can
     * be read back with a call to self::getString().
     *
     * No terminating "]" is output.
     *
     * @param string $existing
     * @param string $append
     * @return string
     */
    public static function putString(string $existing, string $append): string;

    /**
     * Get the current position in a file.
     *
     * @return int
     */
    public function index(): int;

    /**
     * Reset to position returned by earlier "index" call
     *
     * @param int $pos
     * @return mixed
     */
    public function reset(int $pos);

    /**
     * Get length of file.
     *
     * @return int
     */
    public function length(): int;
}
