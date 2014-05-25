<?php
/**
 * MIMEVersion.php | Mar 12, 2014
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
 * Header class for MIME-Version.
 *
 * <h3>RFC822</h3>
 *
 * <pre>
 * version := "MIME-Version" ":" *text
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
final class MIMEVersion extends \BLW\Type\MIME\AHeader
{

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Description</code> is not numeric.
     *
     * @param string|float $Version
     *            Value of MIME-Version header
     */
    public function __construct($Version)
    {
        // 1. Header type
        $this->_Type = 'MIME-Version';

        // 2. Header value

        // Validate $Version
        if (is_numeric($Version)) {
            // Description
            $this->_Value = floatval($Version);

        // Invalid $Version
        } else {
            throw new InvalidArgumentException(0);
        }
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        if ($this->_Type && $this->_Value) {
            return sprintf('%s: %01.1f%s', $this->_Type, $this->_Value, $this->_CRLF);

        } else {
            trigger_error('Type or Value not set', E_USER_WARNING);

            return '';
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
