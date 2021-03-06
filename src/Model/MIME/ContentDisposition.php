<?php
/**
 * ContentDisposition.php | Mar 08, 2014
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
 * Header class for Content-Disposition.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * content-disposition := "Content-Disposition" ":" disposition-type
 *                        *(";" parameter)
 *
 *
 * disposition-type := "attachment" / "inline" / "form-data" / "notification" / token
 *
 * parameter := attribute "=" value
 *
 * attribute := token
 *              ; Matching of attributes
 *              ; is ALWAYS case-insensitive.
 *
 * value := token / quoted-string
 *
 * token := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *             or tspecials>
 *
 * tspecials := "(" / ")" / "<" / ">" / "@" /
 *              "," / ";" / ":" / "\" / <">
 *              "/" / "[" / "]" / "?" / "="
 *              ; Must be in quoted-string,
 *              ; to use within parameter values
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentDisposition extends \BLW\Type\MIME\AHeader
{
    // attachment / inline / form-data / notification
    const DISPOSITION_TYPE = '(?:attachment|inline|form-data|notification)';

    /**
     * Constructor
     *
     * <h4>Note</h4>
     *
     * <p>Raises Notice if a parameter is invalid</p>
     *
     * <hr>
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Disposition</code> is not a string.
     *
     * @param string $Disposition
     *            Content-Disposition value.
     * @param array $Params
     *            Array of parameters with atribute as key and value as value.
     */
    public function __construct($Disposition, array $Params = array())
    {
        // Validate $Disposition
        if (! is_string($Disposition) && ! is_callable(array(
            $Disposition,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);
        }

        // 1. Header type
        $this->_Type = 'Content-Disposition';

        // 2. Header value

        // Type
        $this->_Value = $this->parseDisposition($Disposition);

        // Parameters
        foreach ($Params as $Attribute => $Value) {

            // Add parameter to value
            try {
                $this->_Value .= $this->parseParameter($Attribute, $Value);
            }

            // Skip errors
            catch (InvalidArgumentException $e) {
                trigger_error(sprintf('Invalid parameter attribute (%s) or value (%s)', $Attribute, $Value), E_USER_NOTICE);
            }
        }
    }

    /**
     * Parse a string for content-disposition.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `attatchment` in case of error.
     */
    public static function parseDisposition($Test)
    {
        // Disposition Regex
        $Disposition = sprintf('(?:%s|%s)', self::DISPOSITION_TYPE, self::TOKEN);

        // Match Regex `content-disposition`
        if (preg_match("!^$Disposition$!", @strtolower($Test), $m)) {
            return $m[0];
        }

        // Default
        return 'attachment';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
