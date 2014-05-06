<?php
/**
 * IOption.php | Mar 28, 2014
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
use BLW\Type\IFactory;


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
 * Interface for Command\Input Options.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | COMMAND\OPTION                                    |<------| FACTORY          |
 * +---------------------------------------------------+       | ================ |
 * | _Name:   string                                   |       | createFromArray  |
 * | _Value:  string                                   |       | createFromString |
 * | #Name:   _ID                                      |       +------------------+
 * | #Value:  _Value                                   |
 * +---------------------------------------------------+
 * | createFromArray(): IContainer(Command\Argument)   |
 * |                                                   |
 * | $Arguments:  array()                              |
 * +---------------------------------------------------+
 * | createFromString(): createFromArray()             |
 * |                                                   |
 * | $Arguments:  string                               |
 * +---------------------------------------------------+
 * | splitCommandLine(): array                         |
 * |                                                   |
 * | $Arguments:  string                               |
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
 * @property string $_Name [protected] Label of option.
 * @property string $_Value [protected] Value of option.
 * @property string $Name [readonly] $_Name
 * @property string $Value [readonly] $_Value
 */
Interface IOption extends \BLW\Type\IFactory
{

    const CLASSNAME = '\\BLW\\Type\\Command\\IOption';

    /**
     * Creates an instance of IContainer containing options parsed from an array.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/reserved.variables.argv.php $argv
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Arguments</code> is not an <code>array</code> or instance of <code>Traversable</code>.
     *
     * @param array $Arguments
     *            Array of arguments similar to $argv.
     * @param array $NoValue
     *            Array of options that have no value.
     * @return \BLW\Type\IContainer Instance of <code>IContainer</code> containing parsed options.
     */
    public static function createFromArray($Arguments, array $NoValue = array());

    /**
     * Split a Command line string into its various components.
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/reserved.variables.argv.php $argv
     * @uses \BLW\Type\Command\IOption::splitCommandLine() IOption::splitCommandLine()
     *
     * @param string $Arguments
     *            Command line
     * @return array Split command line.
     */
    public static function createFromString($Arguments);

    /**
     * Split a Command line string into its various components.
     *
     * <h4>Note</h4>
     *
     * <p>Supports:</p>
     *
     * <pre>
     * -o
     * -o &lt;value&gt
     * -o&lt;value&gt
     * -o "&lt;value&gt"
     * -o"&lt;value&gt"
     * -o '&lt;value&gt'
     * -o'&lt;value&gt'
     * --option &lt;value&gt
     * --option=&lt;value&gt
     * --option "&lt;value&gt"
     * --option"&lt;value&gt"
     * --option="&lt;value&gt"
     * --option= "&lt;value&gt"
     * --option '&lt;value&gt'
     * --option'&lt;value&gt'
     * --option='&lt;value&gt'
     * --option= '&lt;value&gt'
     * </pre>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     * @link http://www.php.net/manual/en/reserved.variables.argv.php $argv
     *
     * @param string $Arguments
     *            Command line
     * @return array Split command line.
     */
    public static function splitCommandLine($Arguments);

    /**
     * All objects must have a string representation.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string $this
     */
    public function __toString();
}

return true;
