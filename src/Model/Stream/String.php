<?php
/**
 * String.php | Jan 30, 2014
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
 * @package BLW\Core
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Stream;

use BLW\Type\IFile;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;
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
 * Base class for string streams.
 *
 * @package BLW\Stream
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string $String [readonly] $_String
 * @property resource $Context [readonly] $_Context
 */
class String extends \BLW\Type\AStream
{

    /**
     * File used in object creation.
     *
     * @var String $_String
     */
    protected $_String;

    /**
     * File flags used for making stream.
     *
     * @var int
     */
    protected $_Flags = IFile::FILE_FLAGS;

    /**
     * Stream context.
     *
     * @var array $_Context
     */
    protected $_Context;

#############################################################################################




#############################################################################################
# Serializable Trait
#############################################################################################

    /**
     * Hook that is called just before an object is serialized.
     */
    public function doSerialize()
    {
        $this->_fp = $this->_String = stream_get_contents($this->_fp);
    }

    /**
     * Hook that is called just after an object is unserialized.
     */
    public function doUnSerialize()
    {
        $this->__construct($this->_String, $this->_Flags, $this->_Context);
    }

#############################################################################################
# Stream Trait
#############################################################################################

    /**
     * @ignore
     * @throws \BLW\Model\InvalidArgumentException If $flags are not supported.
     */
    private function _getfp()
    {
        // Context
        $Context = stream_context_create($this->_Context);

        switch ($this->_Flags & ~ IFile::USE_INCLUDE_PATH) {
            // mode 'a';
            case IFile::WRITE | IFile::APPEND:
            // mode 'a+';
            case IFile::READ | IFile::WRITE | IFile::APPEND:
                throw new InvalidArgumentException(2, '%header% Stream.String does not support modes a & a+');
                break;
            // mode 'w';
            case IFile::WRITE | IFile::TRUNCATE:
                $this->_String = '';
                $this->_fp     = fopen('php://temp', 'w', false, $Context);
                break;
            // mode 'w+';
            case IFile::READ | IFile::WRITE | IFile::TRUNCATE: // return 'w+';
                $this->_String = '';
                $this->_fp     = fopen('php://temp', 'w+', false, $Context);
                break;
            // mode 'r+'
            case IFile::READ | IFile::WRITE:
                $this->_fp = fopen('php://temp', 'r+', false, $Context);

                fwrite($this->_fp, $this->_String);
                rewind($this->_fp);
                break;
            // mode 'r';
            default:
                $this->_fp = fopen("data:application/octet-stream;base64," . base64_encode($this->_String), 'r', false, $Context);
        }
    }

    /**
     * Creates a stream from a file.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$String</code> is not a string.
     *
     * @param string $String
     *            Variable to turn into a stream.
     * @param integer $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     */
    public function __construct(&$String = '', $flags = IFile::FILE_FLAGS, array $context = array())
    {
        // Is $String a string?
        if (! is_string($String) && ! is_null($String)) {
            throw new InvalidArgumentException(0);
        }

        // Properties
        $this->_Context = $context;
        $this->_Flags   = @intval($flags);
        $this->_String  = &$String;

        // $_fp
        try {
            $this->_getfp($flags, $String);

        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(1, null, $e->getCode(), $e);
        }
    }

    /**
     * Update variable and close file descriptor.
     */
    public function __destruct()
    {
        // Is fp a resource
        if (is_resource($this->_fp)) {
            // Rewind stream
            if (rewind($this->_fp)) {

                // Update string
                $this->_String = stream_get_contents($this->_fp);

                // Close file handle
                fclose($this->_fp);
            }
        }
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
            // ISerializable
            case 'Status':
                return $this->_Status;
            case 'Serializer':
                return $this->getSerializer();
            // IIterable
            case 'Parent':
                return $this->_Parent;
            case 'ID':
                return $this->getID();
            // IStream
            case 'fp':
                return $this->_fp;
            // IStringStream
            case 'String':
                return $this->_String;
            default:
                trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
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
            // ISerializable
            case 'Status':
            case 'Serializer':
                return true;
            // IIterable
            case 'Parent':
                return $this->_Parent !== null;
            case 'ID':
                return $this->getID() !== null;
            // IStream
            case 'fp':
                return $this->_fp !== null;
            // IFileStream
            case 'String':
                return true;
            default:
                return false;
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to set.
     * @param mixed $value
     *            Value of property.
     * @return boolean Returns a <code>IDataMapper</code> status code.
     */
    public function __set($name, $value)
    {
        switch ($name) {
            // ISerializable
            case 'Status':
            case 'Serializer':
            // IIterable
            case 'ID':
                $result = IDataMapper::READONLY;
                break;
            case 'Parent':
                $result = $this->setParent($value);
                break;
            // IStream
            case 'fp':
            // IFileStream
            case 'String':
                $result = IDataMapper::READONLY;
                break;
            default:
                $result = IDataMapper::UNDEFINED;
                break;
        }

        // Check results
        if (list($Message, $Level) = ADataMapper::getErrorInfo($result, get_class($this), $name)) {
            trigger_error($Message, $Level);
        }
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to set property
        switch($name)
        {
            // ISerializable
            case 'Status':
                $this->clearStatus();
                break;
                // IIterable
            case 'Parent':
                $this->clearParent();
                break;
                // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return false;
// @codeCoverageIgnoreEnd
