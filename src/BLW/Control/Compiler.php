<?php
/**
 * APP.run.php | Dec 16, 2013
 *
 * Copyright (c) mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */

namespace BLW\Control;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

/**
 * @ignore
 */
class DownloadCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @ignore
     */
    protected function configure()
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
    }

    /**
     * @ignore
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Progress = $this->getHelperSet()->get('progress');
        $Url      = $Input->getArgument('url');
        $Temp     = getcwd() . '/temp/';
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

        if(basename($Url)) {

            if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
                $Output->writeln(sprintf('%s: Downloading %s', date("\rc"), $Url));
            }

            copy($Url, $Temp . basename($Url), $Context);
            $Progress->finish();
        }

        else {
            $Output->writeln(sprintf('%s: Invalid url %s', date("\rc"), $Url));
        }
    }
}

/**
 * @ignore
 */
class InstallCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @ignore
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Install library dependencies. (Call before compile)')
            ->addOption(
                'dev',
                NULL,
                InputOption::VALUE_NONE,
                'Build directory. (Default: ./build)'
            )
        ;
    }

    /**
     * @ignore
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Progress = $this->getHelperSet()->get('progress');
        $Type     = $Input->getOption('dev')
            ? '--dev'
            : '--no-dev'
        ;

        $Progress->start($Output, 2);

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln(sprintf('%s: Running composer.', date("\rc")));
            $Progress->display();
        }

        @exec(sprintf('composer install %s', $Type), $Result, $Status);

        $Progress->advance();

        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln("\r\n--------------------------------------------------");
            $Output->writeln($Result);
            $Output->writeln(sprintf('Exited with code: %d', $Status));
            $Output->writeln("\r--------------------------------------------------");
            $Progress->display();
        }

        unset($Result, $Status);
        $Progress->advance();
        $Progress->finish();
    }
}

/**
 * @ignore
 */
class UpdateCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @ignore
     */
    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Updates library dependencies.')
            ->addOption(
                'dev',
                NULL,
                InputOption::VALUE_NONE,
                'Build directory. (Default: ./build)'
            )
        ;
    }

    /**
     * @ignore
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Temp     = getcwd() . '/temp';
        $Progress = $this->getHelperSet()->get('progress');
        $Type     = $Input->getOption('dev')
            ? '--dev'
            : '--no-dev'
        ;

        $Progress->start($Output, 2);

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln(sprintf('%s: Running composer.', date("\rc")));
            $Progress->display();
        }

        @exec(sprintf('composer update %s', $Type), $Result, $Status);

        $Progress->advance();

        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln("\r\n--------------------------------------------------");
            $Output->writeln($Result);
            $Output->writeln(sprintf('Exited with code: %d', $Status));
            $Output->writeln("\r--------------------------------------------------");
            $Progress->display();
        }

        unset($Result, $Status);
        $Progress->advance();
        $Progress->finish();
    }
}

/**
 * @ignore
 */
class CompileCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @ignore
     */
    protected function configure()
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
    }

    /**
     * @ignore
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Progress = $this->getHelperSet()->get('progress');
        $Dir      = $Input->getArgument('dir');

        if (!$Dir) {
            $Dir = getcwd() . '/build';
        }

        $Progress->start($Output, 5);

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln(sprintf('%s: Optimizing autoloader.', date("\rc")));
            $Progress->display();
        }

        @exec(sprintf('composer dump-autoload -o', $Type), $Result, $Status);

        $Progress->advance();

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Output->writeln(sprintf('%s: Initializing compiler.', date("\rc")));
        }

        \BLW\Model\Compiler::Initialize();

        $Progress->advance();

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln(sprintf('%s: Creating compiler.', date("\rc")));
            $Progress->display();
        }

        $Compiler = \BLW\Model\Compiler::GetInstance();

        $Progress->advance();

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln(sprintf('%s: Configuring compiler.', date("\rc")));
            $Progress->display();
        }

        $Compiler
        ->phar('BLW.phar')
        ->out($Dir)
        ;

        $Progress->advance();

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln(sprintf('%s: Compiling...', date("\rc")));
            $Progress->display();
        }

        $Compiler->run();
        unset($Compiler);

        $Progress->advance();
        $Progress->finish();
    }
}

/**
 * @ignore
 */
class BuildCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @ignore
     */
    protected function configure()
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
    }

    /**
     * @ignore
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Dir    = $Input->getArgument('dir');
        $Status = 0;

        if (!$Dir) {
            $Dir = getcwd() . '/build';
        }

        $Actions = array(
            array('command' => 'update')
        	,array('command' => 'compile', 'dir' => $Dir)
            ,array('command' => 'test')
        );

        foreach ($Actions as $Action) {

            if($Status != 0) {
                $Output->writeln(sprintf('%s: Error Stopping.', date("\r\nc")));
            }

            $CMD    = $this->getApplication()->find($Action['command']);
            $Status = $CMD->run(new ArrayInput($Action), $Output);

            $Output->writeln('');
        }
    }
}

/**
 * @ignore
 */
class TestCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @ignore
     */
    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Test Installation / Compilation.')
        ;
    }

    /**
     * @ignore
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $Input, \Symfony\Component\Console\Output\OutputInterface $Output)
    {
        $Temp     = getcwd() . '/temp';
        $Progress = $this->getHelperSet()->get('progress');

        $Progress->start($Output, 2);

        if (OutputInterface::VERBOSITY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln(sprintf('%s: Running PHPUnit.', date("\rc")));
            $Progress->display();
        }

        @exec(sprintf('phpunit'), $Result, $Status);

        $Progress->advance();

        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $Output->getVerbosity()) {
            $Progress->clear();
            $Output->writeln("\r\n--------------------------------------------------");
            $Output->writeln($Result);
            $Output->writeln(sprintf('Exited with code: %d', $Status));
            $Output->writeln("\r--------------------------------------------------");
            $Progress->display();
        }

        unset($Result, $Status);
        $Progress->advance();
        $Progress->finish();
    }
}

/**
 * Console application that builds PHAR files and optimizez images / scripts / etc.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Compiler extends \BLW\Type\Adaptor
{
    /**
     * @var string $_Class Used by GetInstance to generate instance of class
     */
    protected static $_Class = '\\Symfony\\Component\\Console\\Application';

    /**
     * Sets up commands.
     * @return void
     */
    public function doCreate()
    {
        $this->setName('BLW Library');
        $this->setVersion('1.0');

        $this->add(new DownloadCommand);
        $this->add(new InstallCommand);
        $this->add(new UpdateCommand);
        $this->add(new CompileCommand);
        $this->add(new TestCommand);
        $this->add(new BuildCommand);
    }

    public function run($Timeout = 0)
    {
        if(is_callable('set_time_limit')) {
            set_time_limit($Timeout);
        }

        $this->doCreate();
        $this->GetSubject()->run();
    }
}