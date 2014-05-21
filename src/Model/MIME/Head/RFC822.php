<?php
/**
 * RFC822.php | Mar 20, 2014
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
 * | RFC822HEAD                                        |<-----| CONTAINER   |<--+---| ArrayObject      |
 * +---------------------------------------------------+      | =========== |   |   +------------------+
 * | _Version: IHeader                                 |      | IHeader     |   +---| SERIALIZABLE     |
 * | _Section: Section                                 |      | String      |   |   | ================ |
 * | _CRLF:    "\r\n"                                  |      +-------------+   |   | Serializable     |
 * +---------------------------------------------------+                        |   +------------------+
 * | __construct():                                    |                        +---| ITERABLE         |
 * |                                                   |                            +------------------+
 * | $Version: IHeader                                 |
 * | $Section: Section                                 |
 * +---------------------------------------------------+
 * | getVersion(): _Version                            |
 * +---------------------------------------------------+
 * | getSection(): _Section                            |
 * +---------------------------------------------------+
 * | getHeader(): IHeader|false                        |
 * |                                                   |
 * | $Type: string                                     |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class RFC822 extends \BLW\Type\AContainer implements \BLW\Type\MIME\IHead
{

    /**
     * "\r\n"
     *
     * @var string $_CRLF
     */
    protected $_CRLF = "\r\n";

    /**
     * MIME version.
     *
     * @var \BLW\Type\MIME\IHeader $_Version
     */
    protected $_Version = null;

    /**
     * MIME section / part.
     *
     * @var \BLW\Model\MIME\Section $_Section
     */
    protected $_Section = null;

    /**
     * Constructor
     *
     * @param \BLW\Model\MIME\MIMEVersion $Version
     *            Version of
     * @param \BLW\Model\MIME\Section $Section
     *            Main section of MIME body
     */
    public function __construct(IHeader $Version, ISection $Section)
    {
        // IContainer constructor
        parent::__construct(self::HEADER, 'string');

        // Set up properties
        $this->_Version = $Version;
        $this->_Section = $Section;
    }

    /**
     * Returns the mime version of head.
     *
     * @return \BLW\Type\MIME\IHeader $_Version
     */
    public function getVersion()
    {
        return $this->_Version;
    }

    /**
     * Returns the mime section of head.
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return \BLW\Model\MIME\Section $_Section
     */
    public function getSection()
    {
        return $this->_Section;
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
        if (! is_string($Type) && ! is_callable(array(
            $Type,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);
        }

        // Normalize type
        $Type = preg_replace_callback('!\w+!', function ($m) {
            return ucwords($m[0]);

        }, strtolower(trim($Type)));

        // Search indexes. Return if found.
        if (isset($this[$Type])) {
            return $this[$Type];
        }

        // Search through headers
        foreach ($this as $Header) {
            if ($Header instanceof IHeader) {

                // Return header if found
                if ($Header->getType() == $Type) {
                    return $Header;
                }
            }
        }

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
        foreach ($this as $v) {
            if ($v instanceof IHeader) {
                $return .= $v;
            }
        }

        // Mime version
        $return .= $this->_Version;

        // Section start
        $return .= $this->_Section->createStart();
        $return .= $this->_CRLF;

        // Done
        return $return;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
