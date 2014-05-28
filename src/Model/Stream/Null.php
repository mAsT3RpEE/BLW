<?php
/**
 * Null.php | Jan 30, 2014
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
use BLW\Model\FileException;
use BLW\Model\GenericFile;

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
 * Null stream.
 *
 * @package BLW\Stream
 * @version GIT: 0.2.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property integer $Flags File open flags.
 * @property array $Options Stream context.
 */
class Null extends \BLW\Type\AStream
{
    /**
     * Open flags
     *
     * @var int
     */
    protected $_Flags = IFile::FILE_FLAGS;

    /**
     * Stream context options.
     *
     * @link http://jp2.php.net/manual/en/function.stream-context-create.php stream_context_create()
     * @var array $_Options
     */
    protected $_Options;

    /**
     * Creates a stream from a file.
     *
     * @link http://www.php.net/manual/en/function.stream-context-create.php stream_context_create()
     *
     * @throws \BLW\Model\FileException If there is a problem opening the file.
     *
     * @param integer $flags
     *            Open flags.
     * @param array $options
     *            Stream context options.
     */
    public function __construct($flags = IFile::FILE_FLAGS, array $options = array())
    {
        // File
        $File = new GenericFile('php://memory');

        // Properties
        $this->_Flags   = intval($flags);
        $this->_Options = $options;
        $this->_fp      = $File->createResource($this->_Flags, stream_context_create($options));
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
     * Hook that is called just before an object is serialized.
     */
    public function doSerialize()
    {
        // flush
        @fflush($this->_fp);
        @rewind($this->_fp);

        // Resources don't serialize well
        $this->_fp = array(
            @ftell($this->_fp),
            $this->getContents()
        );
    }

    /**
     * Hook that is called just after an object is unserialized.
     */
    public function doUnSerialize()
    {
        // Recreate fp
        list($Pos, $Contents) = $this->_fp;

        $File      = new GenericFile('php://memory');
        $this->_fp = $File->createResource($this->_Flags, stream_context_create($this->_Options));

        // Restore fp
        if ($Contents) {
            $this->putContents($Contents);
            fseek($this->_fp, $Pos);
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
            // IStream
            case 'Flags':
                return $this->_Flags;
            case 'Options':
                return $this->_Options;

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
            case 'Flags':
                return $this->_Flags !== null;
            case 'Options':
                return $this->_Options !== null;
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
            case 'Flags':
            case 'Options':
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
        switch ($name) {
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
return true;
// @codeCoverageIgnoreEnd
