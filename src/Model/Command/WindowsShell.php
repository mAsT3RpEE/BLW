<?php
/**
 * WindowsShell.php | Mar 31, 2014
 *
 * @filesource
 * @license MIT
 * @copyright Copyright (c) 2013-2018, mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @package BLW\Command
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Command;

use BLW\Type\IConfig;
use BLW\Type\IMediator;
use BLW\Type\Command\IInput;

// @codeCoverageIgnoreStart
if (! defined('BLW')) {

    if (strstr($_SERVER['PHP_SELF'], basename(__FILE__))) {
        header("$_SERVER[SERVER_PROTOCOL] 404 Not Found");
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;

        echo "<html>\r\n<head><title>404 Not Found</title></head><body bgcolor=\"white\">\r\n<center><h1>404 Not Found</h1></center>\r\n<hr>\r\n<center>nginx/1.5.9</center>\r\n</body>\r\n</html>\r\n";
        exit();
    }

    return false;
}
// @codeCoverageIgnoreEnd


/**
 * Command for handling shell commands.
 *
 * <h4>Note</h4>
 *
 * <p>Input / Output is done directly from process pipe to streams so
 * <code>onInput() / onOutput() / onError()</code> methods wont work.</p>
 *
 * <p>Use <code>$Config[Callback]</code> instead.</p>
 *
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class WindowsShell extends \BLW\Model\Command\Shell
{

#############################################################################################
# Factory Trait
#############################################################################################

    /**
     * Creates a command line string from $Comand and $Input.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Command
     *            Command to execute
     * @param IInput $Input
     *            Command input.
     * @return string Generated command line.
     */
    public static function createCommandLine($Command, IInput $Input)
    {
        static $Format = 'cmd /V:ON /E:ON /C "(%s %s)"';

        // Command
        $Command = strval($Command);

        // Add Options / Arguments
        $extras = array();

        foreach ($Input->Options as $v) {
            $extras[] = strval($v);
        }

        foreach ($Input->Arguments as $v) {
            $extras[] = strval($v);
        }

        // Build command line
        return sprintf($Format, $Command, implode(' ', $extras));
    }

#############################################################################################
# ShellCommand Trait
#############################################################################################

    /**
     * Constructor
     *
     * @param mixed $Command
     *            Command to run.
     * @param IConfig $Config
     *            Command configuration.
     * @param IMediator $Mediator
     *            Mediator for command.
     * @param string $ID
     *            Command Name / Label
     */
    public function __construct($Command, IConfig $Config, IMediator $Mediator = null, $ID = null)
    {
        // Parent constructor
        parent::__construct($Command, $Config, $Mediator, $ID);

        // Bypass CMD
        $Config['Extras']['bypass_shell'] = true;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
