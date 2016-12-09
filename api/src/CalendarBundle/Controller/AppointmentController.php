<?php

namespace CalendarBundle\Controller;

use CalendarBundle\Entity\Appointment;
use CalendarBundle\Entity\Calendar;
use CalendarBundle\Form\CreateAppointmentType;
use CalendarBundle\Generator\UidGenerator;
use CalendarBundle\Repository\CalendarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AppointmentController
 * @package CalendarBundle\Controller
 */
final class AppointmentController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    private $entityManager;

    private $logger;
    private $calendarRepository;
    private $uidGenerator;

    public function __construct(
        LoggerInterface $logger,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        CalendarRepository $calendarRepository,
        UidGenerator $uidGenerator
    ) {
        $this->logger = $logger;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->calendarRepository = $calendarRepository;
        $this->uidGenerator = $uidGenerator;
    }

    /**
     * Create an Appointment
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createAction(Request $request): Response
    {
        $appointment = new Appointment();
        $form = $this->formFactory->create(CreateAppointmentType::class, $appointment);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $calendar = $this->calendarRepository->find($appointment->getCalendar());

                if ($calendar instanceof Calendar) {
                    $appointment->setCalendar($calendar);
                } else {
                    throw new \Exception("calendar with id: " . (int) $appointment->getCalendar() . " not found");
                }

                $appointment->setUid($this->uidGenerator->generate());
                $appointment->setUidPersistent(true);

                $this->entityManager->persist($appointment);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->logger->error("Could not persist appointment", [ $e->getMessage() ]);
                throw $e;
            }
        } else {
            throw new BadRequestHttpException();
        }

        return new Response();
    }
}
