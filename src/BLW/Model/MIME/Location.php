<?php
/**
 * Location.php | Apr 08, 2014
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

use BLW\Type\IURI;
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
 * Header class for Location.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * Location := "Location:" [CFWS] absoluteURI [CFWS]
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Location extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Location</code> is invalid.
     *        
     * @param \BLW\Type\IURI $Location
     *            Value of Location header
     * @return void
     */
    public function __construct(IURI $Location)
    {
        
        // 1. Header type
        $this->_Type = 'Location';
        
        // 2. Header value
        
        // Validate $Location
        if ($Location->isValid() && $Location->isAbsolute()) {
            
            // Base
            $this->_Value = @strval($Location);
        }         

        // Invalid $Location
        else
            throw new InvalidArgumentException(0);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd