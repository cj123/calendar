<?php declare(strict_types = 1);

namespace CalendarBundle\Gateway;

use CalendarBundle\Entity\Item;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\Constraint\BetweenConstraint;

/**
 * Class RecurrenceGateway
 * @package CalendarBundle\Gateway
 * @author Callum Jones <cj@icj.me>
 */
class RecurrenceGateway
{
    /**
     * @var ArrayTransformer
     */
    private $transformer;

    /**
     * RecurrenceGateway constructor.
     * @param ArrayTransformer $transformer
     */
    public function __construct(ArrayTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Don't use this in a loop.
     *
     * @param Item $item
     * @param \DateTime $date
     * @return bool
     * @throws \Recurr\Exception\MissingData
     * @deprecated don't use me
     */
    public function recurrenceOccursOnDate(Item $item, \DateTime $date): bool
    {
        $date->setTime(0, 0, 0);
        $rule = $item->getRecurrenceRule();

        $startDate = $item->getStart();
        $endDate = $item->getFinish();

        if (!$rule) {
            $timestamp = $date->getTimestamp();

            return $timestamp > $startDate->getTimestamp() && ($endDate === null || $timestamp < $endDate->getTimestamp());
        }

        $dates = $this->transformer->transform(
            new Rule($rule, $startDate, $endDate),
            new BetweenConstraint($date, $date, true)
        );

        return $dates->count() > 0;
    }

    /**
     * Given an array of items, keep the ones which occur on the given date
     *
     * @param Item[] $items
     * @param \DateTime $date
     * @return array
     */
    public function filterItemsByDate(array $items, \DateTime $date)
    {
        $results = [];

        foreach ($items as $item) {
            if (!$item instanceof Item) {
                continue;
            }

            if (!$item->getRecurrenceRule()) {
                $results[] = $item;
                continue;
            }

            // generate recurrences
            $recurrences = $this->findRecurrencesBetweenDates(
                $item,
                $date,
                $date
            );

            // go through each day checking if in recurrences.
            foreach ($recurrences as $recurrence) {
                // only startdate of recurrence rule is valid, not end date.
                /** @var Recurrence $recurrence */
                // dates can't be compared with strict check.
                if ($recurrence->getStart() == $date) {
                    $results[] = $item;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Find all recurrences between two given dates.
     *
     * Start and finish can be equal to represent the same day.
     *
     * @param Item $item
     * @param \DateTime $start
     * @param \DateTime $finish
     * @return RecurrenceCollection
     * @throws \Recurr\Exception\MissingData
     */
    public function findRecurrencesBetweenDates(Item $item, \DateTime $start, \DateTime $finish): RecurrenceCollection
    {
        $rule = $item->getRecurrenceRule();

        if (!$rule) {
            return new RecurrenceCollection([ new Recurrence($item->getStart(), $item->getFinish()) ]);
        }

        $startDate = $item->getStart();
        $endDate = $item->getFinish();

        return $this->transformer->transform(
            new Rule($rule, $startDate, $endDate),
            new BetweenConstraint($start, $finish, true)
        );
    }
}
