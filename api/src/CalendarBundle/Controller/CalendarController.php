<?php declare(strict_types=1);

namespace CalendarBundle\Controller;

use CalendarBundle\Defaults\OptionMap as DefaultOptionMap;
use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Reader\CalendarReader as ICalTclReader;
use CalendarBundle\Formatting\ICS\Reader\CalendarReader as ICSReader;
use ICal\ICal as ICalParser;
use CalendarBundle\Repository\AppointmentRepository;
use CalendarBundle\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DefaultOptionMap
     */
    private $defaultOptionMap;

    /**
     * CalendarController constructor.
     *
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param AppointmentRepository $appointmentRepository
     * @param NoteRepository $noteRepository
     * @param DefaultOptionMap $defaultOptionMap
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        AppointmentRepository $appointmentRepository,
        NoteRepository $noteRepository,
        DefaultOptionMap $defaultOptionMap
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
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
    public function optionsAction(): Response
    {
        // @TODO eventually this will be merged with user's settings
        $defaultOptionMap = $this->defaultOptionMap->getDefaults();

        return new JsonResponse($defaultOptionMap);
    }

    /**
     * Import a calendar given a type.
     *
     * @param Request $request
     * @return Response
     */
    public function importAction(Request $request): Response
    {
        if ($request->getMethod() === Request::METHOD_OPTIONS) {
            // an options request is made, just respond so it doesn't fail.
            return new Response();
        }

        $format = $request->get("format");

        if ($format !== "ical-tcl" && $format !== "ics") {
            throw new BadRequestHttpException("invalid format type");
        }

        try {
            foreach ($request->files as $file) {
                /** @var UploadedFile $file */
                $contents = file_get_contents($file->getRealPath());

                if ($format === "ical-tcl") {
                    $reader = new ICalTclReader(new ICalLexer($contents));
                } elseif ($format === "ics") {
                    $parser = new ICalParser();
                    $parser->initString($contents);
                    $reader = new ICSReader($parser);
                } else {
                    throw new BadRequestHttpException("invalid format type");
                }

                $calendar = $reader->read();

                $this->logger->info("Found an ${format} format with version " . $calendar->getVersion());
                $this->logger->info("Persisting calendar and appointments to database");

                $this->entityManager->persist($calendar);
                $this->entityManager->flush();
            }
        } catch (\Exception $e) {
            $this->logger->error("Import error: " . $e->getMessage());
            return new JsonResponse([ "success" => false ]);
        }

        return new JsonResponse([ "success" => true ]);
    }
}
