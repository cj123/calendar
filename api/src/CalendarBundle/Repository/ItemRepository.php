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
        $className = $this->getClassName();

        $query = $this->getEntityManager()->createQuery(
            "
                SELECT a FROM ${className} a
                    WHERE
                    a.start IS NOT NULL AND a.start <= :end
                    AND (a.recurrenceRule != '' OR (a.start <= :end AND a.start >= :start))
                    ORDER BY a.start ASC
            " // @TODO: there's much more that can be done to improve the speed of this query.
        );

        $query->setParameter("start", $start, Type::DATETIME);
        $query->setParameter("end", $end, Type::DATETIME);

        return $query->getResult();
    }
}
