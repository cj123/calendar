<?php declare(strict_types = 1);

namespace CalendarBundle\Formatting\ICal\Writer;

use CalendarBundle\Entity\Item;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;

/**
 * Class ItemWriter
 * @package CalendarBundle\Formatting\ICal\Writer
 * @author Callum Jones <cj@icj.me>
 */
class ItemWriter implements WriterInterface
{
    /**
     * @var LexerInterface
     */
    protected $lexer;

    /**
     * ItemWriter constructor.
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Item $item): string
    {
        $text = $this->writeProperty("Uid", $item->getUid(), false);
        $item->setUidPersistent(true);

        if ($owner = $item->getOwner()) {
            $text .= $this->writeProperty("Owner", $owner);
        }

        $text .= $this->writeProperty("Contents", $item->getText());
        $text .= $this->writeProperty("Remind", (string) $item->getRemindStart(), false);

        $text .= $this->writeProperty("Hilite", $item->getHilite());

        if ($item->getTodo()) {
            $text .= $this->writeProperty("Todo", "", false);
        }

        if ($item->getDone()) {
            $text .= $this->writeProperty("Done", "", false);
        }

        return $text;
    }

    /**
     * Write a Property to a string.
     *
     * @param string $name
     * @param mixed $value
     * @param bool $escape
     *
     * @return string
     */
    protected function writeProperty(string $name, mixed $value, bool $escape = true): string
    {
        return sprintf("%s [%s]\n", $name, $escape ? $this->lexer->putString($value) : $value);
    }
}
