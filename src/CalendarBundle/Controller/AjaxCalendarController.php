<?php

namespace CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * AjaxCalendarController
 * @package CalendarBundle\Controller
 */
class AjaxCalendarController extends Controller
{
    /**
     * Month View.
     *
     * @param Request $request
     * @return Response
     */
    public function monthViewAction(Request $request): Response
    {
        $month = (int) $request->get("month");
        $year  = (int) $request->get("year");

        if (!$month || !$year) {
            throw new BadRequestHttpException();
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $firstDayOfMonth = \DateTime::createFromFormat("d/m/Y", sprintf("01/%02d/%d", $month, $year));
        $firstWeekday = (int) $firstDayOfMonth->format("w");

        $days = [];

        // make firstWeekday in the range 1..7
        if ($firstWeekday === 0) {
            $firstWeekday = 7;
        }

        // first, go through all the days and pop the padding in until the first day
        for ($day = 0; $day < $firstWeekday - 1; $day++) {
            $days[] = [];
        }

        // then put the actual days in
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $days[] = [
                "num" => $day,
            ];
        }

        return $this->render("CalendarBundle:Calendar:ajax/month-view.html.twig", [
            "days" => $days,
        ]);
    }
}
