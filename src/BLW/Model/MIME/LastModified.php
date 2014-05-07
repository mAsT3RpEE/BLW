<?php
/**
 * LastModified.php | Apr 8, 2014
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

use DateTime;
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
 * Header class for Content-Description.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * LastModified := "DOW, dd mm yyy hh:ii:ss +/-GMT"
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class LastModified extends \BLW\Type\MIME\AHeader
{

    const FORMAT = 'D, d M Y H:i:s O';

    /**
     * Constructor
     *
     * @param \DateTime $DateTime
     *            [optional] Time associated with header
     * @return void
     */
    public function __construct(DateTime $Time = null)
    {
        // Default value for DateTime
        if (! $Time instanceof DateTime)
            $Time = new DateTime();
            
            // 1. Header type
        $this->_Type = 'Last-Modified';
        
        // 2. Header value
        $this->_Value = $Time->format(self::FORMAT);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
