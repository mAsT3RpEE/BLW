<?php
/**
 * Attachment.php | Dec 21, 2013
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
namespace BLW\Model\MIME\Part;

use BLW\Type\IFile;
use BLW\Type\MIME\IHeader;
use BLW\Type\MIME\IPart;

use BLW\Model\FileException;
use BLW\Model\InvalidArgumentException;
use BLW\Model\Stream\String as StringStream;
use BLW\Model\MIME\ContentType;
use BLW\Model\MIME\ContentTransferEncoding;
use BLW\Model\MIME\ContentDisposition;


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
 * File Attachment class.
 *
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Attachment extends \BLW\Type\AContainer implements \BLW\Type\MIME\IPart
{

    const CHUNKLEN = 76;

    /**
     * "\r\n"
     *
     * @var string $_CRLF
     */
    protected $_CRLF = "\r\n";

    /**
     * Constructor
     *
     * @param \BLW\Type\File $File
     *            File to attatch.
     * @param string $Name
     *            File name (basename).
     * @param string $Type
     *            File mime type.
     */
    public function __construct(IFile $File, $Name = null, $Type = null)
    {
        // IContainer constructor
        parent::__construct(IPart::HEADER, 'string');

        // Ensure $Name and $Type are set / not empty
        if (empty($Name))
            $Name = $File->getBasename();

        if (empty($Type))
            $Type = $File->getMimetype();

        // Ensure file is readable
        if ($File->isReadable() || $File->openFile()) {

            // Attachment Headers
            parent::offsetSet('Content-Type', new ContentType($Type, array(
                'name' => $Name
            )));
            parent::offsetSet('Content-Transfer-Encoding', new ContentTransferEncoding('base64'));
            parent::offsetSet('Content-Disposition', new ContentDisposition('attachment', array(
                'filename' => $Name
            )));

            // Attachment content
            parent::offsetSet('Content', $this->format($File->getContents(), self::CHUNKLEN) . $this->_CRLF);
        }

        else
            throw new FileException($File->getPathname());
    }

    /**
     * Format a part body.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Content</code> is not a string.
     *
     * @param string $Content
     *            String to encode
     * @param int $Chunklen
     *            Maximum line length of formatted body
     * @return string Formated string. Returns `invalid` on error.
     */
    public static function format($Content, $Chunklen)
    {
        // Ensure content is a string
        if (is_string($Content) ?  : is_callable(array(
            $Content,
            '__toString'
        ))) {

            // Encode content
            return rtrim(chunk_split(base64_encode($Content), $Chunklen, "\r\n"));
        }

        // Invalid $Content
        else
            throw new InvalidArgumentException(0);

        // Done
        return 'invalid';
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        // String value
        $return = '';

        // Add headers
        foreach ($this as $Header)
            if ($Header instanceof IHeader) {
                $return .= $Header;
            }

            // Add body
        $return .= $this->_CRLF;
        $return .= $this['Content'];
        $return .= $this->_CRLF;

        // Done
        return $return;
    }

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    public function offsetSet($index, $newval)
    {
        // Check index
        switch ($index) {
            // Readonly
            case 'Content-Type':
            case 'Content-Transfer-Encoding':
            case 'Content-Disposition':
            case 'Content':

                trigger_error(sprintf('Cannot modify readonly offset %s[%s]', get_class($this), $index), E_USER_WARNING);
                break;

            // IContainer
            default:

                parent::offsetSet($index, $newval);
        }
    }
}

return true;
