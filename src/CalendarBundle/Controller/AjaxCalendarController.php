<?php

namespace CalendarBundle\Controller;

use CalendarBundle\Repository\AppointmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        // @TODO inject me
        $em = $this->getDoctrine()->getManager();
        $appointmentRepository = $em->getRepository("CalendarBundle:Appointment");

        $days = [];

        // make firstWeekday in the range 1..7 by swapping sunday to be last
        // @TODO use options to determine whether to do this or not.
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
                "hasEvents" => count($appointmentRepository->findByDate(\DateTime::createFromFormat("Y-m-d", "$year-$month-$day"))) > 0,
            ];
        }

        return $this->render("CalendarBundle:Calendar:ajax/month-view.html.twig", [
            "days" => $days,
        ]);
    }

    /**
     * Day View.
     *
     * @param Request $request
     * @return Response
     */
    public function dayViewAction(Request $request): Response
    {
        $date = \DateTime::createFromFormat("Y-m-d", $request->get("date"));

        if (!$date) {
            throw new BadRequestHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $em->getRepository("CalendarBundle:Appointment");
        $results = $appointmentRepository->findByDate($date);

        // @TODO temp. use a serializer
        $data = [];

        foreach ($results as $result) {
            $data[] = [
                "id" => $result->getId(),
                "length" => $result->getLength(),
                "name" => stripslashes(str_replace('\n',"\n", $result->getText())),
                "start" => $result->getStartTime(),
            ];
        }

        return new JsonResponse([
            "count" => count($data),
            "data" => $data,
        ]);
    }
}
