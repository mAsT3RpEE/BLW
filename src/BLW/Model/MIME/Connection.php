<?php
/**
 * Connection.php | Apr 8, 2014
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
 * Header class for Connection.
 *
 * <h3>RFC2616</h3>
 *
 * <pre>
 * Connection := "Connection" ":" connection-token
 * connection-token := token
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class Connection extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Connection[x]</code> is not a string.
     *        
     * @param string $Connection
     *            Accept types separated by a comma (,).
     * @return void
     */
    public function __construct($Connection)
    {
        // 1. Header type
        $this->_Type = 'Connection';
        
        // 2. Header value
        
        // Validate $Connection
        if (is_string($Connection) ?  : is_callable(array(
            $Connection,
            '__toString'
        ))) {
            
            // Type
            $this->_Value = $this->parseConnection($Connection);
        }         

        // Invalid $Connection
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Parse a string for connection-token.
     *
     * @api BLW
     * 
     * @since 1.0.0
     *       
     * @param string $Test
     *            String to search.
     * @return string Returns empty string in case of error.
     */
    public static function parseConnection($Test)
    {
        // Connection Regex
        $Connection = self::TOKEN;
        
        // Match Regex `acceptable-ranges`
        if (preg_match("!$Connection!", @strtolower($Test), $m))
            return $m[0];
            
            // Default
        return '';
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
