<?php declare(strict_types=1);

namespace CalendarBundle\Formatting\ICal\Parser;

use CalendarBundle\Formatting\ICal\DateSet;
use CalendarBundle\Entity\Item;
use CalendarBundle\Formatting\ICal\Lexer\LexerException;
use CalendarBundle\Formatting\ICal\Lexer\LexerInterface;
use CalendarBundle\Formatting\ICal\Reader\DateReader;
use Recurr\DateExclusion;
use Recurr\Rule;

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
     * @var DateSet
     */
    protected $dateSet;

    /**
     * ItemParser constructor.
     */
    public function __construct()
    {
        // create the item to be parsed into.
        $this->item = new Item();
        $this->dateSet = new DateSet();
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
                $this->item->setText(trim($lexer->getString())); // @TODO check on trim, but i think it's better.

                break;

            case "Dates":

                $dateReader = new DateReader($lexer);
                $dateReader->read($this->dateSet);

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
        }
    }

    /**
     * Take out the iCal formatted date, put it into a more standard format on self::$item;
     *
     * @return void
     */
    public function extractDateSet()
    {
        $date = $this->dateSet;

        if ($start = $date->getStart()) {
            $start->setTime(0, 0);
            $this->item->setStart(clone $start->modify("+ " . $date->getStartTime() . "minutes"));
            $this->item->setFinish(clone $start->modify("+ " . $date->getLength() . "minutes"));
        }

        if ($rule = $date->getRecurrenceRule()) {
            $rrule = new Rule($rule);

            if ($deleted = $date->getDeleted()) {
                $rrule->setExDates(array_map(function(\DateTime $date) {
                    return new DateExclusion($date);
                }, $deleted));
            }

            if ($finish = $date->getFinish()) {
                $finish->setTime(0, 0);
                $rrule->setUntil(clone $finish->modify("+ " . $date->getLength() . "minutes"));
            }

            $this->item->setRecurrenceRule($rrule->getString());
        }
    }

    /**
     * Get Item
     *
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * Get DateSet
     *
     * @return DateSet
     */
    public function getDateSet(): DateSet
    {
        return $this->dateSet;
    }
}
