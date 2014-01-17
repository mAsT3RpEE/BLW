<?php
class CronJob extends \BLW\Model\Cron\Job\Symfony {

    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Progress = $this->getHelperSet()->get('progress');

        $Progress->start($Output, 10);

        for ($i=0;$i<10;$i++) {
            $Progress->advance();
        }
    }
}