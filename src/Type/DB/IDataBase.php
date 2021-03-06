<?php
/**
 * IDataBase.php | May 4, 2014
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
namespace BLW\Type\DB;


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
 * Interface for all Databases for use in RDBMS.
 *
 * @package BLW\DB
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @link http://framework.zend.com/manual/2.3/en/modules/zend.db.adapter.html Zend\Db
 * @link https://github.com/zendframework/zf2/tree/master/library/Zend/Db Original
 */
interface IDataBase extends \Zend\Db\Adapter\AdapterInterface, \Zend\Db\Adapter\Profiler\ProfilerAwareInterface
{

}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
