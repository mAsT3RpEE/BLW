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
namespace BLW\Model\MIME\Body;

use BLW\Type\MIME\IBody;
use BLW\Type\MIME\ISection;
use BLW\Type\MIME\IPart;

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
 * +---------------------------------------------------+      +------------------+       +------------------+
 * | BODY\RFC822                                       |<-----| CONTAINER        |<--+---| ArrayObject      |
 * +---------------------------------------------------+      | ================ |   |   +------------------+
 * | _CRLF:      "\r\n"                                |      | HEADER           |   +---| SERIALIZABLE     |
 * | _Sections:  MIME\Section[]                        |      | PART             |   |   | ================ |
 * +---------------------------------------------------+      | string           |   |   | Serializable     |
 * | __construct():                                    |      +------------------+   |   +------------------+
 * |                                                   |                             +---| ITERABLE         |
 * |                                                   |                                 +------------------+
 * | $Section: MIME\Section                            |
 * +---------------------------------------------------+
 * | getSection() MIME\Section                         |
 * +---------------------------------------------------+
 * | addSection(): bool                                |
 * |                                                   |
 * | $Section: MIME\Section                            |
 * +---------------------------------------------------+
 * | endSection(): _Section                            |
 * +---------------------------------------------------+
 * | addPart(): bool                                   |
 * |                                                   |
 * | $Part: MIME\Part                                  |
 * +---------------------------------------------------+
 * | __toString(): string                              |
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
class RFC822 extends \BLW\Type\AContainer implements \BLW\Type\MIME\IBody
{

    const ATTACHMENT       = '\\BLW\\Model\\MIME\\Attachment';
    const INLINEATTACHMENT = '\\BLW\\Model\\MIME\\InlineAttachment';

    /**
     * "\r\n"
     *
     * @var string $_CRLF
     */
    protected $_CRLF = "\r\n";

    /**
     * Current body sections.
     *
     * @var \BLW\Type\MIME\ISection[] $_Sections
     */
    protected $_Sections = array();

    /**
     * Constructor
     *
     * @param \BLW\Model\MIME\Section $Section
     *            Main section of Mime body
     */
    public function __construct(ISection $Section)
    {
        // IContainer constructor
        parent::__construct(IBody::HEADER, IBody::PART, 'string');

        // Set up properties
        $this->_Sections = array(
            $Section
        );
    }

    /**
     * Returns the current mime section of head.
     *
     * @return \BLW\Model\MIME\Section $_Section. Returns <code>null</code> on error.
     */
    public function getSection()
    {
        return array_key_exists(0, $this->_Sections) ? $this->_Sections[0] : null;
    }

    /**
     * Adds a new MIME part to body.
     *
     * @param \BLW\Model\MIME\Section $Section
     *            Part to add.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function addSection(ISection $Section)
    {
        // Does current section exist?
        if (array_key_exists(0, $this->_Sections)) {

            // Add boundary
            $this[] = $this->_Sections[0]->createBoundary();
        }

        // Add section to list
        array_unshift($this->_Sections, $Section);

        $this[] = $Section->createStart();
        $this[] = $this->_CRLF;

        // Done
        return true;
    }

    /**
     * Ends MIME part in body.
     *
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function endSection()
    {
        // Pop MIME part of list
        if ($Section = array_shift($this->_Sections)) {

            // Create MIME boundary
            $this[] = $Section->createEnd();
            $this[] = $this->_CRLF;

            // Success
            return true;
        }

        // Failure
        return false;
    }

    /**
     * Adds a part the current mime body.
     *
     * @throws InvalidArgumentException If <code>$Attachment</code> is not a real attachment.
     *
     * @param \BLW\Type\MIME\IPart $Part
     *            Attachment / FormField / HTML / Text / etc MIME Part.
     * @return boolean <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function addPart(IPart $Part)
    {
        // Does current section exist?
        if (array_key_exists(0, $this->_Sections)) {

            // Add attachment to current section
            $this[] = $this->_Sections[0]->createBoundary();
            $this[] = $Part;

            // Success
            return true;
        }

        // Failure
        return false;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        // Return value
        $return = '';

        // Add content
        foreach ($this as $v) {
            if (is_string($v) ?: is_callable(array(
                $v,
                '__toString'
            ))) {
                $return .= $v;
            }
        }

        // Close open sections
        $len = count($this->_Sections);

        for ($i = 0; $i < $len; $i++) {

            $return .= $this->_Sections[$i]->createEnd();

            // Still more sections?
            if ($i +  1 < $len) {
                // Add spacer
                $return .= $this->_CRLF;
            }
        }

        // Done
        return $return;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
