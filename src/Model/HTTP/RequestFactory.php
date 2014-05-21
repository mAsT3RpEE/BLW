<?php
/**
 * RequestFactory.php | Apr 14, 2014
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
namespace BLW\Model\HTTP;

use ReflectionMethod;
use BLW\Type\IURI;
use BLW\Type\HTTP\IRequest;
use BLW\Type\MIME\IHeader;
use BLW\Type\IFile;
use BLW\Type\HTTP\IRequestFactory;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericURI;
use BLW\Model\MIME\Section;
use BLW\Model\MIME\ContentType;
use BLW\Model\MIME\Part\FormField;
use BLW\Model\MIME\Part\FormData;
use BLW\Model\MIME\Part\FormFile;

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
 * Interface for HTTP Request factory objects.
 *
 * @package BLW\HTTP
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class RequestFactory implements \BLW\Type\HTTP\IRequestFactory
{

#############################################################################################
# FactoryRequest Trait
#############################################################################################

    // Request Interface
    const REQUEST = 'BLW\\Type\\HTTP\\IRequest';

    // Default request class
    const DEFAULT_REQUEST = '\\BLW\\Model\\HTTP\\Request\\Generic';

    /**
     *
     * @var string $_RequestClass [protected] Class to use to create <code>IRequest</code> objects.
     */
    protected $_RequestClass = '';

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
            new ReflectionMethod(get_called_class(), 'createGET'),
            new ReflectionMethod(get_called_class(), 'createHEAD'),
            new ReflectionMethod(get_called_class(), 'createPOST')
        );
    }

    /**
     * @ignore
     * @param \BLW\Type\HTTP\IRequest $Request
     * @param array|\Traversable $Headers
     */
    private function _addHeaders(IRequest $Request, $Headers)
    {
        foreach ($Headers as $Header) {
            if ($Header instanceof IHeader) {

                // Add header
                $Type = $Header->getType();

                // Is header type not set? Add with key.
                if (! isset($Request->Header[$Type])) {
                    $Request->Header[$Type] = $Header;

                // Header type already set? Add without key.
                } else {
                    $Request->Header[] = $Header;
                }
            }
        }
    }

    /**
     * Creates HTTP GET Requests.
     *
     * @param \BLW\Type\IURI $URI
     *            Address of request.
     * @param \BLW\Type\IURI $BaseURI
     *            Base URL of request used to resolve relative addresses.
     * @param array|\Traversable $Headers
     *            Headers to add to request.
     * @return \BLW\Type\HTTP\IRequest Built request.
     */
    public function createGET(IURI $URI, IURI $BaseURI, $Headers = array())
    {
        // Validate $Parameters
        if (! $URI->isValid()) {
            throw new InvalidArgumentException(0);

        // Invalid $Header
        } elseif (! is_array($Headers) && ! $Headers instanceof \Traversable) {
            throw new InvalidArgumentException(2);
        }

        // Create request
        $Request = $this->_RequestClass;
        $Request = new $Request(IRequest::GET);

        // URI
        $Request->URI     = new GenericURI(strval($URI), $BaseURI);
        $Request->Referer = $BaseURI;

        // Headers
        $this->_addHeaders($Request, $Headers);

        // Done
        return $Request;
    }

    /**
     * Creates HTTP HEAD Requests.
     *
     * @param \BLW\Type\IURI $URI
     *            Address of request.
     * @param \BLW\Type\IURI $BaseURI
     *            Base URL of request used to resolve relative addresses.
     * @param array|Traversable $Headers
     *            Headers to add to request.
     * @return \BLW\Type\HTTP\IRequest Built request.
     */
    public function createHEAD(IURI $URI, IURI $BaseURI, $Headers = array())
    {
        // Validate $Parameters
        if (! $URI->isValid()) {
            throw new InvalidArgumentException(0);

        // Invalid $Header
        } elseif (! is_array($Headers) && ! $Headers instanceof \Traversable) {
            throw new InvalidArgumentException(2);
        }

        // Create request
        $Request = $this->_RequestClass;
        $Request = new $Request(IRequest::HEAD);

        // URI
        $Request->URI     = new GenericURI(strval($URI), $BaseURI);
        $Request->Referer = $BaseURI;

        // Headers
        $this->_addHeaders($Request, $Headers);

        // Done
        return $Request;
    }

    /**
     * @ignore
     * @param \BLW\Type\HTTP\IRequest $Request
     * @param array $Fields
     */
    private function _addMultipartFields(IRequest $Request, array $Fields)
    {
        // Section
        $Section = new Section('multipart/form-data');

        // Set Content-Type Header
        $Request->Header['Content-Type'] = $Section->createStart();

        // Add fields
        foreach ($Fields as $Field) {
            $Request->Body[] = $Section->createBoundary();
            $Request->Body[] = $Field;
        }

        // End section
        $Request->Body[] = $Section->createEnd();
    }

    /**
     * @ignore
     * @param \BLW\Type\HTTP\IRequest $Request
     * @param array $Fields
     */
    private function _addUrlEncodedFields(IRequest $Request, array $Fields)
    {
        // Set Content-Type Header
        $Request->Header['Content-Type'] = new ContentType('application/x-www-form-urlencoded');

        // Format body
        $Request->Body[] = new FormData($Fields);
    }

    /**
     * Creates HTTP POST Requests.
     *
     * @param \BLW\Type\IURI $URI
     *            Address of request.
     * @param \BLW\Type\IURI $BaseURI
     *            Base URL of request used to resolve relative addresses.
     * @param array|\Traversable $Data
     *            Post data to send with keys as field names and values as field values.
     *
     * <h4>Note</h4>
     *
     * <p>Field keys should be string values. (ie `foo` and not `0`)</p>
     *
     * <hr>
     *
     * @param array|\Traversable $Headers
     *            Headers to add to request.
     * @return \BLW\Type\HTTP\IRequest Built request.
     */
    public function createPOST(IURI $URI, IURI $BaseURI, $Data = null, $Headers = array())
    {
        // Validate $Parameters
        if (! $URI->isValid()) {
            throw new InvalidArgumentException(0);

        // Invalid $Header
        } elseif (! is_array($Headers) && ! $Headers instanceof \Traversable) {
            throw new InvalidArgumentException(3);
        }

        // Create request
        $Request = $this->_RequestClass;
        $Request = new $Request(IRequest::POST);

        // URI
        $Request->URI     = new GenericURI(strval($URI), $BaseURI);
        $Request->Referer = $BaseURI;

        // Headers
        $this->_addHeaders($Request, $Headers);

        // Build Fields
        $Fields      = array();
        $isMultiPart = false;

        foreach ($Data as $Field => $Value) {
            if (is_string($Field)) {

                // File field
                if ($Value instanceof IFile) {

                    // Add field
                    $Fields[] = new FormFile($Field, $Value);

                    // Switch body to multipart
                    $isMultiPart = true;

                // Direct field
                } elseif ($Value instanceof FormField) {
                    $Fields[] = $Value;

                } elseif ($Value instanceof FormFile) {
                    $Fields[] = $Value;

                // Regular field?
                } elseif (is_scalar($Value) ?: is_callable(array(
                    $Value,
                    '__toString'
                ))) {

                    $Value = is_callable('mb_convert_encoding') ? mb_convert_encoding($Value, 'UTF-8', mb_detect_order()) : strval($Value);

                    // Add field
                    $Fields[] = new FormField($Field, 'text/plain', $Value, 'UTF-8');
                }
            }
        }

        // Multipart body
        if ($isMultiPart) {
            $this->_addMultipartFields($Request, $Fields);

        // Regular body
        } else {
            $this->_addUrlEncodedFields($Request, $Fields);
        }

        // Done
        return $Request;
    }

#############################################################################################
# RequestFactory Trait
#############################################################################################

    /**
     * Constructor
     *
     * @throws \BLw\Model\InvalidArgumentException If $RequestedClass is not a valid class implementing <code>IRequest</code>.
     *
     * @param string $RequestClass
     *            Class to use to create <code>IRequest</code> objects.
     */
    public function __construct($RequestClass = null)
    {
        $RequestClass = $RequestClass
            ? @ltrim($RequestClass, '\\')
            : self::DEFAULT_REQUEST;

        // Validate $Requestclass
        if (! is_string($RequestClass) ?: ! class_exists($RequestClass, true)) {
            throw new InvalidArgumentException(0);

        } elseif (! in_array(self::REQUEST, class_implements($RequestClass, true))) {
            throw new InvalidArgumentException(0);
        }

        // Update $_RequestClass
        $this->_RequestClass = $RequestClass;
    }

#############################################################################################
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
