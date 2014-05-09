#!/usr/bin/env php
<?php
/**
 * test.php | Jan 07, 2014
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

use BLW\Model\Config\Generic as Config;
use BLW\Model\Command\Shell as ShellCommand;
use BLW\Model\Command\Input\Generic as Input;
use BLW\Model\Command\Option\Generic as Option;
use BLW\Model\Stream\Handle;

Application::configure();

Application::run(function (BLW\Type\Command\IInput $Input, BLW\Type\Command\IOutput $Output, BLW\Type\Command\ICommand $Command)
{
    $Print = function($Message) use(&$Output, &$Command)
    {
        $Output->write("$Message\r\n");
        $Command->Config['Logger']->debug($Message);
    };

    // #####################
    // TEST LIBRARY
    // #####################

    $Print('Testing BLW Library...');

    // Run framework tests
    $ShellInput            = new Input(new Handle(fopen('data:text/plain,', 'r')));
    $ShellInput->Options[] = new Option('testsuite', 'Types');
    $ShellInput->Options[] = new Option('coverage-php', 'temp/coverage-types.serialized');
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
    $ShellInput->Options[] = new Option('coverage-php', 'temp/coverage-models.serialized');

    $PHPUnit->run($ShellInput, $Output);

    // Merge coverage files
    // ...

    $Print('Finished testing.');

    // Done
    return 0;
});

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
