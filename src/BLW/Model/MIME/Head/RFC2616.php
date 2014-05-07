<?php
/**
 * RFC2616.php | Mar 20, 2014
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
namespace BLW\Model\MIME\Head;

use BLW\Type\MIME\IHeader;
use BLW\Type\MIME\ISection;
use BLW\Model\InvalidArgumentException;


// @codeCoverageIgnoreStart
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
// @codeCoverageIgnoreEnd


/**
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+      +-------------+       +------------------+
 * | RFC2616                                           |<-----| CONTAINER   |<--+---| ArrayObject      |
 * +---------------------------------------------------+      | =========== |   |   +------------------+
 * | _CRLF: "\r\n"                                     |      | IHeader     |   +---| SERIALIZABLE     |
 * +---------------------------------------------------+      | String      |   |   | ================ |
 * | __construct():                                    |      +-------------+   |   | Serializable     |
 * +---------------------------------------------------+                        |   +------------------+
 *                                                                              +---| ITERABLE         |
 *                                                                                  +------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class RFC2616 extends \BLW\Type\AContainer implements \BLW\Type\MIME\IHead
{

    /**
     * "\r\n"
     *
     * @var string $_CRLF
     */
    protected $_CRLF = "\r\n";

    /**
     * Constructor
     */
    public function __construct()
    {
        // IContainer constructor
        parent::__construct(self::HEADER, 'string');
    }

    /**
     * Search head for a perticular Header.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Type</code> is not a string.
     *
     * @param string $Type
     *            Header label to seach for.
     * @return \BLW\Type\MIME\IHeader Found header. <code>FALSE</code> otherwise.
     */
    public function getHeader($Type)
    {
        // Is $Type scalar?
        if (is_string($Type) ?  : is_callable(array(
            $Type,
            '__toString'
        ))) {

            // Normalize type
            $Type = preg_replace_callback('!\w+!', function ($m)
            {
                return ucwords($m[0]);
            }, strtolower(trim($Type)));

            // Search indexes. Return if found.
            if (isset($this[$Type]))
                return $this[$Type];

                // Search through headers
            foreach ($this as $Header)
                if ($Header instanceof IHeader) {

                    // Return header if found
                    if ($Header->getType() == $Type)
                        return $Header;
                }
        }

        // Invalid $Type
        else
            throw new InvalidArgumentException(0);

        // Error / not found. Return false
        return false;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        $return = '';

        // Add headers
        foreach ($this as $v)
            if ($v instanceof IHeader)
                $return .= $v;

        // Body start
        $return .= $this->_CRLF;

        // Done
        return $return;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
