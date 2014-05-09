<?php
/**
 * APlugin.php | Apr 16, 2014
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
 * @package BLW\HTTP
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\HTTP\Browser;

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
 * Base class for HTTP Browser Plugin objects
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------------+       +--------------+
 * | BROWSER\PLUGIN                                    |<------| OBJECT            |<--+---| SERIALIZABLE |
 * +---------------------------------------------------+       +-------------------+   |   | ============ |
 *                                                             | EVENTSUBSCRIBER   |   |   | Serializable |
 *                                                             +-------------------+   |   +--------------+
 *                                                                                     +---| DATAMAPABLE  |
 *                                                                                     |   +--------------+
 *                                                                                     +---| ITERABLE     |
 *                                                                                         +--------------+
 * </pre>
 *
 *
 * @package BLW\HTTP
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
abstract class APlugin extends \BLW\Type\AObject implements \BLW\Type\HTTP\Browser\IPlugin
{
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
