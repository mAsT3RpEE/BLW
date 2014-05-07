<?php
/**
 * Trailer.php | Apr 8, 2014
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
 * Header class for Trailer.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Trailer := "Trailer" ":" 1*field-name
 * field-name := token
 * token := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 * or tspecials>
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Trailer extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$FieldName[x]</code> is not a string.
     *
     * @param string $FieldNames
     *            Accept types separated by a comma (,).
     * @return void
     */
    public function __construct($FieldNames)
    {
        // 1. Header type
        $this->_Type = 'Trailer';

        // 2. Header value

        // Validate $FieldNames
        if (is_string($FieldNames) ?  : is_callable(array(
            $FieldNames,
            '__toString'
        ))) {

            // Split into array
            $FieldNames = explode(',', $FieldNames);

            // Validate again
            if (! empty($FieldNames) && array_reduce($FieldNames, function ($v, $i)
            {
                return $v && (is_string($i) ?  : is_callable(array(
                    $v,
                    '__toString'
                )));
            }, true)) {

                // Trailer
                $this->_Value = array_reduce($FieldNames, function ($v, $i)
                {

                    $FieldName = Trailer::parseFieldName($i);

                    if ($v && $FieldName)
                        return "$v, $FieldName";
                    elseif ($FieldName)
                        return $FieldName;
                    elseif ($v)
                        return $v;
                    else
                        return '';
                }, '');
            }

            // Invalid $FieldNames
            else
                throw new InvalidArgumentException(0);
        }

        // Invalid $FieldNames
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for field-name.
     *
     * @api BLW
     *
     * @since 1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns empty string in case of error.
     */
    public static function parseFieldName($Test)
    {
        // FieldName Regex
        $FieldName = self::TOKEN;

        // Match Regex `field-name`
        if (preg_match("!$FieldName!", @substr($Test, 0, 1024), $m))
            return $m[0];

            // Default
        return '';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
