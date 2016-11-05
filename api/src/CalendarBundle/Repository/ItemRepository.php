<?php

namespace CalendarBundle\Repository;

use CalendarBundle\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Mapping;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Type;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\Constraint\BetweenConstraint;

/**
 * Class ItemRepository
 * @package CalendarBundle\Repository
 * @author Callum Jones <cj@icj.me>
 */
class ItemRepository extends EntityRepository
{
    /**
     * Take a result set and parse out occurrences.
     *
     * @param \DateTime $date
     * @param array $items
     * @return array
     * @throws \Recurr\Exception\MissingData
     */
    protected function processRecurrences(\DateTime $date, array $items): array
    {
        $recurrenceConstraint = new BetweenConstraint($date, $date, true);
        $recurrenceTransformer = new ArrayTransformer();

        $resultSet = [];

        foreach ($items as $item) {
            if (!$item instanceof Item) {
                continue;
            }

            $deletedDates = $item->getDeleted();

            // @TODO maybe this could be done in SQL?
            if ($deletedDates && in_array($date, $deletedDates)) {
                // skip deleted dates.
                continue;
            }

            if (!$item->getRecurrenceRule()) {
                $resultSet[] = $item;
                continue;
            }

            // parse out recurrence rules.
            $recurrenceRule = new Rule($item->getRecurrenceRule(), $item->getStart(), $item->getFinish());

            $instances = $recurrenceTransformer->transform($recurrenceRule, $recurrenceConstraint);

            if (count($instances) === 0) {
                continue;
            }

            $resultSet[] = $item;
        }

        return $resultSet;
    }
}
