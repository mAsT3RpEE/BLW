<?php
/**
 * IBody.php | Mar 20, 2014
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
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
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+      +------------------+       +-----------------+
 * | BODY                                              |<-----| CONTAINER        |<--+---| ArrayObject     |
 * +---------------------------------------------------+      | ================ |   |   +-----------------+
 * | HEADER                                            |      | HEADER           |   +---| SERIALIZABLE    |
 * | PART                                              |      | PART             |   |   | =============== |
 * +---------------------------------------------------+      | string           |   |   | Serializable    |
 * | _CRLF:      "\r\n"                                |      +------------------+   |   +-----------------+
 * | _Sections:  ISection[]                            |                             +---| ITERABLE        |
 * +---------------------------------------------------+                                 +-----------------+
 * | getSection() _Section(current)                    |
 * +---------------------------------------------------+
 * | addSection(): bool                                |
 * |                                                   |
 * | $Section:  ISection                               |
 * +---------------------------------------------------+
 * | endSection(): _Section                            |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string $_CRLF [protected] "\r\n"
 * @property array $_Sections [protected] Current body sections.
 */
interface IBody extends \BLW\Type\IContainer
{

    const HEADER = '\\BLW\\Type\\MIME\\IHeader';
    const PART   = '\\BLW\\Type\\MIME\\IPart';

    /**
     * Returns the current mime section.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return \BLW\Type\MIME\ISection $_Section[current]. Returns <code>null</code> on error.
     */
    public function getSection();

    /**
     * Adds a new Section to body.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\MIME\ISection $Section
     *            Section to add.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function addSection(ISection $Section);

    /**
     * Ends Section in body.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function endSection();

    /**
     * Adds a part the current mime body.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param \BLW\Type\MIME\IPart $Part
     *            MIME part (headers / body) to add.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function addPart(IPart $Part);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
