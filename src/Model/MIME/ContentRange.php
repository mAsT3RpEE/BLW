<?php
/**
 * ContentRange.php | Apr 8, 2014
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
 * Header class for Content-Range.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Content-Range           := "Content-Range" ":" content-range-spec
 *
 * content-range-spec      := byte-content-range-spec
 * byte-content-range-spec := token SP
 *                            byte-range-resp-spec "/"
 *                            ( instance-length | "*" )
 *
 * byte-range-resp-spec    := (first-byte-pos "-" last-byte-pos)
 *                          | "*"
 *
 * instance-length         := 1*DIGIT
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentRange extends \BLW\Type\MIME\AHeader
{
    // token 1*FWS 1*DIGIT *FWS "-" *FWS 1*DIGIT *FWS "/" *FWS 1*DIGIT
    const CONTENT_RANGE = '[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+\s+[0-9]+\s*\x2d\s*[0-9]+\s*\x2f\s*(?:[0-9]+|\x2a)';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Range</code> is not a string.
     *
     * @param string $Range
     *            Value of Content-Desctiption header
     */
    public function __construct($Range)
    {
        // 1. Header type
        $this->_Type = 'Content-Range';

        // 2. Header value

        // Validate $Range
        if (! is_string($Range) && ! is_callable(array(
            $Range,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } else {
            // Range
            $this->_Value = $this->parseRange($Range);
        }
    }

    /**
     * Parse a string for content-range-spec.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `invalid` in case of error.
     */
    public static function parseRange($Test)
    {
        // Type Regex
        $Range = self::CONTENT_RANGE;

        // Match Regex `content-range-spec`
        if (preg_match("!$Range!", @substr($Test, 0, 1024), $m)) {
            return $m[0];
        }

        // Default
        return 'invalid';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
