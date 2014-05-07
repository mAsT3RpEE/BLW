<?php
/**
 * IMessage.php | Jan 20, 2013
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
 * Interface for MIME formated Message.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | MESSAGE                                           |<------| FACTORY          |
 * +---------------------------------------------------+       | ================ |
 * | _Head:  IHead                                     |       | createFromString |
 * | _Body:  IBody                                     |       +------------------+
 * +---------------------------------------------------+
 * | createFromString(): IMessage                      |
 * |                                                   |
 * | $String:  string                                  |
 * +---------------------------------------------------+
 * | getHeader(): _Head                                |
 * +---------------------------------------------------+
 * | getBody(): _Body                                  |
 * +---------------------------------------------------+
 * | __toString(): string                              |
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
 * @property \BLW\Type\MIME\IHead $_Head Mime head.
 * @property \BLW\Type\MIME\IBody $_Body Mime body.
 */
interface IMessage extends \BLW\Type\IFactory
{

    /**
     * Convert a string to a MimeMessage
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $String
     *            String of message.
     * @return \BLW\Type\MIME\IMessage Genereted message.
     */
    public static function createFromString($String);

    /**
     * Create a mime header from type and value.
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\MIME\IHeader IHeader.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Type</code> is empty or not a string.
     *
     * @param string $Type
     *            Header type.
     * @param string $Value
     *            Header value.
     * @return \BLW\Type\MIME\IHeader Genereted header.
     */
    public static function createHeader($Type, $Value);

    /**
     * Return the current mime header portion.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Model\MIME\IHead Current mime head.
     */
    public function getHeader();

    /**
     * Return the current mime body portion.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Model\MIME\IBody Current mime body.
     */
    public function getBody();

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
