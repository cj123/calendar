<?php

namespace CalendarBundle\Repository;

use CalendarBundle\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Mapping;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\DBAL\Types\Type;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\Constraint\BetweenConstraint;

/**
 * Class AppointmentRepository
 * @package CalendarBundle\Repository
 * @author Callum Jones <cj@icj.me>
 */
class AppointmentRepository extends EntityRepository
{
    /**
     * Find Appointments for a specific date.
     *
     * @param \DateTime $date
     * @return Appointment[]
     * @throws \Recurr\Exception\MissingData
     */
    public function findByDate(\DateTime $date): array
    {
        $date->setTime(0, 0, 0); // we only deal with days, not times

        $query = $this->getEntityManager()->createQuery(
            "
                SELECT a FROM CalendarBundle:Appointment a
                    WHERE
                        ((a.recurrenceRule != '' AND a.start <= :now AND (a.finish IS NULL OR :now <= a.finish))
                        OR (a.recurrenceRule = '' AND a.start = :now))
                    ORDER BY a.startTime ASC

            " // @TODO: there's much more that can be done to improve the speed of this query.
        )->setParameter("now", $date, Type::DATETIME);

        $results = $query->getResult();

        $recurrenceConstraint = new BetweenConstraint($date, $date, true);
        $recurrenceTransformer = new ArrayTransformer();

        $resultSet = [];

        foreach ($results as $result) {
            /** @var Appointment $result */
            $deletedDates = $result->getDeleted();

            // @TODO maybe this could be done in SQL?
            if ($deletedDates && in_array($date, $deletedDates)) {
                // skip deleted dates.
                continue;
            }

            if (!$result->getRecurrenceRule()) {
                $resultSet[] = $result;
                continue;
            }

            // parse out recurrence rules.
            $recurrenceRule = new Rule($result->getRecurrenceRule(), $result->getStart(), $result->getFinish());

            $instances = $recurrenceTransformer->transform($recurrenceRule, $recurrenceConstraint);

            if (count($instances) === 0) {
                continue;
            }

            $resultSet[] = $result;
        }

        return $resultSet;
    }
}
