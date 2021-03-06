<?php
/**
 * Generic.php | Apr 8, 2014
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
 * Header class for any MIME header.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * x-header := token ":" *text
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Generic extends \BLW\Type\MIME\AHeader
{
    // All printable characters except NL / CR
    const TEXT = '[P{L}\x08\x20\x21-\x7e]+';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If:
     *
     * <ul>
     * <li><code>$Type</code> is not a string</li>
     * <li><code>$Value</code> is not a string</li>
     * </ul>
     *
     * @param string $Type
     *            Header type.
     * @param string $Value
     *            Value of header
     */
    public function __construct($Type, $Value = '')
    {
        // 1. Header type

        // Is $Type a string?
        if (! is_string($Type) && ! is_callable(array(
            $Type,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } else {
            $this->_Type = $this->parseType($Type);
        }

        // 2. Header value

        // Is $Value a string?
        if (! is_string($Value) && ! is_callable(array(
            $Value,
            '__toString'
        ))) {
            throw new InvalidArgumentException(1);

        } else {
            $this->_Value = $this->parseValue($Value);
        }
    }

    /**
     * Parse a string for type.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `x-header` in case of error.
     */
    public static function parseType($Test)
    {
        // Type Regex
        $Value = self::TOKEN;

        // Match Regex `token`
        if (preg_match("!$Value!", @strval($Test), $m)) {
            return $m[0];
        }

        // Default
        return 'X-Header';
    }

    /**
     * Parse a string for value.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns empty string in case of error.
     */
    public static function parseValue($Test)
    {
        // Type Regex
        $Value = self::TEXT;

        // Match Regex `type/subtype`
        if (preg_match("!$Value!", @strval($Test), $m)) {
            return $m[0];
        }

        // Default
        return '';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
