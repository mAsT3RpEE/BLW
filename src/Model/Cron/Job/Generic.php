<?php
/**
 * Generic.php | Apr 8, 2014
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Cron\Job;

use DateTime;
use DateInterval;
use BLW\Type\IDataMapper;
use BLW\Type\Command\ICommand;
use BLW\Type\Cron\AJob;
use BLW\Model\InvalidArgumentException;

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
 * Generic Cron job class
 *
 * @package BLW\Cron
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Generic extends \BLW\Type\Cron\AJob
{

    /**
     * Constructor
     *
     * @see \BLW\Type\AWrapper::__construct() AWrapper::__construct()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Interval</code> is invalid.
     *
     * @param \BLW\Type\Command\ICommand $Command
     *            Command to run.
     * @param \DateInterval $Interval
     *            Interval between successive runs.
     */
    public function __construct(ICommand $Command, DateInterval $Interval)
    {
        // Properties
        $this->_Component = $Command;
        $this->_LastRun   = new DateTime('yesterday');
        $this->_NextRun   = new DateTime('today');

        // Interval
        if ($this->setInterval($Interval) != IDataMapper::UPDATED) {
            throw new InvalidArgumentException(1);
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
