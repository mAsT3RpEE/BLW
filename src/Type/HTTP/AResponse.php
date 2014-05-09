<?php
/**
 * AResponse.php | Apr 10, 2014
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
 * @package BLW\HTTP
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\HTTP;

use OutOfRangeException;
use ReflectionMethod;
use BLW\Type\IURI;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;
use BLW\Type\MIME\IMessage;
use BLW\Model\InvalidArgumentException;
use BLW\Model\MIME\Body\RFC2616 as Body;
use BLW\Model\MIME\Head\RFC2616 as Head;

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
 * Base class for HTTP Reqponse objects.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-----------------+       +------------------+
 * | RESPONSE                                          |<------| MIME\MESSAGE    |<------| FACTORY          |
 * +---------------------------------------------------+       +-----------------+       | ================ |
 * | [###]                                             |       | ArrayAccess     |       | createFromString |
 * +---------------------------------------------------+       +-----------------+       +------------------+
 * | _RequestURI:  URI                                 |
 * | _URI:         URI                                 |
 * | _Protocol:    string                              |
 * | _Version:     string                              |
 * | _Status:      int                                 |
 * | _Codes:       string[]                            |
 * | _Storage:     array                               |
 * | #RequestURI:  getRequestURI()                     |
 * |               setRequestURI()                     |
 * | #URI:         getURI()                            |
 * |               setURI()                            |
 * | #Protocol:    _Protocol                           |
 * | #Version:     _Version                            |
 * | #Status:      _Status                             |
 * | #Header:      getHeader()                         |
 * | #Body:        getBody()                           |
 * +---------------------------------------------------+
 * | getCodeString(): string                           |
 * |                                                   |
 * | $Status:  int                                     |
 * +---------------------------------------------------+
 * | isValidCode() bool                                |
 * |                                                   |
 * | $Code:  int                                       |
 * +---------------------------------------------------+
 * | getRequestURI(): _RequestURI                      |
 * +---------------------------------------------------+
 * | setRequestURI(): IDataMapper::STATUS              |
 * |                                                   |
 * | $RequestURI:  IURI                                |
 * +---------------------------------------------------+
 * | getURI(): _URI                                    |
 * +---------------------------------------------------+
 * | setURI(): IDataMapper::STATUS                     |
 * |                                                   |
 * | $URI:  IURI                                       |
 * +---------------------------------------------------+
 * | setStorage(): IDataMapper::STATUS                 |
 * |                                                   |
 * | $Storage:  array                                  |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\HTTP
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IURI $RequestURI [dynamic] Invokes getRequestURI() and setRequestURI().
 * @property \BLW\Type\IURI $URI [dynamic] Invokes getURI() and setURI().
 * @property string $Protocol [readonly] $_Protocol
 * @property string $Version [readonly] $_Version
 * @property integer $Status [readonly] $_Status
 * @property \BLW\Type\MIME\IHead $Header [readonly] Invokes getHeader().
 * @property \BLW\Type\MIME\IBody $Body [readonly] Invokes getBody().
 */
abstract class AResponse extends \BLW\Type\MIME\AMessage implements \BLW\Type\HTTP\IResponse
{

    const MAX_SIZE = 8388608; // 8*1024*1024 (8MB)

#############################################################################################
# ArrayAccess Trait
#############################################################################################

    /**
     * information about request used by client.
     *
     * @var array $_Storage
     */
    protected $_Storage = array();

#############################################################################################
# Response Trait
#############################################################################################

    /**
     * Original request URI.
     *
     * @var \BLW\Type\IURI $_RequestURI
     */
    protected $_RequestURI = null;

    /**
     * Current / Last URI of response.
     *
     * @var \BLW\Type\IURI $_URI
     */
    protected $_URI = null;

    /**
     * Message protocol (https://tools.ietf.org/html/rfc2616#page-17).
     *
     * @var string $_Protocol
     */
    protected $_Protocol = null;

    /**
     * Protocol version (https://tools.ietf.org/html/rfc2616#page-17).
     *
     * @var string $_Version
     */
    protected $_Version = null;

    /**
     * Status code of request.
     *
     * @var int $_Status
     */
    protected $_Status = null;

    /**
     * Array of HTTP response codes mapped to their meanings.
     *
     * @var array $_Codes
     */
    protected static $_Codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );

#############################################################################################




#############################################################################################
# ArrayAccess Trait
#############################################################################################

    /**
     * Returns whether the requested index exists
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param mixed $index
     *            The index being checked.
     * @return boolean <code>TRUE</code> if the requested index exists, <code>FALSE</code> otherwise.
     */
    public function offsetExists($index)
    {
        // Does key exist?
        return array_key_exists($index, $this->_Storage);
    }

    /**
     * Returns the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param mixed $index
     *            The index with the value.
     * @return mixed The value at the specified index or <code>FALSE</code>.
     */
    public function offsetGet($index)
    {
        // Does key exist?
        if (@array_key_exists($index, $this->_Storage)) {
            // Return value
            return $this->_Storage[$index];

        // No key?
        } else {
            trigger_error(sprintf('Undefined index %s[%s]', get_class($this), @substr($index, 0, 128)), E_USER_NOTICE);
        }
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
        // Is $index scalar?
        if (is_scalar($index)) {
            // Update key
            $this->_Storage[$index] = $newval;

        // Is $index NULL?
        } elseif (is_null($index)) {
            // Create key
            $this->_Storage[] = $newval;

        // No?
        } else {
            throw new OutOfRangeException(sprintf('Unexpected index (%s).', is_object($newval) ? get_class($newval) : gettype($newval)));
        }
    }

    /**
     * Unsets the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     */
    public function offsetUnset($index)
    {
        // Is $index scalar?
        if (is_scalar($index)) {
            // unset value
            unset($this->_Storage[$index]);
        }
    }

    /**
     * Get the number of public objects in the ObjectStorage
     *
     * @link http://www.php.net/manual/en/countable.count.php Countable::count()
     *
     * @return integer The number of objects in storage.
     */
    public function count()
    {
        return count($this->_Storage);
    }

#############################################################################################
# Factory Trait
#############################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createHeader'),
            new ReflectionMethod(get_called_class(), 'createFromString')
        );
    }

    /**
     * Parse string for HTTP message parts.
     *
     * @ignore
     * @param string $String
     *            String to parse.
     * @return array Parsed parts
     */
    private static function _parseParts($String)
    {
        $Status   = 0;
        $Protocol = 'HTTP';
        $Version  = '1.0';
        $Body     = '';
        $Headers  = array();

        // Retrieve 1st line
        $i         = strpos($String, "\n", 5);
        $FirstLine = substr($String, 0, max(++$i, 0));

        // Is it a Response line?
        if (strpos($FirstLine, '/') && substr_count($FirstLine, ' ') > 1) {

            // Parse 1st line
            $String = substr($String, $i);
            $Parts  = explode(' ', $FirstLine, 3);
            $Status = isset($Parts[1])
                ? intval(trim($Parts[1]))
                : 0;

            list ($Protocol, $Version) = explode('/', trim($Parts[0]));
        }

        self::parseParts($String, $Headers, $Body);

        // Return parsed info
        return array(
            'Protocol' => $Protocol,
            'Version'  => $Version,
            'Status'   => $Status,
            'Headers'  => $Headers,
            'Body'     => implode('', $Body)
        );
    }

    /**
     * Convert a string to a MimeMessage
     *
     * @param string $String
     *            String of message.
     * @return \BLW\Type\MIME\IMessage Genereted message.
     */
    public static function createFromString($String)
    {
        $String = @substr($String, 0, self::MAX_SIZE);

        // String exists?
        if (strlen($String) < 8) {
            throw new InvalidArgumentException(0, 'IResponse::createFromString() $String is not a string or is to small');
        }

        // Parse parts
        $Parts = self::_parseParts($String);

        // Create Message
        $Message = new static($Parts['Protocol'], $Parts['Version'], $Parts['Status']);

        // Add headers
        foreach ($Parts['Headers'] as $Type => $Values) {

            $index = count($Values) == 1
                ? $Type
                : null;

            foreach ($Values as $Value) {
                $Message->getHeader()->offsetSet($index, self::createHeader($Type, $Values[0]));
            }
        }

        // Add Body
        $Message->getBody()->offsetSet('Content', $Parts['Body']);

        // Done
        unset($Parts);

        return $Message;
    }

#############################################################################################
# Response Trait
#############################################################################################

    /**
     * Constructor
     *
     * @codecoverageIgnore
     *
     * @throws InvalidArgumentException If:
     *
     * <ul>
     * <li><code>$Protocol</code> is not a string</li>
     * <li><code>$Version</code> is not a string</li>
     * </ul>
     *
     * @param string $Protocol
     *            HTTP protocol (Currently only HTTP).
     * @param string $Version
     *            HTTP protocol version (1.1 | 1.0)
     * @param string $Status
     *            HTTP response status code.
     */
    public function __construct($Protocol = null, $Version = null, $Status = null)
    {
        // Validate params
        if (! is_null($Protocol) && ! is_string($Protocol) && ! is_callable(array(
            $Protocol,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        } elseif (! is_null($Version) && ! is_string($Version) && ! is_callable(array(
            $Version,
            '__toString'
        ))) {
            throw new InvalidArgumentException(1);

        } else {

            // Set up params
            $this->_Protocol   = @substr($Protocol, 0, 128) ?: 'HTTP';
            $this->_Version    = @substr($Version, 0, 128) ?: '1.1';
            $this->_Status     = @intval($Status);
            $this->_RequestURI = null;
            $this->_URI        = null;
            $this->_Storage    = array();

            // Header
            $this->_Head = new Head();

            // Body
            $this->_Body = new Body();
        }
    }

    /**
     * Returns the text of a HTTP status code.
     *
     * @param integer $Code
     *            Code to interprate.
     * @return string Code interpratation.
     */
    public static function getCodeString($Code)
    {
        // Search for code in $_Codes
        if (is_scalar($Code) ? array_key_exists($Code, self::$_Codes) : false) {
            // Done
            return self::$_Codes[$Code];
        }

        // Not Found
        return 'Undefined';
    }

    /**
     * Tests if a code is valid or not.
     *
     * @param integer $Code
     *            Code to test
     * @return boolean <code>TRUE</code> if valid. <code>FALSE</code> otherwise.
     */
    public static function isValidCode($Code)
    {
        return array_key_exists($Code, self::$_Codes);
    }

    /**
     * Retrieve the request URI that produced this response.
     *
     * @return \BLW\Type\IURI $_RequestURI
     */
    public function getRequestURI()
    {
        return $this->_RequestURI;
    }

    /**
     * Sets the request URI that produced the response.
     *
     * @param \BLW\Type\IURI $RequestURI
     *            New request URI
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setRequestURI($RequestURI)
    {
        // Validate $RequestURI
        if ($RequestURI instanceof IURI) {

            if ($RequestURI->isValid() && $RequestURI->isAbsolute()) {

                // Update $_URI
                $this->_RequestURI = $RequestURI;

                // Done
                return IDataMapper::UPDATED;
            }
        }

        // Error
        return IDataMapper::INVALID;
    }

    /**
     * Retrieve the last / current URI of this response.
     *
     * @return \BLW\Type\IURI $_URI
     */
    public function getURI()
    {
        return $this->_URI;
    }

    /**
     * Sets the last / current URI of this response.
     *
     * @param \BLW\Type\IURI $URI
     *            New request URI
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setURI($URI)
    {
        // Validate $RequestURI
        if ($URI instanceof IURI) {

            if ($URI->isValid() && $URI->isAbsolute()) {

                // Update $_URI
                $this->_URI = $URI;

                // Done
                return IDataMapper::UPDATED;
            }
        }

        // Invalid
        return IDataMapper::INVALID;
    }

    /**
     * Sets the information about response created by client.
     *
     * <h4>Note</h4>
     *
     * <p>This information is used to create values for
     * <code>ArrayAccess</code></p>
     *
     * <hr>
     *
     * @param array $Storage
     *            New Storage.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setStorage(array $Storage)
    {
        // Update Storage
        $this->_Storage = $Storage;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Resets the client information about response.
     *
     * @see \BLW\Type\HTTP\IResponse::setStorage() IResponse::setStorage()
     *
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function clearStorage()
    {
        // Reset Storage
        $this->_Storage = array();
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return mixed Returns <code>null</code> if not found.
     */
    public function __get($name)
    {
        switch ($name) {
            // IRequest
            case 'RequestURI':
                return $this->getRequestURI();
            case 'URI':
                return $this->getURI();
            case 'Protocol':
                return $this->_Protocol;
            case 'Version':
                return $this->_Version;
            case 'Status':
                return $this->_Status;
            case 'Header':
                return $this->getHeader();
            case 'Body':
                return $this->getBody();
            // Undefined property
            default:
                trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }

        // Default values
        return null;
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return boolean Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __isset($name)
    {
        switch ($name) {
            // IRequest
            case 'RequestURI':
                return $this->getRequestURI() !== null;
            case 'URI':
                return $this->getURI() !== null;
            case 'Protocol':
                return $this->_Protocol !== null;
            case 'Version':
                return $this->_Version !== null;
            case 'Status':
                return $this->_Status !== null;
            case 'Header':
                return $this->getHeader() !== null;
            case 'Body':
                return $this->getBody() !== null;
            // Undefined property
            default:
                false;
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to set.
     * @param mixed $value
     *            Value of property.
     */
    public function __set($name, $value)
    {
        // Try to set property
        switch ($name) {
            // IRequest
            case 'RequestURI':
                $result = $this->setRequestURI($value);
                break;
            case 'URI':
                $result = $this->setURI($value);
                break;
            case 'Protocol':
            case 'Version':
            case 'Status':
            case 'Header':
            case 'Body':
                $result = IDataMapper::READONLY;
                break;
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }

        // Check results
        if (list($Message, $Level) = ADataMapper::getErrorInfo($result, get_class($this), $name)) {
            trigger_error($Message, $Level);
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to set property
        switch ($name) {
            // IRequest
            case 'RequestURI':
                $this->_RequestURI = null;
                break;
            case 'URI':
                $this->_URI = null;
                break;
            // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
