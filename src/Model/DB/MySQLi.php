<?php
/**
 * MySQLi.php | May 4, 2014
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
 * @package BLW\DB
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\DB;

use BLW\Model\InvalidArgumentException;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Adapter\Profiler\ProfilerInterface;
use Zend\Db\Adapter\Driver\Mysqli\Connection;
use Zend\Db\Adapter\Driver\Mysqli\Mysqli as Driver;

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
 * Database that recieves a mysqli resource as its connection.
 *
 * @package BLW\DB
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class MySQLi extends \Zend\Db\Adapter\Adapter implements \BLW\Type\DB\IDataBase
{
    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If an invalid argument is passed.
     *
     * @param resource $Connection
     *        PDO Object already connected to DB.
     * @param \Zend\Db\Adapter\Platform\PlatformInterface $platform
     *        [optional] Adaptor Platform (Mysql, Oracle, Postgre, Sqlite, etc).
     * @param \Zend\Db\ResultSet\ResultSetInterface $queryResultPrototype
     *        [optional] Result set class. <code>NULL</code> for default.
     * @param \Zend\Db\Adapter\Profiler\ProfilerInterface $profiler
     *        [optional] Adapter profiler.
     */
    public function __construct($Connection, PlatformInterface $platform = null, ResultSetInterface $queryResultPrototype = null, ProfilerInterface $profiler = null)
    {
        // Check connection
        if (! is_resource($Connection) ?: strpos(get_resource_type($Connection), 'mysql') === false) {
            // Exception
            throw new InvalidArgumentException(0, '%header% Argument 1 should be a myqli connection');

        } else {

            // Create driver
            $Driver = new Driver(new Connection($Connection));

            // Call parent
            try {
                parent::__construct($Driver, $platform, $queryResultPrototype, $profiler);

            // Invalid argument?
            } catch (\Zend\Db\Exception\InvalidArgumentException $e) {
                // Forward exception
                throw new InvalidArgumentException(-1, '%header% ' . $e->getMessage(), $e->getCode());
            }
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
