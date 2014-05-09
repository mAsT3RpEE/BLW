<?php
/**
 * Pragma.php | Apr 8, 2014
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
use BLW\Type\AEmailAddress;

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
 * Header class for Pragma.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Pragma           := "Pragma" ":" 1#pragma-directive
 * pragma-directive := "no-cache" | extension-pragma
 * extension-pragma := token [ "=" ( token | quoted-string ) ]
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Pragma extends \BLW\Type\MIME\AHeader
{

    /**
     * @ignore
     * @param string $v
     * @param string $i
     * @return string
     */
    private function _combine($v, $i)
    {
        $Directive = self::parseDirective($i);

        if ($v && $Directive) {
            return "$v, $Directive";
        } else {
            return $Directive;
        }
    }

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Age[x]</code> is not a string.
     *
     * @param string $Directives
     *            Directives separated by a comma (,).
     */
    public function __construct($Directives)
    {
        if (! is_string($Directives) && ! is_callable(array(
            $Directives,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);
        }

        // 1. Header type
        $this->_Type = 'Pragma';

        // 2. Header value

        // Split into array
        $this->_Value = array_reduce(explode(',', $Directives), array($this, '_combine'));
    }

    /**
     * Parse a string for cache-directive.
     *
     * @api BLW
     * @since   1.0.0
     * @uses \BLW\Type\AEmailAddress::getRegex() AEmailAddress::getRegex()
     *
     * @param string $Test
     *            String to search.
     * @return string Returns `no-cache` in case of error.
     */
    public static function parseDirective($Test)
    {
        $Directive = sprintf('%s(?:\s*\x3d%s)?', self::TOKEN, AEmailAddress::getRegex('word'));

        // Match Regex `cache-directive`
        if (preg_match("!$Directive!", @substr($Test, 0, 1024), $m)) {
            return $m[0];
        }

        // Default
        return 'no-cache';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
