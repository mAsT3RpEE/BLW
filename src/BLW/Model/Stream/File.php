<?php
/**
 * File.php | Jan 30, 2014
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
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Stream;

use BLW\Type\IFile;
use BLW\Type\IDataMapper;

use BLW\Model\FileException;
use BLW\Model\InvalidArgumentException;


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
 * Base class for file streams.
 *
 * @package BLW\Stream
 * @version GIT 0.2.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IFile $File File used in object creation.
 * @property resource $Context Stream context.
 */
class File extends \BLW\Type\AStream
{

    /**
     * File used in object creation.
     *
     * @var \BLW\Type\IFile $_File
     */
    protected $_File;

    /**
     * Stream context.
     *
     * @var resource $_Context
     */
    protected $_Context;

    /**
     * Creates a stream from a file.
     *
     * @throws \BLW\Model\FileException If there is a problem opening the file.
     *
     * @param \BLW\Type\IFile $File
     *            File to turn into a stream.
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     */
    public function __construct(IFile $File, $flags = IFile::FILE_FLAGS, $context = array())
    {
        $this->_File    = $File;
        $this->_Context = $context;
        $this->_fp      = $File->createResource($flags, $context);
    }

    /**
     * Close file pointer
     */
    public function __destruct()
    {
        // Close file handle
        @fclose($this->_fp);
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
            // IFileStream
            case 'File':
                return $this->_File;
            case 'Context':
                return $this->_Context;

            default:
                trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }

        return null;
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of property to search for.
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
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
            case 'File':
                return $this->_File !== null;
            case 'Context':
                return $this->_Context !== null;
            // Undefined
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
     * @return bool Returns a <code>IDataMapper</code> status code.
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
            case 'File':
            case 'Context':
                $result = IDataMapper::READONLY;
                break;
            default:
                $result = IDataMapper::INVALID;
                break;
        }

        switch ($result) {
            // Readonly property
            case IDataMapper::READONLY:
            case IDataMapper::ONESHOT:
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;

            // Invalid value for property
            case IDataMapper::INVALID:
                trigger_error(sprintf('Invalid value %s for property: %s::$%s', @print_r($value, true), get_class($this), $name), E_USER_NOTICE);
                break;

            // Undefined property property
            case IDataMapper::UNDEFINED:
                trigger_error(sprintf('Tried to modify non-existant property: %s::$%s', get_class($this), $name), E_USER_ERROR);
                break;
        }

        return $result;
    }
}

return true;
