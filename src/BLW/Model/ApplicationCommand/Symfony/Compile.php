<?php
/**
 * Compile.php | Jan 07, 2014
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
use BLW\Model\Object;
use BLW\Model\ShellCommand\Symfony as ShellCommand;
use BLW\Model\Compiler;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Compiles a BLW installation into PHAR files.
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Compile extends \BLW\Type\ApplicationCommand\Symfony
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
            ->setName('compile')
            ->setDescription('Compile library into build directory and create .tar.gz')
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
        // SETUP
        $Progress = $this->getHelperSet()->get('progress');
        $Dir      = $Input->getArgument('dir') ?: $Dir = getcwd() . DIRECTORY_SEPARATOR . 'build';
        $Start    = microtime(true);

        $this->Options->Logger->info('Compiling Library');
        $Progress->setRedrawFrequency(100);
        $Progress->start($Output, 1000);

        // OPTIMIZE AUTOLOADER
        $Message = 'Optimizing autoloader.';

        $this->Options->Logger->info($Message);

        if ($Output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $Progress->clear();
            $Output->writeln("\r".$Message);
            $Progress->display();
        }

        $Command = ShellCommand::GetInstance(
            'composer'
            .ShellCommand::Argument('dump-autoload')
            .ShellCommand::Option('-o')
            .ShellCommand::MERGE_OUTPUT
        );

        $Command->Run(function($Type, $Output) use ($Progress) {
            $Progress->advance();
        });

        $Progress->setCurrent(100, true);

        unset($Command);

        // INITIALIZE COMPILER
        $Message = 'Initializing compiler.';

        $this->Options->Logger->info($Message);

        if ($Output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $Progress->clear();
            $Output->writeln("\r".$Message);
            $Progress->display();
        }

        Compiler::Initialize();

        $Progress->advance(50);

        $Compiler = Compiler::GetInstance()
            ->phar(BLW_APPLICATION . '.phar')
            ->out($Dir)
        ;

        $Progress->advance(50);

        // COMPILE LIBRARY
        $Message = 'Compiling...';

        $this->Options->Logger->info($Message);

        if ($Output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $Progress->clear();
            $Output->writeln("\r".$Message);
            $Progress->display();
        }

        $Compiler->onAdvance(function(\BLW\Interfaces\Event $Event) use ($Progress) {
            $Progress->advance($Event->Steps);
        });

        $Compiler->run();

        unset($Compiler);

        $Progress->setCurrent(1000, true);
        $Progress->finish();

        $this->Options->Logger->info(sprintf('Finished compiling. Total time: %f seconds', microtime(true) - $Start));
    }
}