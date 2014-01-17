<?php
/**
 * Build.php | Jan 07, 2014
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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Compiles and tests a BLW Installation.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Build extends \BLW\Type\ApplicationCommand\Symfony
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
            ->setName('build')
            ->setDescription('Compile and Test library.')
            ->addArgument(
                'dir',
                InputArgument::OPTIONAL,
                'Build directory. (Default: ./build)'
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
        $Dir      = $Input->getArgument('dir') ?: $Dir = getcwd() . DIRECTORY_SEPARATOR . 'build';
        $Start    = microtime(true);
        $Status   = 0;
        $Actions  = array(
             array('command' => 'update')
        	,array('command' => 'compile', 'dir' => $Dir)
            ,array('command' => 'test')
        );

        $this->Options->Logger->info('Building Library');

        foreach ($Actions as $Action) {

            if($Status != 0) {
                $Message = 'Error Stopping.';

                $this->Options->Logger->warning($Message);
                $Output->writeln("\r".$Message);

                break;
            }

            $CMD    = $this->getApplication()->find($Action['command']);
            $Status = $CMD->run(new ArrayInput($Action), $Output);

            $Output->writeln("\r");
        }

        $this->Options->Logger->info(sprintf('Finished building. Total time: %f seconds', microtime(true) - $Start));
    }
}