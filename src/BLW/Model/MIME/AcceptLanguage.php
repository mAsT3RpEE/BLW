<?php
/**
 * AcceptLanguage.php | Apr 8, 2014
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
 * Header class for Accept-Language.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Accept-Language := "Accept-Language" ":" 1*( ( language-range | "*" )[ ";" "q" "=" qvalue ] )
 * language-range  := ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class AcceptLanguage extends \BLW\Type\MIME\AHeader
{
    // ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
    const LANCUAGE_RANGE = '(?:[A-Za-z]{1,8}(?:\x2d[A-Za-z]{1,8}|\x2a){0,10})';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Language[x]</code> is not a string.
     *
     * @param string $Languages
     *            Accept types separated by a comma (,).
     * @return void
     */
    public function __construct($Languages)
    {
        // 1. Header type
        $this->_Type = 'Accept-Language';

        // 2. Header value

        // Validate $Languages
        if (is_string($Languages) ?  : is_callable(array(
            $Languages,
            '__toString'
        ))) {

            // Split into array
            $Languages = explode(',', $Languages);

            // Validate again
            if (! empty($Languages) && array_reduce($Languages, function ($v, $i)
            {
                return $v && (is_string($i) ?  : is_callable(array(
                    $v,
                    '__toString'
                )));
            }, true)) {

                // Type
                $this->_Value = array_reduce($Languages, function ($v, $i)
                {

                    $Language = AcceptLanguage::parseLanguage($i);

                    if ($v && $Language)
                        return "$v, $Language";
                    elseif ($Language)
                        return $Language;
                    elseif ($v)
                        return $v;
                    else
                        return '';
                }, '');
            }

            // Invalid $Languages
            else
                throw new InvalidArgumentException(0);
        }

        // Invalid $Languages
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
    public static function parseLanguage($Test)
    {
        // Language Regex
        $Language = sprintf('(?:%s|\x2a)%s*', self::LANCUAGE_RANGE, self::QVALUE);

        // Match Regex `charset`
        if (preg_match("!$Language!", @strtolower($Test), $m))
            return $m[0];

        // Default
        return '*';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
