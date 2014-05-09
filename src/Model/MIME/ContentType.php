<?php
/**
 * ContentType.php | Mar 08, 2014
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
 * Header class for Content-Type.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * content          := "Content-Type" ":" type "/" subtype
 *                     *(";" parameter)
 *                     ; Matching of media type and subtype
 *                     ; is ALWAYS case-insensitive.
 *
 * type             := discrete-type / composite-type
 *
 * subtype          := extension-token / iana-token
 *
 * parameter        := attribute "=" value
 *
 * discrete-type    := "text" / "image" / "audio" / "video" /
 *                     "application" / extension-token
 *
 * composite-type   := "message" / "multipart" / extension-token
 *
 * extension-token  := ietf-token / x-token
 *
 * iana-token       := <A publicly-defined extension token. Tokens
 *                      of this form must be registered with IANA
 *                      as specified in RFC 2048.>
 *
 * http://www.mhonarc.org/~ehood/MIME/2048/rfc2048.html
 *
 * ietf-token       := <An extension token defined by a
 *                      standards-track RFC and registered
 *                      with IANA.>
 *
 * x-token          := <The two characters "X-" or "x-" followed, with
 *                      no intervening white space, by any token>
 *
 * attribute        := token
 *                     ; Matching of attributes
 *                     ; is ALWAYS case-insensitive.
 *
 * value            := token / quoted-string
 *
 * token            := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *                        or tspecials>
 *
 * tspecials        := "(" / ")" / "<" / ">" / "@" /
 *                     "," / ";" / ":" / "\" / <">
 *                     "/" / "[" / "]" / "?" / "="
 *                     ; Must be in quoted-string,
 *                     ; to use within parameter values
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentType extends \BLW\Type\MIME\AHeader
{
    // image / audio / video / application
    const DISCRETE_TYPE = '(?:text|image|audio|video|application)';

    // message / multipart
    const COMPOSITE_TYPE = '(?:message|multipart)';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Type</code> is not a string.
     *
     * @param string $Type
     *            MIME Content-Type.
     * @param array $Params
     *            Array of parameters with atribute as key and value as value
     */
    public function __construct($Type, array $Params = array())
    {
        // 1. Header type
        $this->_Type = 'Content-Type';

        // 2. Header value

        // Validate $Type
        if (! is_string($Type) && ! is_callable(array(
            $Type,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } else {

            // Type
            $this->_Value = $this->parseType($Type);

            // Parameters
            foreach ($Params as $Attribute => $Value) {

                // Add parameter to value
                try {
                    $this->_Value .= $this->parseParameter($Attribute, $Value);

                // Skip errors
                } catch (InvalidArgumentException $e) {
                    trigger_error(sprintf('Invalid parameter attribute (%s) or value (%s)', $Attribute, $Value), E_USER_NOTICE);
                }
            }
        }
    }

    /**
     * Parse a string for content-type.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `text/plain` in case of error.
     */
    public static function parseType($Test)
    {
        // Type Regex
        $Type = sprintf('(?:%s|%s|%s)', self::DISCRETE_TYPE, self::COMPOSITE_TYPE, self::EXTENTION_TOKEN);

        // Subtype Regex
        $SubType = self::TOKEN;

        // Match Regex `type/subtype`
        if (preg_match("!$Type\\x2f$SubType!", @strtolower($Test), $m)) {
            return $m[0];
        }

        // Default
        return 'text/plain';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
