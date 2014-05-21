<?php
/**
 * AStream.php | Jan 30, 2014
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
namespace BLW\Type;

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
 * Abstract clsss for all streams.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +-------------------+
 * | Stream                                            |<------| SERIALIZABLE      |
 * +---------------------------------------------------+       | ================= |
 * | _fd:  resource                                    |       | Serializable      |
 * | #fd:  _fd                                         |       +-------------------+
 * +---------------------------------------------------+<------| ITERABLE          |
 * | addFilter(): resource                             |       +-------------------+
 * |                                                   |
 * | $FilterName:  string                              |
 * | $Mode:        int                                 |
 * | $Params:      mixed                               |
 * +---------------------------------------------------+
 * | remFilter(): bool                                 |
 * |                                                   |
 * | $Resource:  resource                              |
 * +---------------------------------------------------+
 * | getContents(): string                             |
 * |                                                   |
 * | $MaxLength:  int                                  |
 * | $Offset:     int                                  |
 * +---------------------------------------------------+
 * | putContents(): int                                |
 * |                                                   |
 * | $Data:       string|stream|array                  |
 * | $MaxLength:  int                                  |
 * | $Offset:     int                                  |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property resource $fp [readonly] $_fp
 */
abstract class AStream extends \BLW\Type\ASerializable implements \BLW\Type\IStream
{

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Pointer to current parent of object.
     *
     * @var \BLW\Type\IObject $Parent
     */
    protected $_Parent = null;

#############################################################################################
# Stream Trait
#############################################################################################

    /**
     * Stream / File resource.
     *
     * @var resouce $_fp
     */
    protected $_fp = null;

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Retrieves the current parent of the object.
     *
     * @return \BLW\Type\IObject Returns <code>null</code> if no parent is set.
     */
    final public function getParent()
    {
        return $this->_Parent;
    }

    /**
     * Sets parent of the current object if null.
     *
     * @internal This is a one shot function (Only works once).
     *
     * @param mised $Parent
     *            New parent of object. (IObject|IContainer|IObjectStorage)
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    final public function setParent($Parent)
    {
        // Make sur object is not a parent of itself
        if ($Parent === $this) {
            return IDataMapper::INVALID;

        // Make sure parent is valid
        } elseif (! $Parent instanceof IObject && ! $Parent instanceof IContainer && ! $Parent instanceof IObjectStorage && ! $Parent instanceof IWrapper) {
            return IDataMapper::INVALID;

        // Make sure parent is not already set
        } elseif (! $this->_Parent instanceof IObject && ! $this->_Parent instanceof IContainer && ! $this->_Parent instanceof IObjectStorage) {

            // Update parent
            $this->_Parent = $Parent;

            return IDataMapper::UPDATED;

        // Else dont update parent
        } else {
            return IDataMapper::ONESHOT;
        }
    }

    /**
     * Clears parent of the current object.
     *
     * @access private
     * @internal For internal use only.
     *
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    final public function clearParent()
    {
        $this->_Parent = null;

        return IDataMapper::UPDATED;
    }

#############################################################################################
# Stream trait
#############################################################################################

    /**
     * Searches for filter in steam_get_filters()
     *
     * @param string $filter
     * @return boolean
     */
    private function _testFilter($filter)
    {
        // Search for filter in list
        foreach (stream_get_filters() as $actual) {

            $actual = str_replace('\\*', '.*', preg_quote($actual));

            if (preg_match("!$actual!", $filter)) {
                return true;
            }
        }

        // Not found
        return false;
    }

    /**
     * Adds a filter to the stream.
     *
     * @link http://www.php.net/manual/en/function.stream-filter-append.php stream_filter_append()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$FilterName</code> is not a valid filter.
     *
     * @param string $FilterName
     *            Filter to add.
     * @param integer $Mode
     *            Filter mode. (STREAM_FILTER_READ, STREAM_FILTER_WRITE, STREAM_FILTER_ALL)
     * @param mixed $Params
     *            Filter will be added with the specified params to the end of the list and will therefore be called last during stream operations
     * @return resource Filter resource.
     */
    public function addFilter($FilterName, $Mode = STREAM_FILTER_WRITE, $Params = array())
    {
        // Is filter valid?
        if (! $this->_testFilter($FilterName)) {
            throw new InvalidArgumentException(0);

        // Is fp valid?
        } elseif (is_resource($this->_fp)) {

            // Add filter, return resource
            return stream_filter_append($this->_fp, $FilterName, $Mode, $Params);

        // Invalid fp
        } else {
            trigger_error('IStream contains invalid resource', E_USER_NOTICE);
        }
    }

    /**
     * Removes a filter from a stream.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Resource</code> is not a valid resource.
     *
     * @param resource $Resource
     *            Filter resource to remove.
     * @return boolean Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public static function remFilter($Resource)
    {
        // Is $Resource a resource?
        if (! is_resource($Resource)) {
            throw new InvalidArgumentException(0);
        }

        // Remove filter
        return stream_filter_remove($Resource);
    }

    /**
     * Reads entire stream into a string.
     *
     * @link http://www.php.net/manual/en/function.stream-get-contents.php stream_get_contents()
     *
     * @param integer $MaxLength
     *            The maximum bytes to read. Defaults to -1 (read all the remaining buffer). .
     * @param integer $Offset
     *            Seek to the specified offset before reading. If this number is negative, no seeking will occur and reading will start from the current position.
     * @return string Returns a string or <code>FALSE</code> on failure.
     */
    public function getContents($MaxLength = -1, $Offset = -1)
    {
        // Is $_fp valid?
        if (is_resource($this->_fp)) {

            // Rewind stream
            @rewind($this->_fp);

            // Return contents
            return stream_get_contents($this->_fp, @intval($MaxLength), @intval($Offset));

        // Invalid $_fp
        } else {
            trigger_error('IStream contains invalid resource', E_USER_NOTICE);
        }
    }

    /**
     * Write a string / stream to a stream.
     *
     * @link http://us1.php.net/manual/en/function.file-put-contents.php file_put_contents()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$data</code> is not a string, resource of instance of IStream.
     *
     * @param string|resource|\BLW\Type\IStream $Data
     *            The data to write. Can be either a string, a file resource, a stream resource or IStream object.
     * @param integer $MaxLength
     *            The maximum bytes to read. Defaults to -1 (read all the remaining buffer). .
     * @param integer $Offset
     *            Seek to the specified offset before reading. If this number is negative, no seeking will occur and reading will start from the current position.
     * @return integer Returns the number of bytes actually written.
     */
    public function putContents($Data, $MaxLength = -1, $Offset = -1)
    {
        // Is $_fp valid?
        if (! is_resource($this->_fp)) {
            trigger_error('IStream contains invalid resource', E_USER_NOTICE);

            return 0;
        }

        // Rewind stream
        ftruncate($this->_fp, 0) ?: rewind($this->_fp);

        // Data is an instance of stream
        if ($Data instanceof IStream) {

            // Copy stream
            return stream_copy_to_stream($Data->fp, $this->_fp, @intval($MaxLength), @intval($Offset));

        // Data is a resource
        } elseif (is_resource($Data)) {

            // Copy stream
            return stream_copy_to_stream($Data, $this->_fp, @intval($MaxLength), @intval($Offset));

        // Data is a string
        } elseif (is_string($Data) ?: is_callable(array(
            $Data,
            '__toString'
        ))) {

             // Copy string to stream in chunks of 1kb
            $MaxLength = intval($MaxLength);
            $Offset    = intval($Offset);
            $Data      = strval($Data);

            // $Maxlength is negative?
            if ($MaxLength < 0) {
                // Set to default
                $MaxLength = PHP_INT_MAX;
            }

            // $Offset is negative?
            if ($Offset < 0) {
                // Set to default
                $Offset = 0;
            }

            // Loop through 1kb blocks
            $len = min(strlen($Data), $MaxLength + $Offset);

            for ($current = $Offset; $current < $len;) {

                $current += fwrite($this->_fp, substr($Data, $current, 1024), 1024);

                if (! $current) {
                    // @codeCoverageIgnoreStart
                    break;
                    // @codeCoverageIgnoreEnd
                }
            }

            // Flush contents
            fflush($this->_fp);

            // Return bytes written
            return $current - $Offset;

        // Dammit
        } else {
            throw new InvalidArgumentException(0);
        }
    }

    /**
     * All objects must have a string representation.
     *
     * @return string String value of object.
     */
    public function __toString()
    {
        return strval($this->getContents());
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
                $result = IDataMapper::READONLY;
                break;
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
     * @todo Everything.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to unset property
        switch ($name) {
            // ISerializable
            case 'Status':
                $this->clearStatus();
                break;
            // IIterable
            case 'Parent':
                $this->clearParent();
                break;
            // IDataMapable
        }
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return print_r($this->_fp, true);
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
