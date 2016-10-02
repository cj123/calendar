<?php

namespace CalendarBundle\Command;

use CalendarBundle\Formatting\ICal\Lexer\ICalLexer;
use CalendarBundle\Formatting\ICal\Reader\CalendarReader;
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

        $calendarReader = new CalendarReader(new ICalLexer($contents));
        $calendarReader->read();
    }
}
