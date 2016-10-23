<?php

namespace CalendarBundle\Repository;

use CalendarBundle\Entity\Appointment;
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
        $query = $this->getEntityManager()->createQuery(
            "
                SELECT a FROM CalendarBundle:Appointment a
                    WHERE
                        ((a.recurrenceRule != '' AND a.start < :now) OR (a.recurrenceRule = '' AND a.start = :now))


            "
        )->setParameter("now", $date->setTime(0, 0, 0), Type::DATETIME);

        $results = $query->getResult();

        $recurrenceConstraint = new BetweenConstraint($date, $date, true);

        $resultSet = [];

        foreach ($results as $result) {
            /** @var Appointment $result */
            if (!$result->getRecurrenceRule()) {
                $resultSet[] = $result;
                continue;
            }

            $rrule = new Rule($result->getRecurrenceRule(), $result->getStart(), $result->getFinish());
            $transformer = new ArrayTransformer();

            $instances = $transformer->transform($rrule, $recurrenceConstraint);

            if (count($instances) === 0) {
                continue;
            }

            $resultSet[] = $result;
        }

        return $resultSet;
    }
}
