<?php declare(strict_types=1);

namespace CalendarBundle\Lexer;

/**
 * iCal-Tcl Lexer.
 *
 * The underlying interface for this class has been extracted from the ical-3.0.2 source.
 * See URL below.
 *
 * @source https://fossies.org/dox/ical-3.0.2/lexer_8h_source.html (accessed 30th September 2016)
 *
 * @package CalendarBundle\Lexer
 * @author Callum Jones <cj@icj.me>
 */
class ICalLexer implements LexerInterface
{
    const OPEN_STRING = "[";
    const CLOSE_STRING = "]";

    /**
     * Entire File Contents
     *
     * @var string
     */
    protected $buf;

    /**
     * self::$buf length
     *
     * @var int
     */
    protected $length;

    /**
     * Index of next character in file
     *
     * @var int
     */
    protected $index;

    /**
     * Lexer constructor.
     *
     * @param string $buffer
     */
    public function __construct(string $buffer)
    {
        $this->buf = $buffer;
        $this->length = strlen($this->buf);
        $this->index = 0;
    }

    /**
     * Get the current status
     *
     * @return int
     */
    public function status(): int
    {
        if ($this->index === $this->length) {
            return self::EOF;
        } else if ($this->index > $this->length) {
            return self::ERROR;
        } else {
            return self::VALID;
        }
    }

    /**
     * Return next character without advancing
     *
     * @return string
     */
    public function peek(): string
    {
        if ($this->index < $this->length) {
            return $this->buf[$this->index];
        }

        return "";
    }

    /**
     * Return next character and advance over it
     *
     * @return string
     */
    public function next(): string
    {
        if ($this->index < $this->length) {
            return $this->buf[$this->index++];
        }

        return "";
    }

    /**
     * Advance and then peek()
     *
     * @return string
     */
    public function advance(): string
    {
        $this->index++;

        if ($this->index < $this->length) {
            return $this->buf[$this->index];
        }

        // otherwise, undo the advance
        $this->index--;

        return "";
    }

    /**
     * Move position over expected text (character only)
     *
     * @param string $char
     *
     * @throws LexerException
     */
    public function skip(string $char)
    {
        if (strlen($char) > 1) {
            throw new LexerException("can only skip over characters");
        }

        if (($this->index < $this->length) && ($this->buf[$this->index] === $char)) {
            $this->index++;
            return;
        }

        throw new LexerException("unexpected character: " . $char);
    }

    /**
     * Skip all whitespace
     *
     * @return void
     */
    public function skipWhitespace()
    {
        while ($this->index < $this->length) {
            $char = $this->buf[$this->index];

            if (!ctype_space($char)) {
                return;
            }

            $this->index++;
        }
    }

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
    public function getId(): string
    {
        if ($this->index >= $this->length) {
            return "";
        }

        // identifiers cannot be whitespace, so we can safely skip it.
        $this->skipWhitespace();

        if (!$this->isLetter($this->buf[$this->index])) {
            throw new LexerException("illegal character, was expecting ID");
        }

        $begin = $this->index;

        while (($this->index < $this->length) && $this->isNumOrLetter($this->buf[$this->index])) {
            $this->index++;
        }

        return substr($this->buf, $begin, $this->index - $begin);
    }

    /**
     * Is a given char (or string) a letter?
     *
     * @param string $char
     * @return bool
     */
    private function isLetter(string $char): bool {
        return (bool) preg_match('/[a-z_]/i', $char);
    }

    /**
     * Is a given char (or string) a number or letter?
     *
     * @param string $char
     * @return bool
     */
    private function isNumOrLetter(string $char): bool {
        return (bool) preg_match('/[a-z0-9_]/i', $char);
    }

    /**
     * Read text until the specified character is hit
     *
     * @param string $char
     * @return string
     */
    public function getUntil(string $char): string
    {
        if ($this->index >= $this->length) {
            return "";
        }

        $begin = $this->index;

        while (($this->index < $this->length)) {
            if ($this->buf[$this->index] === $char) {
                break;
            }

            $this->index++;
        }

        return substr($this->buf, $begin, $this->index - $begin);
    }

    /**
     * Read Number.
     *
     * @return int
     */
    public function getNumber(): int
    {
        // @TODO
    }

    /**
     * Read a string. This is terminated at the first ']'. Backslashes can be used
     * to quote chars, so if the input contains "\]", this will be read as "]".
     *
     * If the input contains "\\", that will be read as a single backslash. The closing "]"
     * is not included in the returned string and is not consumed from the input.
     *
     * @return string
     */
    public function getString(): string
    {
        if ($this->index >= $this->length) {
            return "";
        }

        $out = "";

        while (($this->index < $this->length) && ($this->buf[$this->index] !== static::CLOSE_STRING)) {
            if ($this->buf[$this->index] === "\\") {
                $this->index++;
                if ($this->index >= $this->length) {
                    return "";
                }
            }

            $out .= $this->buf[$this->index];
            $this->index++;
        }

        return $out;
    }

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
    public static function putString(string $existing, string $append): string
    {
        $escapeChars = [ "\\" , static::OPEN_STRING, static::CLOSE_STRING ];

        for ($i = 0; $i < strlen($append); $i++) {
            $char = $append[$i];

            if (in_array($char, $escapeChars)) {
                // prepend them with \
                $existing .= "\\";
            }

            $existing .= $char;
        }

        return $existing;
    }

    /**
     * Get the current position in a file.
     *
     * @return int
     */
    public function index(): int
    {
        return $this->index;
    }

    /**
     * Reset to position returned by earlier "index" call.
     *
     * @param int $pos
     * @return mixed
     */
    public function reset(int $pos)
    {
        $this->index = $pos;
    }

    /**
     * Get length of file.
     *
     * @return int
     */
    public function length(): int
    {
        return $this->length;
    }
}
