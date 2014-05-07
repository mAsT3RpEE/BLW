<?php
/**
 * Range.php | Apr 8, 2014
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

use BLW\Model\InvalidArgumentException;
use BLW\Type\IDataMapper;
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
 * Header class for Range.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Range                 := "Range" ":" ranges-specifier
 * ranges-specifier      := byte-ranges-specifier
 * byte-ranges-specifier := bytes-unit "=" byte-range-set
 * byte-range-set        := 1#( byte-range-spec | suffix-byte-range-spec )
 * byte-range-spec       := first-byte-pos "-" [last-byte-pos]
 * first-byte-pos        := 1*DIGIT
 * last-byte-pos         := 1*DIGIT
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Range extends \BLW\Type\MIME\AHeader
{
    // token = *(1*DIGIT "-" 1*DIGIT / 1*DIGIT "-" / "-" 1*DIGIT)
    const RANGE = '[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+\s*\x3d\s*(?:[0-9]+\x2d[0-9]*|\x2d[0-9]+)(?:\x2c\s*(?:[0-9]+\x2d[0-9]*|\x2d[0-9]+))*';

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
        $this->_Type = 'Range';

        // 2. Header value

        // Validate $Range
        if (is_string($Ranges) ?: is_callable(array(
            $Ranges,
            '__toString'
        ))) {

            // Range
            $this->_Value = $this->parseRanges($Ranges);
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
     * @return string Returns `bytes=0-0` in case of error.
     */
    public static function parseRanges($Test)
    {
        // Ranges Regex
        $Ranges = self::RANGE;

        // Match Regex `acceptable-ranges`
        if (preg_match("!$Ranges!", @strtolower($Test), $m))
            return $m[0];

        // Default
        return 'bytes=0-0';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
