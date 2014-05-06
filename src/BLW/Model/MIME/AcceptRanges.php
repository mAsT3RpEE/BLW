<?php
/**
 * AcceptRanges.php | Apr 8, 2014
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
namespace BLW\Model\MIME;

use BLW\Type\IDataMapper;
use BLW\Model\InvalidArgumentException;

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
 * Header class for Accept-Ranges.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Accept-Ranges     := "Accept-Ranges" ":" acceptable-ranges
 * acceptable-ranges := 1#range-unit | "none"
 * range-unit        := token
 * token             := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *                         or tspecials>
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class AcceptRanges extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Ranges[x]</code> is not a string.
     *
     * @param string $Ranges
     *            Accept types separated by a comma (,).
     */
    public function __construct($Ranges)
    {
        // 1. Header type
        $this->_Type = 'Accept-Ranges';

        // 2. Header value

        // Validate $Range
        if (is_string($Ranges) ?  : is_callable(array(
            $Ranges,
            '__toString'
        ))) {

            // Split into array
            $Ranges = explode(' ', $Ranges);

            // Validate again
            if (! empty($Ranges) && array_reduce($Ranges, function ($v, $i)
            {
                return $v && (is_string($i) ?  : is_callable(array(
                    $v,
                    '__toString'
                )));
            }, true)) {

                // Type
                $this->_Value = array_reduce($Ranges, function ($v, $i)
                {

                    $Range = $this->parseRange($i);

                    if ($v && $Range)
                        return "$v $Range";
                    elseif ($Range)
                        return $Range;
                    elseif ($v)
                        return $v;
                    else
                        return '';
                }, '');
            }

            // Invalid $Range
            else
                throw new InvalidArgumentException(0);
        }

        // Invalid $Range
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for charset.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `none` in case of error.
     */
    public static function parseRange($Test)
    {
        // Ranges Regex
        $Range = self::TOKEN;

        // Match Regex `acceptable-ranges`
        if (preg_match("!$Range!", @strtolower($Test), $m))
            return $m[0];

        // Default
        return 'none';
    }
}

return true;
