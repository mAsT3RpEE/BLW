<?php
/**
 * CacheControl.php | Apr 8, 2014
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
use BLW\Type\AEmailAddress;
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
 * Header class for Cache-Control.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Cache-Control = "Cache-Control" ":" 1#cache-directive
 *
 * cache-directive = cache-request-directive
 * | cache-response-directive
 *
 * cache-request-directive =
 * "no-cache" ; Section 14.9.1
 * | "no-store" ; Section 14.9.2
 * | "max-age" "=" delta-seconds ; Section 14.9.3, 14.9.4
 * | "max-stale" [ "=" delta-seconds ] ; Section 14.9.3
 * | "min-fresh" "=" delta-seconds ; Section 14.9.3
 * | "no-transform" ; Section 14.9.5
 * | "only-if-cached" ; Section 14.9.4
 * | cache-extension ; Section 14.9.6
 *
 * cache-response-directive =
 * "public" ; Section 14.9.1
 * | "private" [ "=" <"> 1#field-name <"> ] ; Section 14.9.1
 * | "no-cache" [ "=" <"> 1#field-name <"> ]; Section 14.9.1
 * | "no-store" ; Section 14.9.2
 * | "no-transform" ; Section 14.9.5
 * | "must-revalidate" ; Section 14.9.4
 * | "proxy-revalidate" ; Section 14.9.4
 * | "max-age" "=" delta-seconds ; Section 14.9.3
 * | "s-maxage" "=" delta-seconds ; Section 14.9.3
 * | cache-extension ; Section 14.9.6
 *
 * cache-extension = token [ "=" ( token | quoted-string ) ]
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class CacheControl extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Age[x]</code> is not a string.
     *        
     * @param string $Directives
     *            Accept types separated by a comma (,).
     * @return void
     */
    public function __construct($Directives)
    {
        // 1. Header type
        $this->_Type = 'Cache-Control';
        
        // 2. Header value
        
        // Validate $Directives
        if (is_string($Directives) ?  : is_callable(array(
            $Directives,
            '__toString'
        ))) {
            
            // Split into array
            $Directives = preg_split('!\s*[\x2c]+\s*!', $Directives);
            $this->_Value = array();
            
            // Value
            foreach ($Directives as $Directive) {
                
                // Add directive to value
                $this->_Value[] = $this->parseDirective($Directive);
            }
            
            $this->_Value = implode(', ', $this->_Value);
        }         

        // Invalid $Directives
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for cache-directive.
     *
     * @api BLW
     * 
     * @since 1.0.0
     * @uses \BLW\Type\AEmailAddress::getRegex() AEmailAddress::getRegex()
     *      
     * @param string $Test
     *            String to search.
     * @return string Returns `no-cache` in case of error.
     */
    public function parseDirective($Test)
    {
        $Directive = sprintf('%s(?:\s*\x3d%s)?', self::TOKEN, AEmailAddress::getRegex('word'));
        
        // Match Regex `cache-directive`
        if (preg_match("!$Directive!", @substr($Test, 0, 1024), $m))
            return $m[0];
            
            // Default
        return 'no-cache';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
