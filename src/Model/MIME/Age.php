<?php
/**
 * Ages.php | Apr 8, 2014
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
 * Header class for Age.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Age       := "Age" ":" age-value
 * age-value := 1*10 DIGIT
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Age extends \BLW\Type\MIME\AHeader
{
    // 1*10 DIGIT
    const AGE_VALUE = '[0-9]{1,10}';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Age[x]</code> is not a string.
     *
     * @param string $Age
     *            Accept types separated by a comma (,).
     */
    public function __construct($Age)
    {
        // 1. Header type
        $this->_Type = 'Age';

        // 2. Header value

        // Validate $Age
        if (! is_string($Age) && ! is_callable(array(
            $Age,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } else {
            // Type
            $this->_Value = $this->parseAge($Age);
        }
    }

    /**
     * Parse a string for age-value.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `2147483648` in case of error.
     */
    public static function parseAge($Test)
    {
        // Ages Regex
        $Age = self::AGE_VALUE;

        // Match Regex `acceptable-ranges`
        if (preg_match("!$Age!", @strtolower($Test), $m)) {
            return $m[0];
        }

        // Default
        return '2147483648';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
