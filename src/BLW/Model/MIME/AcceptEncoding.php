<?php
/**
 * AcceptEncoding.php | Apr 8, 2014
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
 * Header class for Accept-Encoding.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Accept-Encoding := "Accept-Encoding" ":" 1*( ( encoding | "*" )[ ";" "q" "=" qvalue ] )
 * encoding        := token
 * qvalue          := float
 *
 * token           := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *                       or tspecials>
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class AcceptEncoding extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Encoding[x]</code> is not a string.
     *
     * @param string $Encodings
     *            Accept types separated by a comma (,).
     */
    public function __construct($Encodings)
    {
        // 1. Header type
        $this->_Type = 'Accept-Encoding';

        // 2. Header value

        // Validate $Encodings
        if (is_string($Encodings) ?  : is_callable(array(
            $Encodings,
            '__toString'
        ))) {

            // Split into array
            $Encodings = explode(',', $Encodings);

            // Validate again
            if (! empty($Encodings) && array_reduce($Encodings, function ($v, $i)
            {
                return $v && (is_string($i) ?  : is_callable(array(
                    $v,
                    '__toString'
                )));
            }, true)) {

                // Type
                $this->_Value = array_reduce($Encodings, function ($v, $i)
                {

                    $Encoding = AcceptEncoding::parseEncoding($i);

                    if ($v && $Encoding)
                        return "$v, $Encoding";
                    elseif ($Encoding)
                        return $Encoding;
                    elseif ($v)
                        return $v;
                    else
                        return '';
                }, '');
            }

            // Invalid $Encodings
            else
                throw new InvalidArgumentException(0);
        }

        // Invalid $Encodings
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for encoding.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `*` in case of error.
     */
    public static function parseEncoding($Test)
    {
        // Encoding Regex
        $Encoding = sprintf('(?:%s|\x2a)%s*', self::TOKEN, self::QVALUE);

        // Match Regex `encoding`
        if (preg_match("!$Encoding!", @strtolower($Test), $m))
            return $m[0];

        // Default
        return '*';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
