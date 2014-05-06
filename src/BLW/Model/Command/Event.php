<?php
/**
 * Event.php | Mar 31, 2014
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
 * @package BLW\Core
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Command;

use BLW\Type\Command\ICommand;

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

/**
 * Generic command event.
 *
 * @package BLW\Core
 * @api BLW
 * @since 0.1.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Event extends \BLW\Type\AEvent
{

    /**
     * Constructor
     *
     * @see \BLW\Type\ICommand ICommand
     *
     * @param mixed $Subject
     *            The subject of the event, usually an object.
     * @param int $Type
     *            Type of command event:
	 *
     * <ul>
     * <li><b>GENERAL</b>: Gerenic notification.</li>
     * <li><b>RUN</b>: Triggered just before command run.</li>
     * <li><b>SHUTDOWN</b>: Triggered after command run.</li>
     * <li><b>ERROR</b>: Triggered on command error.</li>
     * <li><b>STDIN</b>: Triggered on command input write.</li>
     * <li><b>STDOUT</b>: Triggered on command output read.</li>
     * <li><b>STDERR</b>: Triggered on command error read.</li>
     * </ul>
	 *
     * @param array $Context
     *            Arguments to store in the event.
     */
    public function __construct($Subject = null, $Type = ICommand::GENERAL, array $Context = array())
    {
        // Properties
        $this->_Subject = $Subject;
        $this->_Context = array(
            'Type' => intval($Type)
        ) + $Context;
    }
}

return true;
