<?php
/**
 * ContentID.php | Mar 08, 2014
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
 * Header class for Content-ID.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * id := "Content-ID" ":" msg-id
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class ContentID extends \BLW\Type\MIME\AHeader
{
    // alphanumeric / "." / "-" / "_"
    const MSG_ID = '[\x2d\x2e\x30-\x39\x41-\x5a\x5f\x61-\x7a]{8,}';

    /**
     *
     * @var string $_ID Unique id
     */
    protected $_ID = '';

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$ID / $Host</code> is not a string.
     *        
     * @param string $Address
     *            Address specification.
     * @return void
     */
    public function __construct($Address = '')
    {
        // 1. Header type
        $this->_Type = 'Content-ID';
        
        // 2. Header value
        
        // Validate $Address
        if (is_string($Address) ?  : is_callable(array(
            $Address,
            '__toString'
        ))) {
            
            // address-spec
            $this->_ID = $this->parseID($Address);
            $this->_Value = "<$this->_ID>";
        }         

        // Invalid $Address
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Returns the id specified by header.
     *
     * @api BLW
     * 
     * @since 1.0.0
     *       
     * @return string $_ID
     */
    public function getID()
    {
        return $this->_ID;
    }

    /**
     * Returns the url that can be entered in message body.
     *
     * @api BLW
     * 
     * @since 1.0.0
     *       
     * @return string `cid:$_ID`
     */
    public function getURL()
    {
        return "cid:$this->_ID";
    }

    /**
     * Parse a string for unique id.
     *
     * @api BLW
     * 
     * @since 1.0.0
     * @uses \BLW\Type\AEmailAddress::getRegex()
     *      
     * @param string $Test
     *            String to search.
     * @return string Returns <code>uniqid(id, true)</code> in case of error.
     */
    public static function parseID($Test)
    {
        // addr-spec Regex
        $AddrSpec = AEmailAddress::getRegex('addr-spec');
        
        // Match Regex `addr-spec`
        if (preg_match("!$AddrSpec!", @strval($Test), $m))
            return $m[0];
            
            // Default
        return uniqid('id', true) . '@' . (@$_SERVER['HTTP_HOST'] ?  : @$_SERVER['SERVER_NAME'] ?  : @$_SERVER['SERVER_ADDR'] ?  : '0.0.0.0');
    }
}
