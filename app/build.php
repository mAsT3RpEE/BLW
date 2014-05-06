#!/usr/bin/env php
<?php
/**
 * build.php | Jan 07, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__DIR__) . '/src/bootstrap.php';
include __DIR__ . '/Common/Application.php';

use Common\Application;

use BLW\Model\Compiler;
use BLW\Model\GenericFile;
use BLW\Model\Archiver;
use BLW\Model\Config\Generic as Config;
use BLW\Model\Stream\Handle;
use BLW\Model\Command\Shell as ShellCommand;
use BLW\Model\Command\Input\Generic as Input;
use BLW\Model\Command\Option\Generic as Option;


Application::configure();

Application::run(function (BLW\Type\Command\IInput $Input, BLW\Type\Command\IOutput $Output, BLW\Type\Command\ICommand $Command)
{
    $Print = function($Message) use(&$Output, &$Command)
    {
        $Output->write("$Message\r\n");
        $Command['Logger']->debug($Message);
    };

    $Empty = function ($Dir) use (&$Empty)
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Dir), RecursiveIteratorIterator::CHILD_FIRST) as $path) {

            if ($path->isDir() && $path->getBasename() != '.' && $path->getBasename() != '..')
                $Empty($path->getPathname());

            elseif ($path->isFile())
                unlink($path->getPathname());
        }

        usleep(100000);

        rmdir($Dir);
    };

    $Copy = function ($Src, $Dest) use(&$Copy)
    {
        $Src  = rtrim($Src,  DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $Dest = rtrim($Dest, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Src, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {

            if ($path->isDir())
                continue;

            elseif ($path->isFile()) {

                if (!is_dir($Dir = str_replace($Src, $Dest, $path->getPath())))
                    mkdir($Dir);

                copy($path->getPathname(), str_replace($Src, $Dest, $path->getPathname()));
            }
        }
    };

    // #####################
    // RUN COMPOSER
    // #####################

    $Print('Running Composer...');

    $ShellInput            = new Input(new Handle(fopen('data:text/plain,', 'r')));
    $ShellInput->Options[] = new Option('n', true);
    $ShellInput->Options[] = new Option($Input->getOption('dev') ? 'dev' : 'no-dev', true);
    $PHPUnit               = new ShellCommand('composer install', new Config(array(
        'Timeout'       => 60,
        'CWD'           => dirname(__DIR__),
        'Environment'   => null,
        'Extras'        => array(),
    )), $Command->getMediator(), $Command->getMediatorID());

    // Check results
    if ($code = $PHPUnit->run($ShellInput, $Output))
        return $code;

    $ShellInput  = new Input(new Handle(fopen('data:text/plain,', 'r')));
    $Composer    = new ShellCommand('composer dumpautoload -o', new Config(array(
        'Timeout'       => 60,
        'CWD'           => dirname(__DIR__),
        'Environment'   => null,
        'Extras'        => array(),
    )), $Command->getMediator(), $Command->getMediatorID());

    $Output->write("\r\n");

    // #####################
    // COMPILE APPLICATION
    // #####################

    $Print('Compiling application...');

    @unlink(BLW_DIR . 'build' . DIRECTORY_SEPARATOR . 'BLW.phar');
    @unlink(BLW_DIR . 'build' . DIRECTORY_SEPARATOR . 'BLW.tar.gz');

    $Output->write("-Collecting files\r\n");
    $Output->write('[--------------------------------------------------]');

    // Create PHAR
    $Compiler = new Compiler(
        new GenericFile(BLW_DIR . 'build'),
        new GenericFile(BLW_DIR),
        new GenericFile(BLW_DIR . 'temp'),
        $Command->getMediator()
    );

    // Collect files
    $Compiler->addDir(new GenericFile(BLW_DIR . 'src'), 'php*', 'js', 'css');

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/composer')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/autoload.php')));

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/jeremeamia/SuperClosure/src')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR .str_replace('/', DIRECTORY_SEPARATOR,  'vendor/jeremeamia/SuperClosure/LICENSE.md')));

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/monolog/monolog/src/Monolog')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/monolog/monolog/LICENSE')));

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/mrclay/minify/min/lib')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/mrclay/minify/LICENSE.txt')));

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/nikic/php-parser/lib')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/nikic/php-parser/LICENSE')));

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/nikic/php-parser/lib')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/nikic/php-parser/LICENSE')));

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/psr/log/Psr')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/psr/log/LICENSE')));

    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/symfony')), 'php');
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/symfony/yaml/Symfony/Component/Yaml/LICENSE')));
    $Compiler->addFile(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/symfony/css-selector/Symfony/Component/CssSelector/LICENSE')));

    // Thank you zend. Yours is truly the best library
    $Compiler->addDir(new GenericFile(BLW_DIR . str_replace('/', DIRECTORY_SEPARATOR, 'vendor/zendframework')), 'php');

    $Output->overwrite('[=-------------------------------------------------]');

    // Compile files
    $Compiler->onAdvance(function($Event) use (&$Output) {
    	static $Total   = 0;
    	static $Percent = 1;

    	// Add count
    	$Total += $Event->Steps;

    	// Check count
    	if ($Total > 100) {

    	    // Reset
    	    $Total = $Total % 100;

    	    // Advance
    	    $Percent++;

    	    $Output->overwrite(substr_replace('[--------------------------------------------------]', str_repeat('=', $Percent), 1, $Percent));
    	}
    });

    $Output->overwrite('-Packaging files');
    $Output->write("\r\n[=-------------------------------------------------]");

    $Compiler->compile('BLW');

    unset($Compiler);

    $Output->overwrite('-Creating archive');
    $Output->write("\r\n[========================================----------]");

    // Copy assets
    $TempBuild = BLW_DIR . 'temp' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR;

    @mkdir($TempBuild);

    copy(BLW_DIR . 'build/BLW.phar', $TempBuild . 'BLW.phar');
    copy(BLW_DIR . 'LICENSE.md', $TempBuild . 'LICENSE.md');

    $Output->overwrite("[=========================================---------]");

    // Create Archive
    $Archiver = new Archiver(
        new GenericFile(BLW_DIR . 'build'),
        new GenericFile($TempBuild),
        new GenericFile(BLW_DIR . 'temp'),
        $Command->getMediator()
    );

    $Archiver->addDir(new GenericFile($TempBuild), '*');
    $Archiver->onAdvance(function($Event) use (&$Output) {
    	static $Total   = 0;
    	static $Percent = 0;

    	// Add count
    	$Total += $Event->Steps;

    	// Check count
    	if ($Total > 100) {

    	    // Reset
    	    $Total = $Total % 100;

    	    // Advance
    	    $Percent++;

    	    $Output->overwrite(substr_replace('[=========================================---------]', str_repeat('=', $Percent), 41, $Percent));
    	}
    });
    $Archiver->compile('BLW');

    unset($Archiver);

    // Cleanup
    $Empty($TempBuild);

    $Output->overwrite("[==================================================]\r\n");

    // #####################
    // TEST LIBRARY
    // #####################

    $Print('Testing BLW Library...');

    // Run framework tests
    $ShellInput            = new Input(new Handle(fopen('data:text/plain,', 'r')));
    $ShellInput->Options[] = new Option('testsuite', 'Types');
    $PHPUnit               = new ShellCommand('phpunit', new Config(array(
    	'Timeout'       => 60,
        'CWD'           => dirname(__DIR__),
        'Environment'   => null,
        'Extras'        => array(),
    )), $Command->getMediator(), $Command->getMediatorID());

    // Check results
    if ($code = $PHPUnit->run($ShellInput, $Output))
        return $code;

    // Run library tests
    $ShellInput            = new Input(new Handle(fopen('data:text/plain,', 'r')));
    $ShellInput->Options[] = new Option('testsuite', 'Models');

    $PHPUnit->run($ShellInput, $Output);

    // Done
    $Print('Finished.');

    return 0;
});

return true;
