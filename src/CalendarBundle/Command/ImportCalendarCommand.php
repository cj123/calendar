<?php declare(strict_types=1);

namespace CalendarBundle\Command;

use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Reader\CalendarReader as ICalTclReader;
use CalendarBundle\Formatting\ICS\Reader\CalendarReader as ICSReader;
use Doctrine\ORM\EntityManagerInterface;
use ICal\ICal as ICalParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
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
     * @param LoggerInterface $logger
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
            ->addArgument("type", InputArgument::REQUIRED, "the type of calendar (ics/ical-tcl)")
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
        $calType  = $input->getArgument("type");

        $contents = file_get_contents($filename);

        $this->logger->info("Parsing calendar from " . $filename);

        if ($calType === "ics") {
            $parser = new ICalParser();
            $parser->initString($contents);
            $calendarReader = new ICSReader($parser);
        } elseif ($calType === "ical-tcl"){
            $calendarReader = new ICalTclReader(new ICalLexer($contents));
        } else {
            throw new InvalidArgumentException("invalid calendar type");
        }

        $calendar = $calendarReader->read();

        $this->logger->info("Found an ical-tcl format with version " . $calendar->getVersion());
        $this->logger->info("Persisting calendar and appointments to database");

        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }
}
