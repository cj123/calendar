<?php declare(strict_types = 1);

namespace CalendarBundle\Gateway;

use CalendarBundle\Entity\Item;
use Recurr\DateExclusion;
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
    public function findRecurrencesBetweenDates(
        Item $item,
        \DateTime $start,
        \DateTime $finish
    ): RecurrenceCollection
    {
        $rule = $item->getRecurrenceRule();

        if (!$rule) {
            $itemStart = $item->getStart();
            $itemEnd   = $item->getFinish();

            if ($itemStart && $itemEnd && $itemStart != $itemEnd) {
                $period = new \DatePeriod(
                    $itemStart,
                    new \DateInterval('P1D'),
                    $itemEnd
                );

                return new RecurrenceCollection(array_map(function($date) {
                    return new Recurrence($date);
                }, iterator_to_array($period)));
            } elseif ($itemStart) {
                return new RecurrenceCollection([ new Recurrence($itemStart) ]);
            } else {
                return new RecurrenceCollection();
            }
        }

        $startDate = $item->getStart();
        $endDate = $item->getFinish();

        $rrule = new Rule($rule, $startDate, $endDate);

        return $this->transformer->transform(
            $rrule,
            new BetweenConstraint($start, $finish, true)
        );
    }
}
