<?php
/**
 * Generic.php | Mar 30, 2014
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
namespace BLW\Model\Command\Output;

use BLW\Type\IStream;

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
 * Generic command output.
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Generic extends \BLW\Type\Command\AOutput
{

    /**
     * Constructor
     *
     * @param \BLW\Type\IStream $stdOut
     *              Output stream of command.
     * @param \BLW\Type\IStream $stdErr
     *              Error stream of command.
     */
    public function __construct(IStream $stdOut, IStream $stdErr)
    {
        // Set Properties
        $this->_OutStream = $stdOut;
        $this->_ErrStream = $stdErr;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
