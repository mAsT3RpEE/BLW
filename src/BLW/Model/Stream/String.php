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
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\Stream;

use BLW\Type\IFile;
use BLW\Type\IDataMapper;

use BLW\Model\FileException;
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
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
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
     * Stream context.
     *
     * @var resource $_Context
     */
    protected $_Context;

    /**
     * Creates a stream from a file.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$String</code> is not a string.
     *
     * @param string $String
     *            Variable to turn into a stream.
     * @param string $Type
     *            Content-Type of string.
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     */
    public function __construct(&$String = '', $Type = 'text/plain', $flags = IFile::FILE_FLAGS, $context = array())
    {
        // Is $String a string?
        if (is_string($String) || is_null($String)) {

            // Is $Type a string?
            if (is_string($Type)) {

                $this->_String = &$String;

                switch ($flags & ~ IFile::USE_INCLUDE_PATH) {
                    // mode 'a';
                    case IFile::WRITE | IFile::APPEND:
                    // mode 'a+';
                    case IFile::READ | IFile::WRITE | IFile::APPEND:
                        throw new InvalidArgumentException(2, '%header% Stream.String does not support modes a & a+');
                        break;
                    // mode 'w';
                    case IFile::WRITE | IFile::TRUNCATE:
                        $this->_fp = fopen('php://temp', 'w');
                        break;
                    // mode 'w+';
                    case IFile::READ | IFile::WRITE | IFile::TRUNCATE: // return 'w+';
                        $this->_fp = fopen('php://temp', 'w+');
                        break;
                    // mode 'r+'
                    case IFile::READ | IFile::WRITE:
                        $this->_fp = fopen('php://temp', 'r+');

                        fwrite($this->_fp, $String);
                        rewind($this->_fp);
                        break;
                    // mode 'r';
                    default:
                        $this->_fp = fopen("data:$Type;base64," . base64_encode($String), 'r');
                        break;
                }
            }

            // Invalid $Type
            else
                throw new InvalidArgumentException(1);
        }

        // Invalid $String
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Update variable and close file descriptor.
     */
    public function __destruct()
    {
        // Rewind stream
        if (rewind($this->_fp)) {

            // Update string
            $this->_String = stream_get_contents($this->_fp);

            // Close file handle
            fclose($this->_fp);
        }
    }

    /**
     * Flushes the output.
     *
     * @link http://www.php.net/manual/en/streamwrapper.stream-flush.php streamWrapper::stream_flush()
     *
     * @return bool <code>TRUE</code> if the cached data was successfully stored (or if there was no data to store). <code>FALSE</code> otherwise.
     */
    public function stream_flush()
    {
        // Try to flush stream
        if ($return = fflush($this->_fp)) {

            // Does stream contain data?
            if ($pos = ftell($this->_fp)) {

                // Rewind stream
                if (rewind($this->_fp)) {

                    // Update string
                    $this->_String = stream_get_contents($this->_fp);

                    // Reset pointer
                    fseek($this->_fp, $pos);
                }
            }
        }

        // Done
        return $return;
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
            case 'String':
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

return false;