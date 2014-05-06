<?php
/**
 * Via.php | Apr 8, 2014
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
use BLW\Type\AURI;
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
 * Header class for Via.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Via := "Via" ":" 1#( received-protocol received-by [ comment ] )
 * received-protocol := [ protocol-name "/" ] protocol-version
 * protocol-name := token
 * protocol-version := token
 * received-by := ( host [ ":" port ] ) | pseudonym
 * pseudonym := token
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Via extends \BLW\Type\MIME\AHeader
{
    // token = *(1*DIGIT "-" 1*DIGIT / 1*DIGIT "-" / "-" 1*DIGIT)
    const RANGE = '[\x21\x23-\x27\x2b\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]+\s*\x3d\s*(?:[0-9]+\x2d[0-9]*|\x2d[0-9]+)(?:\x2c\s*(?:[0-9]+\x2d[0-9]*|\x2d[0-9]+))*';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Via[x]</code> is not a string.
     *        
     * @param string $Via
     *            Accept types separated by a comma (,).
     * @return void
     */
    public function __construct($Via)
    {
        // 1. Header type
        $this->_Type = 'Via';
        
        // 2. Header value
        
        // Validate $Via
        if (is_string($Via) ?  : is_callable(array(
            $Via,
            '__toString'
        ))) {
            
            // Split into array
            $Via = preg_split('!\s*\x2c\s*!', $Via);
            
            // Validate again
            if (! empty($Via) && array_reduce($Via, function ($v, $i)
            {
                return $v && (is_string($i) ?  : is_callable(array(
                    $v,
                    '__toString'
                )));
            }, true)) {
                
                // Type
                $this->_Value = array_reduce($Via, function ($v, $i)
                {
                    
                    $Via = $this->parseVia($i);
                    
                    if ($v && $Via)
                        return "$v, $Via";
                    elseif ($Via)
                        return $Via;
                    elseif ($v)
                        return $v;
                    else
                        return '';
                }, '');
            }             

            // Invalid $Via
            else
                throw new InvalidArgumentException(0);
        }         

        // Invalid $Via
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for via.
     *
     * @api BLW
     * 
     * @since 1.0.0
     * @uses \BLW\Type\AEmailAddress::getRegex() AEmailAdress::getRegex()
     *      
     * @param string $Test
     *            String to search.
     * @return string Returns empty string in case of error.
     */
    public static function parseVia($Test)
    {
        // Via Regex
        $Via = sprintf('(?:%s\s*\x2f\s*)?%s(?:%s|%s)%s?', self::TOKEN, self::TOKEN, AEmailAddress::getRegex('domain'), self::TOKEN, AEmailAddress::getRegex('comment'));
        
        // Match Regex `via`
        if (preg_match("!$Via!", @substr($Test, 0, 1024), $m))
            return $m[0];
            
            // Default
        return '';
    }
}

return true;
