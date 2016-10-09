<?php

namespace CalendarBundle\Command;

use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Reader\CalendarReader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportCalendarCommand
 * @package CalendarBundle\Command
 * @author Callum Jones <cj@icj.me>
 */
class ImportCalendarCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ImportCalendarCommand constructor.
     * @param null|string $name
     * @param EntityManagerInterface $entityManager
     */
    public function __construct($name = null, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName("calendar:import")
            ->setDescription("import an ical-tcl calendar")
            ->addArgument("filename", InputArgument::REQUIRED, "the filename (location) of the calendar")
        ;
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument("filename");

        $contents = file_get_contents($filename);

        $this->logger->info("Parsing calendar from " . $filename);

        $calendarReader = new CalendarReader(new ICalLexer($contents));
        $calendar = $calendarReader->read();

        $this->logger->info("Found an ical-tcl format with version " . $calendar->getVersion());
        $this->logger->info("Persisting calendar and appointments to database");

        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }
}
