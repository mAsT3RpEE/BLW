<?php
/**
 * ContentMD5.php | Mar 08, 2014
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
 * Header class for Content-MD5.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * Content-MD5 := "Content-MD5" ":" md5-digest; See RFC 1864
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentMD5 extends \BLW\Type\MIME\AHeader
{
    // See RFC 1864
    const MD5 = '[0-9a-z]{32}';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$MD5</code> is not a string.
     *        
     * @param string $MD5
     *            Value of Content-Transfer-MD5 header.
     * @return void
     */
    public function __construct($MD5)
    {
        // 1. Header type
        $this->_Type = 'Content-MD5';
        
        // 2. Header value
        
        // Validate $MD5
        if (is_string($MD5) ?  : is_callable(array(
            $MD5,
            '__toString'
        ))) {
            
            // Type
            $this->_Value = $this->parseMD5($MD5);
        }         

        // Invalid $Type
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for md5-digest.
     *
     * @api BLW
     * 
     * @since 1.0.0
     *       
     * @param string $Test
     *            String to search.
     * @return string Returns `d41d8cd98f00b204e9800998ecf8427e` in case of error.
     */
    public static function parseMD5($Test)
    {
        // Type Regex
        $MD5 = self::MD5;
        
        // Match Regex `md5-digest`
        if (preg_match("!$MD5!", @strtolower($Test), $m))
            return $m[0];
            
            // Default
        return 'd41d8cd98f00b204e9800998ecf8427e';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
