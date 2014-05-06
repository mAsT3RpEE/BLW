<?php
/**
 * AcceptCharset.php | Apr 8, 2014
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
 * Header class for Accept-Charset.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Accept-Charset := "Accept-Charset" ":" 1*( ( charset | "*" )[ ";" "q" "=" qvalue ] )
 * charset        := token
 * qvalue         := float
 *
 * token          := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *                      or tspecials>
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class AcceptCharset extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Charset[x]</code> is not a string.
     *
     * @param string $Charsets
     *            Accept types separated by a comma (,).
     * @return void
     */
    public function __construct($Charsets)
    {
        // 1. Header type
        $this->_Type = 'Accept-Charset';

        // 2. Header value

        // Validate $Charsets
        if (is_string($Charsets) ?  : is_callable(array(
            $Charsets,
            '__toString'
        ))) {

            // Split into array
            $Charsets = preg_split('!\s*\x2c\s*!', $Charsets);

            // Validate again
            if (! empty($Charsets) && array_reduce($Charsets, function ($v, $i)
            {
                return $v && (is_string($i) ?  : is_callable(array(
                    $v,
                    '__toString'
                )));
            }, true)) {

                // Type
                $this->_Value = array_reduce($Charsets, function ($v, $i)
                {

                    $Charset = $this->parseCharset($i);

                    if ($v && $Charset)
                        return "$v, $Charset";
                    elseif ($Charset)
                        return $Charset;
                    elseif ($v)
                        return $v;
                    else
                        return '';
                }, '');
            }

            // Invalid $Charsets
            else
                throw new InvalidArgumentException(0);
        }

        // Invalid $Charsets
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
     * @return string Returns `*` in case of error.
     */
    public static function parseCharset($Test)
    {
        // Charset Regex
        $Charset = sprintf('(?:%s|\x2a)%s*', self::TOKEN, self::QVALUE);

        // Match Regex `charset`
        if (preg_match("!$Charset!", @strtolower($Test), $m))
            return $m[0];

        // Default
        return '*';
    }
}

return true;
