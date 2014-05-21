<?php
/**
 * ARequest.php | Apr 10, 2014
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

use ReflectionMethod;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;
use BLW\Type\IURI;
use BLW\Type\IConfig;
use BLW\Model\InvalidArgumentException;
use BLW\Model\Config\Generic as GenericConfig;
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
 * Base class for HTTP Request
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-----------------+       +------------------+
 * | REQUEST                                           |<------| MIME\MESSAGE    |<------| FACTORY          |
 * +---------------------------------------------------+       +-----------------+       | ================ |
 * | GET                                               |                                 | createFromString |
 * | POST                                              |                                 | createHeader     |
 * | HEAD                                              |                                 +------------------+
 * | OPTIONS                                           |
 * | CONNECT                                           |
 * +---------------------------------------------------+
 * | _Type:     int                                    |
 * | _URI:      IURI                                   |
 * | _Referer:  IURI                                   |
 * | _Config:   IConfig                                |
 * | #Type:     getType()                              |
 * |            setType()                              |
 * | #URI:      getURI()                               |
 * |            setURI()                               |
 * | #Referer:  getReferer()                           |
 * |            setReferer()                           |
 * | #Config:   _Config                                |
 * | #Header:   getHeader()                            |
 * | #Body:     getBody()                              |
 * +---------------------------------------------------+
 * | getType(): _Type                                  |
 * +---------------------------------------------------+
 * | setType(): IDataMapper::STATUS                    |
 * |                                                   |
 * | $Type:  string                                    |
 * +---------------------------------------------------+
 * | getURI(): _URI                                    |
 * +---------------------------------------------------+
 * | setURI(): IDataMapper::STATUS                     |
 * |                                                   |
 * | $URI:  IURI                                       |
 * +---------------------------------------------------+
 * | getReferer(): _Referer                            |
 * +---------------------------------------------------+
 * | setReferer(): IDataMapper::STATUS                 |
 * |                                                   |
 * | $Referer:  IURI                                   |
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
 * @property string $Type [dynamic] Invokes getType() and setType().
 * @property \BLW\Type\IURI $URI [dynamic] Invokes getURI() and setURI().
 * @property \BLW\Type\IURI $Referer [dynamic] Invokes getReferer() and setReferer().
 * @property \BLW\Type\IConfig $Config [readonly] $_Config
 * @property \BLW\Type\MIME\IHead $Header [dynamic] Invokes getHeader().
 * @property \BLW\Type\MIME\IBody $Body [dynamic] Invokes getBody().
 */
abstract class ARequest extends \BLW\Type\MIME\AMessage implements \BLW\Type\HTTP\IRequest
{

#############################################################################################
# Request Trait
#############################################################################################

    /**
     * Request type:
     *
     * <ul>
     * <li><b>IRequest::GET</b>: GET request ('GET')</li>
     * <li><b>IRequest::POST</b>: POST request ('POST')</li>
     * <li><b>IRequest::HEAD</b>: HEAD request ('HEAD')</li>
     * <li><b>IRequest::OPTIONS</b>: OPTIONS request ('OPTIONS')</li>
     * <li><b>IRequest::CONNECT</b>: CONNECT request ('CONNECT')</li>
     * <li><b>IRequest::DELETE</b>: DELETE request ('DELETE')</li>
     * </ul>
     *
     * @var string $_Type
     */
    protected $_Type = IRequest::GET;

    /**
     * URL to make request to.
     *
     * @var \BLW\Type\IURI $_URI
     */
    protected $_URI = null;

    /**
     * Refering URI.
     *
     * @var \BLW\Type\IURI $_Referer
     */
    protected $_Referer = null;

    /**
     * Request configuration
     *
     * <ul>
     * <li><b>Timeout</b>: <i>int</i> Time in seconds to wait befor timing out request</li>
     * <li><b>Auth</b>: <i>string</i> Username and password to use for connection formatted as "[username]:[password]"</li>
     * <li><b>Proxy</b>: <i>string</i> Proxy server to use formatted as [username[:password]@]host[:port]
     * </ul>
     *
     * @var \BLW\Type\IConfig $_Config
     */
    protected $_Config = array();

#############################################################################################




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
     * Convert a string to a MimeMessage
     *
     * @todo Everything.
     *
     * @param string $String
     *            String of message.
     * @return \BLW\Type\MIME\IMessage Genereted message.
     */
    public static function createFromString($String)
    {
        throw new \RuntimeException('Unimplemented');
    }

#############################################################################################
# Request Trait
#############################################################################################

    /**
     * Constructor
     *
     * @codecoverageIgnore
     *
     * @throws \BLW\Model\InvalidArguementException If type is not a recognised type.
     *
     * @param string $Type
     *            [optional] Request type.
     * @param IConfig $Config
     *            [optional] Request configuration:
     *
     * <ul>
     * <li><b>Timeout</b>: <i>int</i> Time in seconds to wait befor timing out request</li>
     * <li><b>Proxy</b>: <i>string</i> Proxy server to use formatted as [username[:password]@]host[:port]
     * </ul>
     */
    public function __construct($Type = IRequest::GET, IConfig $Config = null)
    {
        // Type
        if ($this->setType($Type) !== IDataMapper::UPDATED) {
            throw new InvalidArgumentException(0);
        }

        // Properties
        $this->_URI     = null;
        $this->_Referer = null;
        $this->_Config  = $Config ?  : new GenericConfig(array(
            'Timeout'       => 30,
            'MaxRedirects'  => 10,
            'EnableCookies' => true
        ));

        // Header
        $this->_Head = new Head();

        // Body
        $this->_Body = new Body();
    }

    /**
     * Return the type of request.
     *
     * @return string $_Type
     */
    public function getType()
    {
        return $this->_Type;
    }

    /**
     * Set the type of request.
     *
     * @throws \BLW\Model\InvalidArgumentException If $Type is unkown.
     *
     * @param string $Type
     *            New Type:
     *
     * <ul>
     * <li><b>GET</b>: <i>'GET'</i></li>
     * <li><b>POST</b>: <i>'POST'</i></li>
     * <li><b>HEAD</b>: <i>'HEAD'</i></li>
     * <li><b>OPTIONS</b>: <i>'OPTIONS'</i></li>
     * <li><b>CONNECT</b>: <i>'CONNECT'</i></li>
     * <li><b>DELETE</b>: <i>'DELETE'</i></li>
     * </ul>
     *
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setType($Type)
    {
        // Validate type
        switch (@substr($Type, 0, 16)) {
            // Valid types
            case IRequest::GET:
            case IRequest::POST:
            case IRequest::HEAD:
            case IRequest::OPTIONS:
            case IRequest::CONNECT:
            case IRequest::DELETE:

                // Set Type
                $this->_Type = $Type;

                // Done
                return IDataMapper::UPDATED;

            // Error
            default:
                return IDataMapper::INVALID;
        }
    }

    /**
     * Return the request URI.
     *
     * @return \BLW\Type\IURI $_URI
     */
    public function getURI()
    {
        return $this->_URI;
    }

    /**
     * Set the request URI
     *
     * <h4>Note</h4>
     *
     * <p>Fails if:</p>
     *
     * <ul>
     * <li>$URI is invalid</li>
     * <li>$URI is not absolute</li>
     * </ul>
     *
     * <hr>
     *
     * @param \BLW\Type\IURI $URI
     *            Request URL.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setURI($URI)
    {
        // Validate $URI
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
     * Return the request referer.
     *
     * @return \BLW\Type\IURI $_Referer
     */
    public function getReferer()
    {
        return $this->_Referer;
    }

    /**
     * Set the request referer.
     *
     * <h4>Note</h4>
     *
     * <p>Fails if:</p>
     *
     * <ul>
     * <li>$URI is invalid</li>
     * <li>$URI is not absolute</li>
     * </ul>
     *
     * <hr>
     *
     * @param \BLW\Type\IURI $Referer
     *            Request referer URL.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setReferer($Referer)
    {
        // Validate $URI
        if ($Referer instanceof IURI) {

            if ($Referer->isValid() && $Referer->isAbsolute()) {

                // Update $_Referer
                $this->_Referer = $Referer;

                // Done
                return IDataMapper::UPDATED;
            }
        }

        // Invalid
        return IDataMapper::INVALID;
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
            case 'Type':
                return $this->getType();
            case 'URI':
                return $this->getURI();
            case 'Referer':
                return $this->getReferer();
            case 'Config':
                return $this->_Config;
            case 'Header':
                return $this->getHeader();
            case 'Body':
                return $this->getBody();
            // Undefined property
            default:
                trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }
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
            case 'Type':
                return $this->getType() !== null;
            case 'URI':
                return $this->getURI() !== null;
            case 'Referer':
                return $this->getReferer() !== null;
            case 'Config':
                return $this->_Config !== null;
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
            case 'Type':
                $result = $this->setType($value);
                break;
            case 'URI':
                $result = $this->setURI($value);
                break;
            case 'Referer':
                $result = $this->setReferer($value);
                break;
            case 'Config':
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
            case 'Type':
                $this->_Type = null;
                break;
            case 'URI':
                $this->_URI = null;
                break;
            case 'Referer':
                $this->_Referer = null;
                break;
            // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
