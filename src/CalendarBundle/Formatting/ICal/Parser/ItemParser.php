<?php declare(strict_types=1);

namespace CalendarBundle\Formatting\ICal\Parser;

use CalendarBundle\Entity\Item;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Reader\DateReader;

/**
 * Class ItemParser
 * @package CalendarBundle\Formatting\ICal\Parser
 * @author Callum Jones <cj@icj.me>
 */
class ItemParser implements ParserInterface
{
    /**
     * @var Item
     */
    protected $item;

    /**
     * ItemParser constructor.
     */
    public function __construct()
    {
        // create the item to be parsed into.
        $this->item = new Item();
    }

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
    public function parse(LexerInterface $lexer, string $keyword)
    {
        switch ($keyword) {
            case "Remind":
                $lexer->skipWhitespace();
                $remindStart = $lexer->getNumber();

                if (!$remindStart) {
                    throw new ParserException("unable to read remind level");
                }

                $this->item->setRemindStart($remindStart);

                break;

            case "Owner":
                $lexer->skipWhitespace();
                $owner = $lexer->getString();

                if (!$owner) {
                    throw new ParserException("unable to read owner information");
                }

                $this->item->setOwner($owner);

                break;

            case "Uid":
                $lexer->skipWhitespace();
                $uid = $lexer->getUntil("]");

                if (!$uid) {
                    throw new ParserException("unable to read UID");
                }

                $this->item->setUid($uid);
                $this->item->setUidPersistent(true);

                break;

            case "Contents":
                $this->item->setText($lexer->getString());

                break;

            case "Dates":

                $dateReader = new DateReader($lexer);
                $date = $dateReader->read();

                var_dump($date);

                if ($date instanceof \DateTime) {
                    $this->item->setDate($date);
                }

                $lexer->getUntil("]"); // @TODO may not need this.

                break;

            case "Hilite":
                $hilite = $lexer->getString();

                if (!$hilite) {
                    throw new ParserException("unable to read hilite");
                }

                $this->item->setHilite($hilite);

                break;

            case "Todo":
                $this->item->setTodo(true);

                break;

            case "Done":
                $this->item->setDone(true);

                break;

            default:
                // @TODO default case?
                break;
        }
    }

    /**
     * Get Item
     *
     * @return Item
     */
    public function getItem(): Item {
        return $this->item;
    }
}
