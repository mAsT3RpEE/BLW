<?php
/**
 * ContentTransferEncoding.php | Mar 08, 2014
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
namespace BLW\Model\MIME;

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
 * Header class for Content-Transfer-Encoding.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * encoding        := "Content-Transfer-Encoding" ":" mechanism
 *
 * mechanism       := "7bit" / "8bit" / "binary" /
 *                    "quoted-printable" / "base64" /
 *                    ietf-token / x-token
 *
 * ietf-token      := <An extension token defined by a standards-track
 *                     RFC and registered with IANA.>
 *
 * x-token         := <The two characters "X-" or "x-" followed, with
 *                     no intervening white space, by any token>
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentTransferEncoding extends \BLW\Type\MIME\AHeader
{
    // 7bit / 8bit / binary / quoted-printable / base64
    const MECHANISM = '(?:7bit|8bit|binary|quoted-printable|base64)';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Encoding</code> is not a string.
     *
     * @param string $Encoding
     *            Value of Content-Transfer-Encoding header.
     */
    public function __construct($Encoding)
    {
        // 1. Header type
        $this->_Type = 'Content-Transfer-Encoding';

        // 2. Header value

        // Validate $Encoding
        if (! is_string($Encoding) && ! is_callable(array(
            $Encoding,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } else {
            // Type
            $this->_Value = $this->parseEncoding($Encoding);
        }
    }

    /**
     * Parse a string for encoding.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `text/plain` in case of error.
     */
    public static function parseEncoding($Test)
    {
        // Type Regex
        $Mechanism = sprintf('(?:%s|%s)', self::MECHANISM, self::EXTENTION_TOKEN);

        // Match Regex `type/subtype`
        if (preg_match("!$Mechanism!", @strtolower($Test), $m)) {
            return $m[0];
        }

        // Default
        return 'binary';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
