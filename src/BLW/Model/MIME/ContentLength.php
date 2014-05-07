<?php
/**
 * ContentLength.php | Mar 08, 2014
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
 * Header class for Content-Length.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * Content-Length := "Content-Length" ":" 1*DIGIT
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentLength extends \BLW\Type\MIME\AHeader
{
    // 7bit / 8bit / binary / quoted-printable / base64
    const LENGTH = '[0-9]+';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Length</code> is not a string.
     *        
     * @param string $Length
     *            Value of Content-Transfer-Length header.
     * @return void
     */
    public function __construct($Length)
    {
        // 1. Header type
        $this->_Type = 'Content-Length';
        
        // 2. Header value
        
        // Validate $Length
        if (is_string($Length) ?  : is_callable(array(
            $Length,
            '__toString'
        ))) {
            
            // Type
            $this->_Value = $this->parseLength($Length);
        }         

        // Invalid $Type
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for length.
     *
     * @api BLW
     * 
     * @since 1.0.0
     *       
     * @param string $Test
     *            String to search.
     * @return string Returns `0` in case of error.
     */
    public static function parseLength($Test)
    {
        // Type Regex
        $Length = self::LENGTH;
        
        // Match Regex `length`
        if (preg_match("!$Length!", @strtolower($Test), $m))
            return $m[0];
            
            // Default
        return '0';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
