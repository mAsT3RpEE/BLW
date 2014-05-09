<?php
/**
 * ISection.php | Mar 21, 2014
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
 * Interface for Section class which helps create and organize mime parts / mime boundaries.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+      +----------------+       +-----------------+
 * | SECTION                                           |<-----| CONTAINER      |<--+---| ArrayObject     |
 * +---------------------------------------------------+      | ============== |   |   +-----------------+
 * | _CRLF:      "\r\n"                                |      | HEADER         |   +---| SERIALIZABLE    |
 * | _Type:      string                                |      +----------------+   |   | =============== |
 * | _Boundary:  string                                |      | FACTORY        |   |   | Serializable    |
 * +---------------------------------------------------+      | ============== |   |   +-----------------+
 * | createStart(): IHeader                            |      | createStart    |   +---| ITERABLE        |
 * +---------------------------------------------------+      | createBoundary |       +-----------------+
 * | createBoundary(): IHeader                         |      | createEnd      |
 * +---------------------------------------------------+      +----------------+
 * | createEnd(): IHeader                              |
 * +---------------------------------------------------+
 * | buildBoundary(): string                           |
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
 * @property string $_CRLF [protected] "\r\n"
 * @property string $_Type Content type.
 * @property string $_Boundary MIME boundary.
 */
interface ISection extends \BLW\Type\IContainer, \BLW\Type\IFactory
{

    /**
     * Create start of section header.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Model\MIME\ContentType MIME header.
     */
    public function createStart();

    /**
     * Create mime boundary header.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Model\MIME\Boundary MIME header.
     */
    public function createBoundary();

    /**
     * Create mime boundary header.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Model\MIME\Boundary MIME header.
     */
    public function createEnd();

    /**
     * Generate a random mime boundary.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string Boundary string.
     */
    public static function buildBoundary();

    /**
     * Retrieve current section boundary.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string $_Boundary
     */
    public function getBoundary();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
