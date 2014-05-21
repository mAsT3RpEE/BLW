<?php
/**
 * AInput.php | Mar 30, 2014
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\Command;

use BLW\Type\IStream;
use BLW\Type\IDataMapper;
use BLW\Type\IMediator;
use BLW\Type\IContainer;
use BLW\Type\ADataMapper;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericEvent;

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
 * Interface for command input objects passed to ICommand::run()
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------+
 * | COMMAND\INPUT                                     |<------| MEDIATABLE |
 * +---------------------------------------------------+       +------------+
 * | _Arguments:  IContainer(Command\Argument)         |
 * | _Options:    IContainer(Command\Option)           |
 * | _InStream:   IStream|null                         |
 * | #Arguments:  _Arguments                           |
 * | #Options:    _Options                             |
 * | #stdIn       _InStream                            |
 * +---------------------------------------------------+
 * | getArgument(): Command\Argument|null              |
 * |                                                   |
 * | $index: int                                       |
 * +---------------------------------------------------+
 * | setArgument(): IDataMapper::STATUS                |
 * |                                                   |
 * | $index:  int                                      |
 * | $value:  Input\Argument                           |
 * +---------------------------------------------------+
 * | getOption(): Command\Option|null                  |
 * |                                                   |
 * | $name:  string                                    |
 * +---------------------------------------------------+
 * | setOption():  IDataMapper::STATUS                 |
 * |                                                   |
 * | $name:   string                                   |
 * | $value:  Input\Option                             |
 * +---------------------------------------------------+
 * | setMediatorID: IDataMapper::STATuS                |
 * |                                                   |
 * | $ID:  string                                      |
 * +---------------------------------------------------+
 * | read(): fread(_Stream->fp)                        |
 * |                                                   |
 * | $bytes:  int                                      |
 * +---------------------------------------------------+
 * | readline(): fgets(_Stream->fp)                    |
 * |                                                   |
 * | $bytes:  int                                      |
 * +---------------------------------------------------+
 * | __toString():  string                             |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Command
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IContainer $Arguments [readonly] $_Arguments
 * @property \BLW\Type\IContainer $Options [readonly] $_Options
 * @property \BLW\Type\IStream $stdIn [readonly] $_Instream
 */
abstract class AInput extends \BLW\Type\AMediatable implements \BLW\Type\Command\IInput
{

    /**
     * Input arguments.
     *
     * @var \BLW\Type\IContainer $_Arguments
     */
    protected $_Arguments = null;

    /**
     * Input options.
     *
     * @var \BLW\Type\IContainer $_Options
     */
    protected $_Options = null;

    /**
     * Input stream.
     *
     * @var \BLW\Type\IStream $_InStream
     */
    protected $_InStream = null;

    /**
     * Return argument from command input.
     *
     * @param integer $index
     *            Argument position.
     * @return \BLW\Type\Command\IArgument Returns argument at position <code>$index</code>. Returns <code>FALSE</code> on error.
     */
    public function getArgument($index)
    {
        // Does argument exist? Return it.
        if (isset($this->_Arguments[$index])) {
            return $this->_Arguments[$index];

        // Error
        } else {
            return false;
        }
    }

    /**
     * Set an argument in a command input.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$name</code> is not an integer.
     *
     * @param integer $index
     *            Argument position.
     * @param IArgument $value
     *            New value of argument
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setArgument($index, IArgument $value)
    {
        // Is $index scalar?
        if (! is_int($index)) {
            throw new InvalidArgumentException(0);
        }

        // Update argument
        $this->_Arguments[$index] = $value;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Return option from command input.
     *
     * @param string $name
     *            Option switch / label.
     * @return \BLW\Type\Command\IOption|null Returns argument matching <code>$name</code>. Returns <code>NULL</code> on error.
     */
    public function getOption($name)
    {
        // Does option exist? Return it.
        if (isset($this->_Options[$name])) {
            return $this->_Options[$name];
        }

        // Error
        return null;
    }

    /**
     * Set an option in a command input.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$name</code> is not a string.
     *
     * @param string $name
     *            Option switch / label.
     * @param IOption $value
     *            New value of switch
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setOption($name, IOption $value)
    {
        // Is $name scalar?
        if (! is_string($name) && ! is_callable(array(
            $name,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);
        }

        // Update option
        $this->_Options[strval($name)] = $value;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Used to change mediator id of input to attatch its actions to its command at runtime.
     *
     * @param string $ID
     *            Unique id used to identify object in mediator.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setMediatorID($ID)
    {
        // Is $ID scalar?
        if (is_string($ID) ?: is_callable(array(
            $ID,
            '__toString'
        ))) {

            // Update ID
            $this->_MediatorID = strval($ID);

            // Done
            return IDataMapper::UPDATED;

        // Error
        } else {
            return IDataMapper::INVALID;
        }
    }

    /**
     * Read from input stream.
     *
     * <h4>Note</h4>
     *
     * <p>Function is also responsible for dispatching onInput hook.</p>
     *
     * <p>onInput hook is called after data has been written and can modify data returned.</p>
     *
     * <hr>
     *
     * @param integer $bytes
     *            Maximum bytes to read.
     * @return string Data read. <code>FALSE</code> on Error / EOF.
     */
    public function read($bytes)
    {
        // Does input stream exist?
        if ($this->_InStream instanceof IStream) {

            // Read Data
            $return = fread($this->_InStream->fp, $bytes) ?  : false;

            // Does mediator exist?
            if ($this->_Mediator instanceof IMediator) {

                // Dispatch event
                $this->_do('Input', new GenericEvent($this, array(
                    'Bytes' => &$bytes,
                    'Data' => &$return
                )));
            }

            // Return read data
            return $return;
        }

        // Error
        return false;
    }

    /**
     * Read a line from input stream.
     *
     * <h4>Note</h4>
     *
     * <p>Function is also responsible for dispatching onInput hook.</p>
     *
     * <hr>
     *
     * @param integer $bytes
     *            Maximum bytes to read.
     * @return string Data read. <code>FALSE</code> on Error / EOF.
     */
    public function readline($bytes)
    {
        // Does mediator exist?
        if ($this->_Mediator instanceof IMediator) {

            // Dispatch event
            $this->_do('Input', new GenericEvent($this, array(
                'bytes' => &$bytes
            )));
        }

        // Does input stream exist?
        if ($this->_InStream instanceof IStream) {

            // Return read data
            return fgets($this->_InStream->fp, $bytes);
        }

        // Error
        return false;
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
        // Does input stream exist?
        if ($this->_InStream instanceof IStream) {

            // Return stream contents
            return $this->_InStream->getContents(1024 * 1024);
        }

        // Error
        return '';
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
            // IInput
            case 'Arguments':
                return $this->_Arguments;
            case 'Options':
                return $this->_Options;
            case 'stdIn':
                return $this->_InStream;
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
            // IMediatable
            case 'Mediator':
                return $this->getMediator() !== null;
            case 'MediatorID':
                return $this->getMediatorID() !== null;
            // IInput
            case 'Arguments':
                return $this->_Arguments !== null;
            case 'Options':
                return $this->_Options !== null;
            case 'stdIn':
                return $this->_InStream !== null;
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
            // IMediatable
            case 'Mediator':
                $result = $this->setMediator($value);
                break;
            case 'MediatorID':
                $result = $this->setMediatorID($value);
                break;
            // IInput
            case 'Arguments':
            case 'Options':
            case 'stdIn':
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
            // IMediatable
            case 'Mediator':
                $this->clearMediator();
                break;
            case 'MediatorID':
                $this->setMediatorID('*');
                break;
            // IInput
            case 'Arguments':
            case 'Options':
            case 'stdIn':
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;
            // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
