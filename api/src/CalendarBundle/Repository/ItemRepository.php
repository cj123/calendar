<?php declare(strict_types = 1);

namespace CalendarBundle\Repository;

use CalendarBundle\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Mapping;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

/**
 * Class ItemRepository
 * @package CalendarBundle\Repository
 * @author Callum Jones <cj@icj.me>
 */
class ItemRepository extends EntityRepository
{
    /**
     * Find Appointments between dates.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @return Item[]
     */
    public function findBetweenDates(\DateTime $start, \DateTime $end): array
    {
        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);

        $className = $this->getClassName();

        $query = $this->getEntityManager()->createQuery(
            "
                SELECT a FROM ${className} a
                    WHERE (
                        a.start IS NOT NULL and a.finish IS NOT NULL AND (
                            a.start <= :start AND (a.finish IS NULL OR :end <= a.finish)
                        )
                    )
                    OR
                    (
                        a.recurrenceRule = ''
                        AND (
                            a.start >= :start AND a.start <= :end
                        )
                    )
                    ORDER BY a.start ASC, a.finish ASC

            " // @TODO: there's much more that can be done to improve the speed of this query.
        )->setParameter("start", $start, Type::DATETIME)
            ->setParameter("end", $end, Type::DATETIME);

        return $query->getResult();
    }
}
