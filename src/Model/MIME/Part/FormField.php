<?php
/**
 * FormField.php | Apr 10, 2014
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
namespace BLW\Model\MIME\Part;

use BLW\Type\MIME\IHeader;
use BLW\Type\MIME\IPart;
use BLW\Model\InvalidArgumentException;
use BLW\Model\Stream\String as StringStream;
use BLW\Model\MIME\ContentType;
use BLW\Model\MIME\ContentDisposition;
use BLW\Model\MIME\ContentTransferEncoding;

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
 * Form field class.
 *
 * @package BLW\MIME
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class FormField extends \BLW\Type\AContainer implements \BLW\Type\MIME\IPart
{

    const CHUNKLEN = 76;

    /**
     * "\r\n"
     *
     * @var string $_CRLF
     */
    protected $_CRLF = "\r\n";

    /**
     * Field name / label
     *
     * @var string $_Field
     */
    protected $_Field = '';

    /**
     * Constructor
     *
     * @param string $Field
     *            Label of field (ie foo, foo[bar]).
     * @param string $Type
     *            Content-Type.
     * @param string $Content
     *            Value of field.
     * @param string $Charset
     *            Character set of $Content.
     */
    public function __construct($Field, $Type, $Content, $Charset = 'utf-8')
    {
        // IContainer constructor
        parent::__construct(IPart::HEADER, 'string');

        // Ensure content is a string
        if (! is_string($Content) && ! is_callable(array(
            $Content,
            '__toString'
        ))) {
            throw new InvalidArgumentException(1);

        } else {

            // Part Headers
            parent::offsetSet('Content-Disposition', new ContentDisposition('form-data', array(
                'name' => $Field
            )));
            parent::offsetSet('Content-Type', new ContentType($Type, array(
                'charset' => $Charset
            )));
            parent::offsetSet('Content-Transfer-Encoding', new ContentTransferEncoding('quoted-printable'));

            // Part content
            parent::offsetSet('Content', $this->format($Content, self::CHUNKLEN) . $this->_CRLF);

            // Store $Field
            $this->_Field = $Field;
        }
    }

    /**
     * Retrieve the field name used during creation.
     *
     * @return string $_Field
     */
    public function getField()
    {
        return $this->_Field;
    }

    /**
     * Format a part body.
     *
     * @uses \BLW\Model\Stream\String Stream\String
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Content</code> is not a string.
     *
     * @param string $Content
     *            String to encode
     * @param integer $Chunklen
     *            Maximum line length of formatted body
     * @return string Formated string. Returns `invalid` on error.
     */
    public static function format($Content, $Chunklen)
    {
        // Ensure content is a string
        if (! is_string($Content) && ! is_callable(array(
            $Content,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } else {

            $Content  = strval($Content);
            $Chunklen = max(1, @intval($Chunklen));
            $Stream   = new StringStream($Content);

            $Stream->addFilter('convert.quoted-printable-encode', STREAM_FILTER_READ, array(
                'line-length' => $Chunklen,
                'line-break-chars' => "\r\n"
            ));

            return strval($Stream) ?: 'invalid';
        }
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
        foreach ($this as $Header) {
            if ($Header instanceof IHeader) {
                $return .= $Header;
            }
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
     * @return void
     */
    public function offsetSet($index, $newval)
    {
        // Check index
        switch ($index) {
            // Readonly
            case 'Content-Type':
            case 'Content-Disposition':
            case 'Content-Transfer-Encoding':
            case 'Content':

                trigger_error(sprintf('Cannot modify readonly offset %s[%s]', get_class($this), $index), E_USER_WARNING);
                break;

            // IContainer
            default:

                parent::offsetSet($index, $newval);
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
