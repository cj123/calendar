<?php

namespace CalendarBundle\Repository;

use CalendarBundle\Entity\Note;
use Doctrine\DBAL\Types\Type;

/**
 * Class NoteRepository
 * @package CalendarBundle\Repository
 * @author Callum Jones <cj@icj.me>
 */
class NoteRepository extends ItemRepository
{
    /**
     * Find Notes for a specific date.
     *
     * @param \DateTime $date
     * @return Note[]
     * @throws \Recurr\Exception\MissingData
     */
    public function findByDate(\DateTime $date): array
    {
        $date->setTime(0, 0, 0); // we only deal with days, not times

        $query = $this->getEntityManager()->createQuery(
            "
                SELECT n FROM CalendarBundle:Note n
                    WHERE
                        ((n.recurrenceRule != '' AND n.start <= :now AND (n.finish IS NULL OR :now <= n.finish))
                        OR (n.recurrenceRule = '' AND n.start = :now))

            " // @TODO: there's much more that can be done to improve the speed of this query.
        )->setParameter("now", $date, Type::DATETIME);

        return $this->processRecurrences($date, $query->getResult());
    }
}
