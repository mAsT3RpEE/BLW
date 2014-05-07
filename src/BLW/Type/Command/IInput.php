<?php
/**
 * IInput.php | Mar 30, 2014
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
 * | $index:  int                                      |
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
 * | setOption(): IDataMapper::STATUS                  |
 * |                                                   |
 * | $name:   string                                   |
 * | $value:  Input\Option                             |
 * +---------------------------------------------------+
 * | setMediatorID: IDataMapper::STATUS                |
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
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IContainer $_Arguments [protected] Input arguments.
 * @property \BLW\Type\IContainer $_Options [protected] Input options.
 * @property \BLW\Type\IStream $_InStream [protected] Input stream.
 * @property \BLW\Type\IContainer $Arguments [readonly] $_Arguments
 * @property \BLW\Type\IContainer $Options [readonly] $_Options
 * @property \BLW\Type\IStream $stdIn [readonly] $_Instream
 */
Interface IInput extends \BLW\Type\IMediatable
{

    /**
     * Return argument from command input.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param int $index
     *            Argument position.
     * @return \BLW\Type\Command\IArgument Returns argument at position <code>$index</code>. Returns <code>FALSE</code> on error.
     */
    public function getArgument($index);

    /**
     * Set an argument in a command input.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$name</code> is not an integer.
     *
     * @param int $index
     *            Argument position.
     * @param IArgument $value
     *            New value of argument
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setArgument($index, IArgument $value);

    /**
     * Return option from command input.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $name
     *            Option switch / label.
     * @return \BLW\Type\Command\IOption Returns option matching <code>$name</code>. Returns <code>FALSE</code> on error.
     */
    public function getOption($name);

    /**
     * Set an option in a command input.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$name</code> is not a string.
     *
     * @param string $name
     *            Option switch / label.
     * @param IOption $value
     *            New value of switch
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setOption($name, IOption $value);

    /**
     * Used to change mediator id of input to attatch its actions to its command at runtime.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $ID
     *            Unique id used to identify object in mediator.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setMediatorID($ID);

    /**
     * Read from input stream.
     *
     * <h4>Note</h4>
     *
     * <p>Function is also responsible for dispatching onInput hook.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param int $bytes
     *            Maximum bytes to read.
     * @return string Data read. <code>FALSE</code> on Error / EOF.
     */
    public function read($bytes);

    /**
     * Read a line from input stream.
     *
     * <h4>Note</h4>
     *
     * <p>Function is also responsible for dispatching onInput hook.</p>
     *
     * <hr>
     *
     * @api BLW
     *
     * @since 1.0.0
     * @param int $bytes
     *            Maximum bytes to read.
     * @return string Data read. <code>FALSE</code> on Error / EOF.
     */
    public function readline($bytes);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.tostring __toString()
     *
     * @return string $this
     */
    public function __toString();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
