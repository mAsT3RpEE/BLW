<?php
/**
 * Accept.php | Apr 8, 2014
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
 * Header class for Accept.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Accept           := "Accept" ":" #( media-range [ accept-params ])
 * media-range      := ( "*" / "*"
 *                   | ( type "/" "*" )
 *                   | ( type "/" subtype )
 *                   ) *( ";" parameter )
 *
 * accept-params    := ";" "q" "=" qvalue *( accept-extension )
 * accept-extension := ";" token [ "=" ( token | quoted-string ) ]
 *
 * parameter        := attribute "=" value
 *
 * attribute        := token
 * ; Matching of attributes
 * ; is ALWAYS case-insensitive.
 *
 * value            := token / quoted-string
 *
 * token            := 1*<any (US-ASCII) CHAR except SPACE, CTLs,
 *                        or tspecials>
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Accept extends \BLW\Type\MIME\AHeader
{
    // image / audio / video / application
    const DISCRETE_TYPE = '(?:text|image|audio|video|application)';

    // message / multipart
    const COMPOSITE_TYPE = '(?:message|multipart)';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Type[x]</code> is not a string.
     *
     * @param string $Types
     *            Accept types separated by a comma (,).
     * @param array $Params
     *            Array of parameters with atribute as key and value as value.
     */
    public function __construct($Types, array $Params = array())
    {
        // 1. Header type
        $this->_Type = 'Accept';

        // 2. Header value

        // Validate $Types
        if (is_string($Types) ?  : is_callable(array(
            $Types,
            '__toString'
        ))) {

            // Split into array
            $Types = explode(',', $Types);

            // Validate again
            if (! empty($Types) && array_reduce($Types, function ($v, $i)
            {
                return $v && (is_string($i) ?  : is_callable(array(
                    $v,
                    '__toString'
                )));
            }, true)) {

                // Type
                $this->_Value = array_reduce($Types, function ($v, $i)
                {

                    $Type = Accept::parseType($i);

                    if ($v && $Type)
                        return "$v, $Type";
                    elseif ($Type)
                        return $Type;
                    elseif ($v)
                        return $v;
                    else
                        return '';
                }, '');

                // Parameters
                foreach ($Params as $Attribute => $Value) {

                    // Add parameter to value
                    try {
                        $this->_Value .= $this->parseParameter($Attribute, $Value);
                    }

                    // Skip errors
                    catch (InvalidArgumentException $e) {}
                }
            }

            // Invalid $Types
            else
                throw new InvalidArgumentException(0);
        }

        // Invalid $Types
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for media-range.
     *
     * @api BLW
     *
     * @since 1.0.0
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `* / *` in case of error.
     */
    public static function parseType($Test)
    {
        // Type Regex
        $Type = sprintf('(?:%s|%s|%s|\x2a)', self::DISCRETE_TYPE, self::COMPOSITE_TYPE, self::EXTENTION_TOKEN);

        // Subtype Regex
        $SubType = sprintf('(?:%s|\x2a)', self::TOKEN);

        // QValue Regex
        $Qvalue = sprintf('%s*', self::QVALUE);

        // Match Regex `type/subtype(;q=qvalue)*`
        if (preg_match("!$Type\\x2f$SubType$Qvalue!", @strtolower($Test), $m))
            return $m[0];

        // Default
        return '*/*';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
