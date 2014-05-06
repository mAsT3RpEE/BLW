<?php
/**
 * AFile.php | Jan 20, 2013
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
namespace BLW\Type;

use ReflectionMethod;
use SplFileInfo;
use SplFileObject;

use BLW\Model\InvalidArgumentException;
use BLW\Model\FileException;


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
 * Abstract class for all file objects.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +--------------------+       +--------------------+
 * | FILE                                              |<------| WRAPPER            |<--+---| SERIALIZABLE       |
 * +---------------------------------------------------+       | ================== |   |   | ================== |
 * | $_MimeTypes:  string[]                            |       | SplFileInfo        |   |   | Serializable       |
 * | $_FileName:   string                              |       | SplFileObject      |   |   +--------------------+
 * | $_Flags:      int                                 |       +--------------------+   +---| COMPONENTMAPABLE   |
 * +---------------------------------------------------+       | RecursiveIterator  |   |   +--------------------+
 * | createResource(): file resource                   |       +--------------------+   +---| ITERABLE           |
 * +---------------------------------------------------+       | SeekableIterator   |       +--------------------+
 * | openFile(): bool                                  |       +--------------------+
 * |                                                   |       | FACTORY            |
 * | $flags:    IFile::FLAGS                           |       | ================== |
 * | $context:  context resource|null                  |       | createResource     |
 * +---------------------------------------------------+       +--------------------+
 * | closeFile(): bool                                 |
 * +---------------------------------------------------+
 * | isOpen(): bool                                    |
 * +---------------------------------------------------+
 * | getMimeType(): string                             |
 * +---------------------------------------------------+
 * | getContents(): string                             |
 * |                                                   |
 * | $flags:    int                                    |
 * | $context:  context resource                       |
 * +---------------------------------------------------+
 * | putCOntents(): bool                               |
 * |                                                   |
 * | $Data:     string|stream|array                    |
 * | $context:  resource                               |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 * @link http://mast3rpee.tk/projects/BLW/ BLW Library
 * @link https://php.net/manual/en/class.iterator.php Iterator
 * @link https://php.net/manual/en/class.recursiveiterator.php RecursiveIterator
 * @link https://php.net/manual/en/class.recursiveiterator.php SeekableIterator
 *
 * @property \SplFileInfo $_Component Adapted class.
 * @property string $_FileName
 */
abstract class AFile extends \BLW\Type\AWrapper implements \BLW\Type\IFile
{

#############################################################################################
# File Trait
#############################################################################################

    /**
     * Associated array from file extention to mimetype.
     *
     * @var string $_MimeTypes
     */
    protected static $_MimeTypes = array(

        // Web related
        'txt'  => 'text/plain',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'php'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'swf'  => 'application/x-shockwave-flash',
        'flv'  => 'video/x-flv',
        // images
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        'exe'  => 'application/x-msdownload',
        'msi'  => 'application/x-msdownload',
        'cab'  => 'application/vnd.ms-cab-compressed',
        // audio/video
        'aif'  => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'mp3'  => 'audio/mpeg',
        'wma'  => 'audio/x-ms-wma',
        'wmv'  => 'audio/x-ms-wmv',
        'avi'  => 'video/avi',
        'mov'  => 'video/quicktime',
        'qt'   => 'video/quicktime',
        // adobe
        'pdf'  => 'application/pdf',
        'psd'  => 'image/vnd.adobe.photoshop',
        'ai'   => 'application/postscript',
        'eps'  => 'application/postscript',
        'ps'   => 'application/postscript',
        // ms office
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'rtf'  => 'application/rtf',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',
        // open office
        'odg'  => 'application/vnd.oasis.opendocument.graphics',
        'odp'  => 'application/vnd.oasis.opendocument.presentation',
        'odt'  => 'application/vnd.oasis.opendocument.text',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    );

    /**
     * Full path of file.
     *
     * @var string $_FileName
     */
    protected $_FileName = '';

    /**
     * File opening flags.
     *
     * @var int $_Flags
     */
    protected $_Flags = 0;

#############################################################################################




#############################################################################################
# Iterator Trait
#############################################################################################

    /**
     * Returns the current element.
     *
     * @link www.php.net/manual/en/splfileobject.current.php SplFileObject::current()
     *
     * @return mixed Curent element.
     */
    public function current()
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->current();

        // No?
        else
            trigger_error('IFile::current() should only be called after IFile::openFile()', E_USER_NOTICE);
    }

    /**
     * Return the key of the current element.
     *
     * <h4>Note:</h4>
     *
     * <p>Returns <code>null</code> on failure.</p>
     *
     * <hr>
     *
     * @link www.php.net/manual/en/splfileobject.key.php SplFileObject::key()
     *
     * @return mixed|null Current key.
     */
    public function key()
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->key();

        // No?
        else
            trigger_error('IFile::key() should only be called after IFile::openFile()', E_USER_NOTICE);
    }

    /**
     * Move forward to next element.
     *
     * @link www.php.net/manual/en/splfileobject.next.php SplFileObject::next()
     */
    public function next()
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->next();

        // No?
        else
            trigger_error('IFile::next() should only be called after IFile::openFile()', E_USER_NOTICE);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @link www.php.net/manual/en/splfileobject.rewind.php SplFileObject::rewind()
     */
    public function rewind()
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->rewind();

        // No?
        else
            trigger_error('IFile::rewind() should only be called after IFile::openFile()', E_USER_NOTICE);
    }

    /**
     * Checks if current position is valid.
     *
     * @link www.php.net/manual/en/splfileobject.valid.php SplFileObject::valid()
     *
     * @return bool The return value will be casted to boolean and then evaluated. Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function valid()
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->valid();

        // No?
        else
            trigger_error('IFile::valid() should only be called after IFile::openFile()', E_USER_NOTICE);
    }

#############################################################################################
# RecursiveIterator Trait
#############################################################################################

    /**
     * Returns an iterator for the current entry.
     *
     * @link www.php.net/manual/en/splfileobject.getchildren.php SplFileObject::getChildren()
     *
     * @return \RecursiveIterator An iterator for the current entry.
     */
    public function getChildren()
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->getChildren();

        // No?
        else
            trigger_error('IFile::getChildren() should only be called after IFile::openFile()', E_USER_NOTICE);
    }

    /**
     * Returns if an iterator can be created fot the current entry.
     *
     * @link www.php.net/manual/en/splfileobject.haschildren.php SplFileObject::hasChildren()
     *
     * @return bool <code>TRUE</code> If the current entry can be iterated over. <code>FALSE</code> otherwise.
     */
    public function hasChildren()
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->hasChildren();

        // No?
        else
            trigger_error('IFile::hasChildren() should only be called after IFile::openFile()', E_USER_NOTICE);
    }

#############################################################################################
# SeekableIterator Trait
#############################################################################################

    /**
     * Seeks to a position.
     *
     * @link www.php.net/manual/en/splfileobject.seek.php SplFileObject::seek()
     *
     * @param int $position
     *            The position to seek to.
     */
    public function seek($position)
    {
        // Is file opened?
        if ($this->_Component instanceof SplFileObject)
            return $this->_Component->seek($position);

        // No?
        else
            trigger_error('IFile::seek() should only be called after IFile::openFile()', E_USER_NOTICE);
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
            new ReflectionMethod(get_called_class(), 'createResource')
        );
    }

    /**
     * Creates a file resource if file is opened.
     *
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     *
     * @return resource file pointer resource on success. <code>FALSE</code> on error.
     */
    public function createResource($flags = IFile::FILE_FLAGS, $context = null)
    {
        $UseIncludePath = (bool) $flags & IFile::USE_INCLUDE_PATH;

        if ($context) {
            return @fopen($this->getPathname(), $this->buildMode($flags), $UseIncludePath, $context);
        }

        return @fopen($this->getPathname(), $this->buildMode($flags), $UseIncludePath);
    }

#############################################################################################
# File Trait
#############################################################################################

    /**
     * Constructor
     *
     * @throws \BLW\Type\InvalidArgumentException If $File is not a string / SplFileInfo.
     *
     * @param string|\SplFileInfo $File
     *            Path of file.
     * @param int $flags
     *            Creation flags.
     */
    public function __construct($File, $flags = IWrapper::WRAPPER_FLAGS)
    {
        // File is and instanceof SplFileInfo
        if ($File instanceof SplFileInfo) {

            // Clone object
            $this->_Component = clone $File;
        }

        // File is a string (This includes IFile)
        elseif (is_string($File) ?: is_callable(array(
            $File,
            '__toString'
        )))
            $this->_Component = new SplFileInfo(strval($File));

        // File is invalid
        else
            throw new InvalidArgumentException(0);

        // Save Path
        $this->_FileName = $this->_Component->getPathname();
    }

    /**
     * Returns the mime type of the file
     *
     * @return string Mimetype
     */
    public function getMimeType()
    {
        // Get mimetype by finfo
        if (is_callable('finfo_open') && is_callable('finfo_close') && is_callable('finfo_file')) {
            $Finfo    = finfo_open(FILEINFO_MIME);
            $MimeType = finfo_file($Finfo, $this->_Component->getPathname());

            finfo_close($Finfo);

            return $MimeType;
        }

        // Get mimetype by mime_content_type
        elseif (is_callable('mime_content_type'))
            return mime_content_type($this->_Component->getPathname());

        // Get mimetype by extention
        elseif (array_key_exists($Extention = strtolower($this->_Component->getExtension()), self::$_MimeTypes))
            return self::$_MimeTypes[$Extention];

        // Default mimetype
        return 'application/octet-stream';
    }

    /**
     * Reads entire file into a string.
     *
     * @link http://us1.php.net/manual/en/function.file-get-contents.php file_get_contents()
     *
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     * @return string Contents of file. <code>FALSE</code> on failure
     */
    public function getContents($flags = IFile::FILE_FLAGS, $context = array())
    {
        // Use include path?
        $UseIncludePath = (bool) $flags & IFile::USE_INCLUDE_PATH;

        // Open file with context
        if ($context)
            return @file_get_contents($this->_Component->getPathname(), $UseIncludePath, $context);

        // Open file without context
        return @file_get_contents($this->_Component->getPathname(), $UseIncludePath);
    }

    /**
     * Write a string to a file.
     *
     * @link http://us1.php.net/manual/en/function.file-put-contents.php file_put_contents()
     *
     * @param string|array|resource $data
     *            The data to write. Can be either a string, an array or a stream resource.
     * @param resource $context
     *            Stream context.
     * @return int Returns the number of bytes actually written.
     */
    public function putContents($data, $context = array())
    {
        // Is data valid?
        if (is_string($data) || is_array($data) || is_resource($data)) {

            // Put contents with context
            if ($context)
                return @file_put_contents($this->_Component->getPathname(), $data, FILE_USE_INCLUDE_PATH, $context);

            // Put contents without context
            return @file_put_contents($this->_Component->getPathname(), $data, FILE_USE_INCLUDE_PATH);
        }

        // Invalid data
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * Creates an open mode from open flags.
     *
     * @link http://www.php.net/manual/en/function.fopen.php fopen()
     *
     * @param int $flags
     *            Open flags.
     * @return string Open Mode.
     */
    public function buildMode($flags)
    {
        switch ($flags & ~ IFile::USE_INCLUDE_PATH) {
            case IFile::READ | IFile::WRITE:
                return 'r+';
            case IFile::WRITE | IFile::APPEND:
                return 'a';
            case IFile::READ | IFile::WRITE | IFile::APPEND:
                return 'a+';
            case IFile::WRITE | IFile::TRUNCATE:
                return 'w';
            case IFile::READ | IFile::WRITE | IFile::TRUNCATE:
                return 'w+';
            default:
                return 'r';
        }
    }

    /**
     * Opens file and changes component to SplFileObject.
     *
     * @throws \BLW\Model\FileException if there is an error opening file.
     *
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     * @return bool <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function openFile($flags = IFile::FILE_FLAGS, $context = null)
    {
        // Use include path?
        $UseIncludePath = (bool) $flags & IFile::USE_INCLUDE_PATH;

        // Create SplFile Object
        try {

            // Create object with context
            if ($context)
                $this->_Component = $this->_Component->openFile($this->buildMode($flags), $UseIncludePath, $context);

            // Create object without context
            else
                $this->_Component = $this->_Component->openFile($this->buildMode($flags), $UseIncludePath);

            // Save flags used
            $this->_Flags = $flags;

            // Done
            return true;
        }

        // Error
        catch (\Exception $e) {
            throw new FileException($this->_Component->getPathname(), null, 0, $e);
        }

        // Done
        return false;
    }

    /**
     * Closes file and changes component to SplFileInfo.
     *
     * @return bool <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function closeFile()
    {
        // Is component opened?
        if ($this->_Component instanceof SplFileObject) {

            // Delete SplFileObject and create SplFileInfo
            $this->_Component = $this->_Component->getFileInfo();
            $this->_Flags     = 0;

            // Done
            return true;
        }

        // Done
        return false;
    }

    /**
     * Checks wheter current file has been opened.
     *
     * @return bool <code>TRUE</code> if open. <code>FALSE</code> Otherwise.
     */
    public function isOpen()
    {
        return $this->_Component instanceof SplFileObject;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string String value of object.
     */
    public function __toString()
    {
        return $this->_Component->getPathname();
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return md5($this->__toString());
    }

    /**
     * Hook that is called just before an object is serialized.
     *
     * @return \BLW\Type\Serializable $this
     */
    public function doSerialize()
    {
        // SplFIleObject and SplFileInfo cannot be serialized
        $this->_Component = (array) $this->_Component;
    }

    /**
     * Hook that is called just after an object is unserialized.
     */
    public function doUnSerialize()
    {
        // Save properties
        $Propterties = $this->_Component;

        // Recreate SplFileInfo
        $this->_Component = new SplFileInfo($this->_FileName);

        // Restore properties
        array_walk($Propterties, function ($v, $k)
        {
            try {
                $this->_Component->{$k} = $v;
            }

            catch (\Exception $e) {}
        });

        // Reopen file
        if (!empty($this->_Flags))
            $this->openFile($this->_Flags);
    }

#############################################################################################

}

return true;
