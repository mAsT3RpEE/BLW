<?php
/**
 * IFile.php | Jan 20, 2013
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
 * Interface for all file objects.
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
 * @property \SplFileObject|\SplFIleInfo $_Component [protected] Adapted class.
 * @property string $_MimeTypes [protected static] Associated array from file extention to mimetype.
 * @property string $_FileName [protected] $Name of file.
 * @property string $_OpenMode [protected] Mode passed to <code>IFile::openFile()</code>.
 * @property bool $_UseIncludePath [protected] Parameter passet to <code>IFile::openFile()</code>.
 */
interface IFile extends \BLW\Type\IWrapper, \BLW\Type\IFactory, \RecursiveIterator, \SeekableIterator
{
    // Open Modes
    const READ             = 0x0002;
    const WRITE            = 0x0004;
    const APPEND           = 0x0008;
    const TRUNCATE         = 0x0010;
    const USE_INCLUDE_PATH = 0x0020;
    const FILE_FLAGS       = 0x0022;

    /**
     * Returns the current element.
     *
     * @api BLW
     * @since 1.0.0
     * @link https://php.net/manual/en/iterator.current.php Iterator::current()
     *
     * @return mixed Curent element.
     */
    public function current();

    /**
     * Return the key of the current element.
     *
     * <h4>Note:</h4>
     *
     * <p>Returns <code>null</code> on failure.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     * @link https://php.net/manual/en/iterator.key.php Iterator::key()
     *
     * @return mixed|null Current key.
     */
    public function key();

    /**
     * Move forward to next element.
     *
     * @api BLW
     * @since 1.0.0
     * @link https://php.net/manual/en/iterator.next.php Iterator::next()
     */
    public function next();

    /**
     * Rewind the Iterator to the first element.
     *
     * @api BLW
     * @since 1.0.0
     * @link https://php.net/manual/en/iterator.rewind.php Iterator::rewind()
     */
    public function rewind();

    /**
     * Checks if current position is valid.
     *
     * @api BLW
     * @since 1.0.0
     * @link https://php.net/manual/en/iterator.valid.php Iterator::valid()
     *
     * @return The return value will be casted to boolean and then evaluated. Returns <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function valid();

    /**
     * Returns an iterator for the current entry.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/recursiveiterator.getchildren.php RecursiveIterator::getChildren()
     *
     * @return \RecursiveIterator An iterator for the current entry.
     */
    public function getChildren();

    /**
     * Returns if an iterator can be created fot the current entry.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/recursiveiterator.haschildren.php RecursiveIterator::hasChildren()
     *
     * @return bool <code>TRUE</code> If the current entry can be iterated over. <code>FALSE</code> otherwise.
     */
    public function hasChildren();

    /**
     * Seeks to a position.
     *
     * @link http://www.php.net/manual/en/seekableiterator.seek.php SeekableIterator::seek()
     * @param int $position
     *            The position to seek to.
     */
    public function seek($position);

    /**
     * Creates a file resource if file is opened.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     *
     * @return resource file pointer resource on success. <code>FALSE</code> on error.
     */
    public function createResource($flags = IFile::FILE_FLAGS, $context = null);

    /**
     * Returns the mime type of the file
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string Mimetype
     */
    public function getMimeType();

    /**
     * Reads entire file into a string.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://us1.php.net/manual/en/function.file-get-contents.php file_get_contents()
     *
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     * @return string Contents of file. <code>FALSE</code> on failure
     */
    public function getContents($flags = IFile::FILE_FLAGS, $context = null);

    /**
     * Write a string to a file.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://us1.php.net/manual/en/function.file-put-contents.php file_put_contents()
     *
     * @param string|array|resource $data
     *            The data to write. Can be either a string, an array or a stream resource.
     * @param resource $context
     *            Stream context.
     * @return int Returns the number of bytes actually written.
     */
    public function putContents($data, $context = null);

    /**
     * Opens file and changes component to SplFileObject.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @throws \BLW\Model\FileException if there is an error opening file.
     *
     * @param int $flags
     *            Open flags.
     * @param resource $context
     *            Stream context.
     * @return bool <code>TRUE</code> on success. <code>FALSE</code> otherwise.
     */
    public function openFile($flags = IFile::FILE_FLAGS, $context = null);

    /**
     * Closes file and changes component to SplFileInfo.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return bool <code>TRUE</code> if successfull. <code>FALSE</code> otherwise.
     */
    public function closeFile();

    /**
     * Checks wheter current file has been opened.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return bool <code>TRUE</code> if open. <code>FALSE</code> Otherwise.
     */
    public function isOpen();

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 0.1.0
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
