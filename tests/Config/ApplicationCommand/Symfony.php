<?php
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ApplicationCommand extends \BLW\Type\ApplicationCommand\Symfony
{
    public function configure(array $Options = array())
    {
        $this
            ->setName('test')
            ->setDescription('Test command.')
            ->addArgument(
                'test-argument',
                InputArgument::OPTIONAL,
                'A test argument'
            )
            ->addOption(
                'test-option',
                NULL,
                InputOption::VALUE_NONE,
                'A test option'
            )
        ;
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Progress = $this->getHelperSet()->get('progress');
        $Argument = $Input->getArgument('test-argument');
        $Option   = $Input->getOption('test-option');

        $Progress->start($Output, 10);

        for ($i=0;$i<10;$i++) {
            $Progress->advance();
        }

        $Output->writeln(sprintf('Argument is: %s', print_r($Argument, true)));
        $Output->writeln(sprintf('Option is: %s', print_r($Option, true)));
    }
}