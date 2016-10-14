<?php

namespace CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CalendarController
 * @package CalendarBundle\Controller
 */
class CalendarController extends Controller
{
    /**
     * Index Action
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $numDays = cal_days_in_month(CAL_GREGORIAN, 10, 2016);

        return $this->render("CalendarBundle:Calendar:index.html.twig", [
            "daysInMonth" => $numDays,
        ]);
    }
}
