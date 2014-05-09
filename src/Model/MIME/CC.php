<?php
/**
 * CC.php | Mar 08, 2014
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\MIME;

use BLW\Type\IContainer;
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
 * Header class for Content-Location.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * content-location := "CC:" [CFWS] addr-list [CFWS]
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class CC extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$AddressList</code> does not conain any <code>IEmailAddress</code> objects.
     *
     * @param \BLW\Type\IContainer $AddressList
     *            Container of <code>IEmailAddress</code>.
     */
    public function __construct(IContainer $AddressList)
    {

        // 1. Header type
        $this->_Type = 'Cc';

        // 2. Header value
        $this->_Value = $this->parseAddressList($AddressList);

        // Check value
        if (! $this->_Value) {
            throw new InvalidArgumentException(0);
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
