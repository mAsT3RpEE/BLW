<?php
/**
 * AOutput.php | Mar 30, 2014
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
 * @package BLW\Command
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Command;

use BLW\Type\IMediatable;
use BLW\Type\IDataMapper;
use BLW\Type\IStream;
use BLW\Type\IMediator;

use BLW\Model\Event\Generic as GenericEvent;
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
 * Base class for command output objects passed to ICommand::run()
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------+
 * | COMMAND\OUTPUT                                    |<------| MEDIATABLE |
 * +---------------------------------------------------+       +------------+
 * | STDOUT                                            |
 * | STDERR                                            |
 * +---------------------------------------------------+
 * | _OutStream:  IStream                              |
 * | _ErrStream:  IStream                              |
 * | #stdOut      _OutStream                           |
 * | #stdErr      _ErrStream                           |
 * +---------------------------------------------------+
 * | setMediatorID: IDataMapper::STATuS                |
 * |                                                   |
 * | $ID:  string                                      |
 * +---------------------------------------------------+
 * | write(): fwrite(_OutStream->fp)                   |
 * |          fwrite(_ErrStream->fp)                   |
 * |                                                   |
 * | $string:  string                                  |
 * | $flags:   IOutput::WRITE_FLAGS                    |
 * +---------------------------------------------------+
 * | __toString():  string                             |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Command
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IStream $stdOut [readonly] $_Outstream
 * @property \BLW\Type\IStream $stdErr [readonly] $_Errstream
 */
abstract class AOutput extends \BLW\Type\AMediatable implements \BLW\Type\Command\IOutput
{

    /**
     * Output stream.
     *
     * @var \BLW\Type\IStream $_OutStream
     */
    protected $_OutStream = null;

    /**
     * Error stream.
     *
     * @var \BLW\Type\IStream $_ErrStream
     */
    protected $_ErrStream = null;

    /**
     * Stores the length of last line written by write().
     *
     * @var int $_LastLineLength
     */
    private $_LastLineLength = 0;

    /**
     * Used to change mediator id of Output to attatch its actions to its command at runtime.
     *
     * @param string $ID
     *            Unique id used to identify object in mediator.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setMediatorID($ID)
    {
        // Is $ID scalar?
        if (is_string($ID) ?  : is_callable(array(
            $ID,
            '__toString'
        ))) {

            // Update ID
            $this->_MediatorID = strval($ID);

            // Done
            return IDataMapper::UPDATED;
        }

        // Error
        return IDataMapper::INVALID;
    }

    /**
     * Write to Output stream.
     *
     * <h4>Note</h4>
     *
     * <p>Function is also responsible for dispatching onOutput and onError hook.</p>
     *
     * <p>onOutput / onError hooks are called before writing and can update data.</p>
     *
     * <hr>
     *
     * @event IOuptut.Output
     * @event IOuptut.Error
     *
     * @param string $string
     *            Data to write.
     * @param int $flags
     *            Write flags.
     * @return int Bytes written. <code>FALSE</code> on Error.
     */
    public function write($string, $flags = IOutput::WRITE_FLAGS)
    {
        // Is $string a string?
        if (is_string($string) ?: is_callable(array(
            $string,
            '__toString'
        ))) {

            $string = strval($string);
            $return = 0;

            // Is output stream set?
            if (($flags & IOutput::STDOUT) && $this->_OutStream instanceof IStream) {

                // Output
                $size = fwrite($this->_OutStream->fp, $string);

                // Is mediator set?
                if ($this->_Mediator instanceof IMediator) {
                    // Output event
                    $this->_do('Output', new GenericEvent($this, array(
                        'Bytes' => &$size,
                        'Data'  => $string
                    )));
                }

                // Update byte count
                $return += $size;
            }

            // Is error stream set?
            if (($flags & IOutput::STDERR) && $this->_ErrStream instanceof IStream) {

                // Error
                $size = fwrite($this->_ErrStream->fp, $string);

                // Is mediator set?
                if ($this->_Mediator instanceof IMediator) {
                    // Error Event
                    $this->_do('Error', new GenericEvent($this, array(
                        'Bytes' => &$size,
                        'Data'  => $string
                    )));
                }

                // Update byte count
                $return += $size;
            }

            // Update line length
            if ($return) {

                // Look for last position of CR / NL or start @ 0
                $string                = rtrim($string);
                $Start                 = max(strrpos($string, "\r"), strrpos($string, "\n")) + 1;
                $this->_LastLineLength = strlen($string) - $Start;
            }

            // Done
            return $return ?: false;
        }

        // Invalid $string
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
    }

    /**
     * Rewrite to Output stream (replace last write).
     *
     * @see \BLW\Type\Command\IOutput::write() IOutput::write()
     *
     * @param string $string
     *            Data to write.
     * @param int $flags
     *            Write flags.
     * @return int Bytes written. <code>FALSE</code> on Error.
     */
    public function overwrite($string, $flags = IOutput::WRITE_FLAGS)
    {
        // Pad Message to overwite previous message with spaces
        $string = str_pad($string, $this->_LastLineLength, "\x20", STR_PAD_RIGHT);

        // Carriage Return
        $this->write("\x0d", $flags);

        // Overwirte last output
        return $this->write($string, $flags);
    }

    /**
     * All objects must have a string representation.
     *
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString()
    {
        return sprintf('[Command\\Output:%s]', basename(get_class($this)));
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
            // IMediatable
            case 'Mediator':
                return $this->getMediator();
            case 'MediatorID':
                return $this->getMediatorID();
            // IOutput
            case 'stdOut':
                return $this->_OutStream;
            case 'stdErr':
                return $this->_ErrStream;
            // Undefined property
            default:
                trigger_error(sprintf('Undefined property %s::$%s', get_class($this), $name), E_USER_NOTICE);
        }

        // Default
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
            // IMediatable
            case 'Mediator':
                return $this->getMediator() !== null;
            case 'MediatorID':
                return $this->getMediatorID() !== null;
            // IOutput
            case 'stdOut':
                return $this->_OutStream !== null;
            case 'stdErr':
                return $this->_ErrStream !== null;
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
     * @return bool Returns a <code>IDataMapper</code> status code.
     */
    public function __set($name, $value)
    {
        // Try to set property
        switch ($name) {
            // IMediatable
            case 'Mediator':
                $result = $this->setMediator($value);
                break;
            case 'MediatorID':
                $result = $this->setMediatorID($value);
                break;
            // IOutput
            case 'stdOut':
            case 'stdErr':
                $result = IDataMapper::READONLY;
                break;
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }

        // Check results
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
    }

    /**
     * Unmap dynamic properties from DataMapper.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     * @return bool Returns a <code>TRUE</code> if property exists. <code>FALSE</code> otherwise.
     */
    public function __unset($name)
    {
        // Try to set property
        switch ($name) {
            // IMediatable
            case 'Mediator':
                $result = $this->clearMediator();
                break;
            case 'MediatorID':
                $result = $this->setMediatorID('*');
                break;
            // IInput
            case 'stdOut':
            case 'stdErr':
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;
            // Undefined property
            default:
                $result = IDataMapper::UNDEFINED;
        }
    }
}

return true;
