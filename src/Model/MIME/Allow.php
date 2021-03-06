<?php
/**
 * Allow.php | Apr 8, 2014
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
 * Header class for Accept-Allows.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * allow  := "Allow" ":" METHOD *(, METHOD)
 * method := GET|POST|PUT|HEAD
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Allow extends \BLW\Type\MIME\AHeader
{
    // GET / POST / PUT / HEAD
    const METHOD = '(?:GET|POST|PUT|HEAD)';

    /**
     * @ignore
     * @param string $v
     * @param string $i
     * @return string
     */
    private function _combine($v, $i)
    {
        $Allow = self::parseAllow($i);

        if ($v && $Allow) {
            return "$v, $Allow";
        } else {
            return $Allow;
        }
    }

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Allow[x]</code> is not a string.
     *
     * @param string $Allowed
     *            Accept types separated by a comma (,).
     * @return void
     */
    public function __construct($Allowed)
    {
        // 1. Header type
        $this->_Type = 'Allow';

        // 2. Header value

        // Validate $Allowed
        if (! is_string($Allowed) && ! is_callable(array(
            $Allowed,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } else {

            // Split into array
            $Allowed = preg_split('![\x20\x2c]+!', $Allowed, - 1, PREG_SPLIT_NO_EMPTY);

            // Type
            $this->_Value = array_reduce($Allowed, array($this, '_combine'), '');
        }
    }

    /**
     * Parse a string for method.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `GET` in case of error.
     */
    public static function parseAllow($Test)
    {
        // Allows Regex
        $Allow = self::METHOD;

        // Match Regex `method`
        if (preg_match("!$Allow!", @strtoupper($Test), $m)) {
            return $m[0];
        }

        // Default
        return 'GET';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
