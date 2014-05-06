<?php
/**
 * ContentBase.php | Mar 08, 2014
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
 * Header class for Content-Base.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * content-base := "Content-Base:" [CFWS] absoluteURI [CFWS]
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentBase extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Base</code> is invalid.
     *        
     * @param \BLW\Type\IURI $Base
     *            Value of Content-Base header
     * @return void
     */
    public function __construct(IURI $Base)
    {
        
        // 1. Header type
        $this->_Type = 'Content-Base';
        
        // 2. Header value
        
        // Validate $Base
        if ($Base->isValid() && $Base->isAbsolute()) {
            
            // Base
            $this->_Value = @strval($Base);
        }         

        // Invalid $Base
        else
            throw new InvalidArgumentException(0);
    }
}
