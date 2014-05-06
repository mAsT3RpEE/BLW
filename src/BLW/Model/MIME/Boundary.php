<?php
/**
 * Boundary.php | Mar 20, 2014
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
 * Header class for MIME boundary.
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Boundary extends \BLW\Type\MIME\AHeader
{
    // Boundary regex (ALPHA / NUM) 1*(ALPHA / NUM / - / : / = / _)
    const BOUNDARY = '[0-9A-Za-z][\x2d\x3a\x3d0-9A-Z\x5fa-z]+';
    
    // String constants
    const START = '--';

    const END = '--';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Boundary</code> is not a string.
     *        
     * @param string $Boundary
     *            MIME boundary in Content-Type header.
     * @param string $isLast
     *            [optional] Is this the last / ending boundary.
     * @return void
     */
    public function __construct($Boundary, $isLast = false)
    {
        // 1. Header type
        $this->_Type = self::START;
        
        // 2. Header value
        
        // Validate $Boundary
        if (is_string($Boundary) ?  : is_callable(array(
            $Boundary,
            '__toString'
        ))) {
            
            // Set boundary and end if necessary
            $this->_Value = $this->parseBoundary($Boundary) . ($isLast ? self::END : '');
            
            // Check boundary
            if (strlen($this->_Value) <= 4)
                throw new InvalidArgumentException(0);
        }         

        // Invalid $Boundary
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for MIME boundary.
     *
     * @api BLW
     * 
     * @since 1.0.0
     *       
     * @param string $Test
     *            String to search.
     * @return string Returns empty string in case of error.
     */
    public function parseBoundary($Test)
    {
        // Boundary Regex
        $Boundary = self::BOUNDARY;
        
        // Match Regex `addr-spec`
        if (preg_match("!$Boundary!", @strval($Test), $m))
            return $m[0];
            
            // Default
        return '';
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        return sprintf('%s%s%s', $this->_Type, $this->_Value, $this->_CRLF);
    }
}
