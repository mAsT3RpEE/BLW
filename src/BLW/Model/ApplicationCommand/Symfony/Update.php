<?php
/**
 * Update.php | Jan 07, 2014
*
* Copyright (c) 2013-2018 mAsT3RpEE's Zone
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*
* @filesource
* @copyright mAsT3RpEE's Zone
* @license MIT
*/

/**
 *	@package BLW\Core
*	@version 1.0.0
*	@author Walter Otsyula <wotsyula@mast3rpee.tk>
*/
namespace BLW\Model\ApplicationCommand\Symfony; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use BLW;
use BLW\Model\ShellCommand\Symfony as ShellCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

/**
 * Updates a BLW installation.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Update extends \BLW\Type\ApplicationCommand\Symfony
{
    /**
     * Configure the command.
     * @throws \BLW\Model\InvalidClassException If Logger option is invalid
     * @param array $Options Configuration options
     * @return void
     */
    public function configure(array $Options = array())
    {
        $this
            ->setName('update')
            ->setDescription('Updates library dependencies.')
            ->addOption(
                'dev',
                NULL,
                InputOption::VALUE_NONE,
                'Development mode'
            )
        ;

        if (!isset($this->Options->Logger)) {
            throw new \BLW\Model\InvalidClassException(0, '%header% Option `Logger` does not exist.');
        }

        elseif (!$this->Options->Logger instanceof \BLW\Interfaces\Logger) {
            throw new \BLW\Model\InvalidClassException(0, '%header% Option `Logger` is invalid.');
        }
    }

    /**
     * Executes the current command.
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|integer null or 0 if everything went fine, or an error code
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $this->Options->Logger->info('Updating Install');

        // SETUP
        $Progress = $this->getHelperSet()->get('progress');
        $Dev      = $Input->getOption('dev')
            ? '--dev'
            : '--no-dev'
        ;

        $Progress->setRedrawFrequency(1);
        $Progress->start($Output, 100);

        // RUN COMPOSER
        if ($Output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $Progress->clear();
            $Output->writeln("\rRunning composer");
            $Progress->display();
        }

        $Command = ShellCommand::GetInstance(
            'composer'
            .ShellCommand::Argument('update')
            .ShellCommand::Option($Dev)
            .ShellCommand::MERGE_OUTPUT
        )
            ->SetTimeout(60)
        ;

        try {
            $Command->Run(function($Type, $Output) use ($Progress) {
                $Progress->advance();
            });
        }

        catch(ProcessTimedOutException $e) {
            $Progress->advance(10);
            $Progress->finish();

            $Message = 'Command timed out';

            $this->Options->Logger->info($Message);

            $Output->writeln("\r".$Message);

            return;
        }

        $Progress->setCurrent(90, true);

        // DISPLAY OUTPUT
        if ($Output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $Progress->clear();
            $Output->writeln("\r\n--------------------------------------------------");
            $Output->writeln($Command->GetOutput());
            $Output->writeln(sprintf('Exited with code: %d', $Command->ExitStatus()));
            $Output->writeln("\r--------------------------------------------------");
            $Progress->display();
        }

        $Progress->advance(10);
        $Progress->finish();

        $this->Options->Logger->info('Updated Install. Exit code: '.$Command->ExitStatus());

        unset($Command);
    }
}