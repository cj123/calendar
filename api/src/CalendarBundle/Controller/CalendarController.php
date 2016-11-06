<?php

namespace CalendarBundle\Controller;

use CalendarBundle\Defaults\OptionMap as DefaultOptionMap;
use CalendarBundle\Repository\AppointmentRepository;
use CalendarBundle\Repository\NoteRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * CalendarController
 * @package CalendarBundle\Controller
 * @author Callum Jones <cj@icj.me>
 */
class CalendarController
{
    /**
     * @var AppointmentRepository
     */
    private $appointmentRepository;

    /**
     * @var NoteRepository
     */
    private $noteRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var DefaultOptionMap
     */
    private $defaultOptionMap;

    /**
     * CalendarController constructor.
     *
     * @param SerializerInterface $serializer
     * @param AppointmentRepository $appointmentRepository
     * @param NoteRepository $noteRepository
     * @param DefaultOptionMap $defaultOptionMap
     */
    public function __construct(
        SerializerInterface $serializer,
        AppointmentRepository $appointmentRepository,
        NoteRepository $noteRepository,
        DefaultOptionMap $defaultOptionMap
    ) {
        $this->serializer = $serializer;
        $this->appointmentRepository = $appointmentRepository;
        $this->noteRepository = $noteRepository;
        $this->defaultOptionMap = $defaultOptionMap;
    }

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

        // make firstWeekday in the range 1..7 by swapping sunday to be last
        // @TODO use options to determine whether to do this or not.
        if ($firstWeekday === 0) {
            $firstWeekday = 7;
        }

        // first, go through all the days and pop the padding in until the first day
        for ($day = 0; $day < $firstWeekday; $day++) {
            $days[] = [];
        }

        // then put the actual days in
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $strDate = \DateTime::createFromFormat("Y-m-d", "$year-$month-$day");
            $events = $this->appointmentRepository->findByDate($strDate);

            $days[] = [
                "num" => $day,
                "hasEvents" => count($events) > 0,
            ];
        }

        return new JsonResponse($days);
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

        $results = $this->appointmentRepository->findByDate($date);

        return new Response($this->serializer->serialize($results, "json"), 200, [
            "Content-Type" => "application/json",
        ]);
    }

    /**
     * Get notes for a given date
     *
     * @param Request $request
     * @return Response
     */
    public function notesAction(Request $request): Response
    {
        $date = \DateTime::createFromFormat("Y-m-d", $request->get("date"));

        if (!$date) {
            throw new BadRequestHttpException();
        }

        $results = $this->noteRepository->findByDate($date);

        return new Response($this->serializer->serialize($results, "json"), 200, [
            "Content-Type" => "application/json",
        ]);
    }

    /**
     * Returns user defined options
     *
     * @return Response
     */
    public function optionsAction()
    {
        // @TODO eventually this will be merged with user's settings
        $defaultOptionMap = $this->defaultOptionMap->getDefaults();

        return new JsonResponse($defaultOptionMap);
    }
}
