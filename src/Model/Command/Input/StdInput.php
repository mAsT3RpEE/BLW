<?php
/**
 * StdInput.php | Mar 30, 2014
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
namespace BLW\Model\Command\Input;

use BLW\Type\Command\IArgument;
use BLW\Type\Command\IOption;
use BLW\Model\GenericContainer;
use BLW\Model\Stream\Handle;
use BLW\Model\Command\Argument\Generic as Argument;
use BLW\Model\Command\Option\Generic as Option;

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
 * Command input from argv and stdin.
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class StdInput extends \BLW\Type\Command\AInput
{

    /**
     * Constructor
     */
    public function __construct()
    {
        global $argv;

        // Set Properties
        $this->_Arguments   = new GenericContainer(IArgument::CLASSNAME);
        $this->_Options     = new GenericContainer(IOption::CLASSNAME);
        $this->_InStream    = new Handle(STDIN);

        // Arguments
        $this->_Arguments->exchangeArray(Argument::createFromArray($argv));

        // Options
        $this->_Options->exchangeArray(Option::createFromArray($argv));
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
