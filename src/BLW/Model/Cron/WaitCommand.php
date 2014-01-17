<?php
/**
 * WaitCommand.php | Jan 07, 2014
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
 *	@package BLW\Cron
*	@version 1.0.0
*	@author Walter Otsyula <wotsyula@mast3rpee.tk>
*/
namespace BLW\Model\Cron; if(!defined('BLW')){trigger_error('Unsafe access of custom library',E_USER_WARNING);return;}

use BLW;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Wait for another job.
 * @package BLW\Cron
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class WaitCommand extends \BLW\Type\ApplicationCommand\Symfony
{
    /**
     * Configure the command.
     * @throws \BLW\Model\InvalidClassException If Period option is invalid
     * @param array $Options Configuration options
     * @return void
     */
    public function configure(array $Options = array())
    {
        $this
            ->setName('wait')
            ->setDescription('Wait for a cronjob to activate.')
        ;

        if (!isset($this->Options->Period)) {
            throw new \BLW\Model\InvalidClassException(0, '%header% Option `Period` does not exist.');
        }

        elseif (!$this->Options->Period instanceof \DateInterval) {
            throw new \BLW\Model\InvalidClassException(0, '%header% Option `Period` is invalid.');
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
        $Output->writeln(sprintf(
             'No cron jobs: waiting %s'
            ,$this->Options->Period->format($this->Options->Period->days. ' day(s), %H hour(s), %M min(s), %S sec(s)')
        ));
    }
}