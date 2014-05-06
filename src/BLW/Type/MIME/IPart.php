<?php
/**
 * IPart.php | Mar 20, 2014
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
 * @package BLW\MIME
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\MIME;

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
 * Interface for MIME body part.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+      +-------------+       +-----------------+
 * | PART                                              |<-----| CONTAINER   |<--+---| ArrayObject     |
 * +---------------------------------------------------+      | =========== |   |   +-----------------+
 * | HEADER                                            |      | IHeader     |   +---| SERIALIZABLE    |
 * | CHUNKLEN                                          |      | String      |   |   | =============== |
 * +---------------------------------------------------+      +-------------+   |   | Serializable    |
 * | _CRLF:  "\r\n                                     |                        |   +-----------------+
 * +---------------------------------------------------+                        +---| ITERABLE        |
 * | format(): string                                  |                            +-----------------+
 * |                                                   |
 * | $Content:   string                                |
 * | $Chunklen:  int                                   |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string CHUNKLEN Length of body chunksize.
 * @property string $_CRLF [protected] "\r\n"
 */
interface IPart extends \BLW\Type\IContainer
{
    const HEADER = '\\BLW\\Type\\MIME\\IHeader';

    /**
     * Format a part body.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Content</code> is not a string.
     *
     * @param string $Content
     *            String to encode
     * @param int $Chunklen
     *            Maximum line length of formatted body
     * @return string Formated string. Returns `invalid` on error.
     */
    public static function format($Content, $Chunklen);
}

return true;
