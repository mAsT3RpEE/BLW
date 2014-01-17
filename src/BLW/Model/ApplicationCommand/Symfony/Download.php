<?php
/**
 * Download.php | Jan 07, 2014
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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

/**
 * Downloads a file.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Download extends \BLW\Type\ApplicationCommand\Symfony
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
            ->setName('download')
            ->setDescription('Download a file into temp dir.')
            ->addArgument(
                    'url',
                    InputArgument::REQUIRED,
                    'Url to download.'
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
        $Progress = $this->getHelperSet()->get('progress');
        $Url      = $Input->getArgument('url');
        $Temp     = sys_get_temp_dir();
        $Context  = stream_context_create(
            array('http' => array(
                     'method'       => 'GET',
                     'header'       => array('Accept-language: en'),
                     'user_agent'   => $_SERVER['HTTP_USER_AGENT']
            )),
            array('notification' => function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) use ($Output, $Progress) {

                switch ($notification_code)
                {
                    case STREAM_NOTIFY_FILE_SIZE_IS:
                        $Progress->start($Output, $bytes_max);
                        $Progress->setRedrawFrequency(1024);
                        break;
                    case STREAM_NOTIFY_PROGRESS:
                        try {
                            $Progress->setCurrent($bytes_transferred);
                        }

                        catch (\LogicException $e) {
                            $Progress->start($Output);
                            $Progress->setRedrawFrequency(1024);
                            $Progress->setCurrent($bytes_transferred);
                        }

                        break;
                }
            }
        ));

        if($File = basename($Url)) {

            $Message = sprintf('Downloading %s', $Url);

            $this->Options->Logger->debug($Message);

            if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
                $Output->writeln($Message);
            }

            copy($Url, $Temp . $File, $Context);
            rename($Temp . $File, $File);

            $Progress->finish();
        }

        else {
            $Message = sprintf('Invalid url %s', $Url);

            $this->Options->Logger($Message);

            $Output->writeln($Message);
        }
    }
}