<?php
/**
 * AArgument.php | Mar 28, 2014
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

use ReflectionMethod;

use BLW\Type\IDataMapper;

use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericContainer;


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
 * Interface for Command\Input arguments.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | ARGUMENT                                          |<------| FACTORY          |
 * +---------------------------------------------------+       | ================ |
 * | _Value: string                                    |       | createFromArray  |
 * | #Value: string                                    |       | createFromString |
 * +---------------------------------------------------+       +------------------+
 * | createFromArray(): IContainer(Command\Argument)   |
 * |                                                   |
 * | $Arguments: array()                               |
 * +---------------------------------------------------+
 * | createString(): createFromArray()                 |
 * |                                                   |
 * | $Arguments: string                                |
 * +---------------------------------------------------+
 * | __construct():                                    |
 * |                                                   |
 * | $Value: string                                    |
 * +---------------------------------------------------+
 * | __toString(): string                              |
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
 * @property string $Value [dynamic] $_Value
 */
abstract class AArgument implements \BLW\Type\Command\IArgument
{

#############################################################################################
# Argument Trait
#############################################################################################

    /**
     * Value of argument.
     *
     * @var string $_Value
     */
    protected $_Value = '';

    /**
     * TAB / SPACE
     *
     * @var string[] $_WS
     */
    protected static $_WS = array(
        "\x09",
        "\x20"
    );

    /**
     * ASCII Control characers excluding whitespace characters / NL
     *
     * @var string[] $_NOWS_CTRL
     */
    protected static $_NOWS_CTRL = array(
        "\x00",
        "\x01",
        "\x02",
        "\x03",
        "\x04",
        "\x05",
        "\x06",
        "\x07",
        "\x0b",
        "\x0e",
        "\xd",
        "\x0f",
        "\x10",
        "\x11",
        "\x12",
        "\x13",
        "\x14",
        "\x15",
        "\x16",
        "\x17",
        "\x18",
        "\x19",
        "\x1a",
        "\x1b",
        "\x1c",
        "\x1d",
        "\x1e",
        "\x1f",
        "\x7f"
    );

#############################################################################################



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
            new ReflectionMethod(get_called_class(), 'createFromArray'),
            new ReflectionMethod(get_called_class(), 'createFromString')
        );
    }

    /**
     * Creates an instance of IContainer containing arguments parsed from an array.
     *
     * @link http://www.php.net/manual/en/reserved.variables.argv.php $argv
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Arguments</code> is not an <code>array</code> or instance of <code>Traversable</code>.
     *
     * @param array $Arguments
     *            Array of arguments similar to $argv.
     * @param array $NoValue
     *            Array of options that have no value.
     * @return \BLW\Type\IContainer Instance of <code>IContainer</code> containing parsed arguments.
     */
    public static function createFromArray($Arguments, array $NoValue = array())
    {
        // Return value
        $Container = new GenericContainer(IArgument::CLASSNAME);

        // Check if next argument is a descriptor or a value
        $CheckNext = function ($Name, &$Value) use(&$Arguments, &$NoValue)
        {

            // Check if next argument is a descriptor or a value
            $NextArgument = next($Arguments); prev($Arguments);

            if (! in_array($Name, $NoValue) &&      // Name of option not in $NoValue
                $Value === true &&                  // Value not parsed with equals sign
                $NextArgument !== false &&          // Next argument exists
                substr($NextArgument, 0, 1) != '-'  // Next argument is not an option
            ) {
                // Next argument
                next($Arguments);
            }
        };

        // Is $Arguments traversable?
        if (is_array($Arguments) ?: $Arguments instanceof \Traversable) {

            // Make sure we are dealing with an array
            if ($Arguments instanceof \Traversable)
                $Arguments = iterator_to_array($Arguments);

            // Sanitize array
            foreach ($Arguments as $k => $Value) {

                // Format arguments as strings
                $Value = strval($Value);

                // Does value exist? Update
                if (! empty($Value))
                    $Arguments[$k] = $Value;

                // No value. Delete from array
                else
                    unset($Arguments[$k]);
            }

            // Reindex keys
            $Arguments = array_values($Arguments);

            // Search through each value
            for (reset($Arguments), $Argument = current($Arguments); $Argument !== false; $Argument = next($Arguments)) {

                // Long option --<option>
                if (substr($Argument, 0, 2) == '--') {

                    $Name  = substr($Argument, 2);
                    $Value = true;

                    // Equals character --<option>=<value>
                    if (strpos($Name, '=') !== false) {
                        // Extract name and value
                        list ($Name, $Value) = explode('=', $Name, 2);

                        // Check value
                        if ($Value == null)
                            $Value = true;
                    }

                    // Check next argument for value
                    $CheckNext($Name, $Value);
                }

                // Option -<option> or /<option>
                elseif (substr($Argument, 0, 1) == '-' /* || substr($Argument, 0, 1) == '/'*/) {

                    $Name  = substr($Argument, 1, 1);
                    $Value = substr($Argument, 2) ?  : true;

                    // Check next argument for value
                    $CheckNext ($Name, $Value);
                }

                //Argument
                else
                    $Container[] = new static($Argument);
            }
        }

        // Invalid $Arguments
        else
            throw new InvalidArgumentException(0);

        // Done
        return $Container;
    }

    /**
     * Creates an instance of IContainer containing arguments parsed from a string.
     *
     * @uses \BLW\Type\Command\IOption::splitCommandLine() IOption::splitCommandLine()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Arguments</code> is not a <code>string</code>.
     *
     * @param strig $Arguments
     *            String containing commandline.
     * @param array $NoValue
     *            Array of options that have no value.
     * @return \BLW\Type\IContainer Instance of <code>IContainer</code> containing parsed arguments. Returns <code>FALSE</code> on error.
     */
    public static function createFromString($Arguments, array $NoValue = array())
    {
        // Is $Arguments scalar?
        if (is_string($Arguments) ?: is_callable(array(
            $Arguments,
            '__toString'
        ))) {

            // Create IContainer
            return self::createFromArray(AOption::splitCommandLine($Arguments), $NoValue);
        }

        // Invalid $Arguments
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
    }

###############################################################################################
# Argument Trait
###############################################################################################

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Value</code> is not <code>scalar</code>.
     *
     * @param string $Value Value of argument.
     */
    public function __construct($Value)
    {
        // Is $Value scalar?
        if (is_scalar($Value) ?: is_callable(array(
            $Value,
            '__toString'
        ))) {
            // Set value
            $this->_Value = strval($Value);
        }

        // Invalid $Value
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * All objects must have a string representation.
     *
     * @uses \escapeshellarg() escapeshellarg()
     *
     * @return string $this
     */
    public function __toString()
    {
        // Does value need encoding?
        if (preg_match('![\s\x21-\x2c\x3b\x60\x7c\x3b-\x3f\x5b-\x5d\x7b-\x7e\xFF]!', $this->_Value)) {
            // Add quotes and escape single quotes
            if (is_callable('escapeshellarg'))
                $Value = escapeshellarg($this->_Value);
            else
                $Value = "'" . str_replace("'", "'\\''", $this->_Value) . "'";
        }

        // No quotes necessarry
        else
            $Value = $this->_Value;

        // Format argument
        return $Value;
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
            // IArgument
            case 'Value':
                return $this->_Value;

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
            // IArgument
            case 'Value':
                return true;

            // Undefined property
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
        // Try to set property
        switch ($name) {
            // IArgument
            case 'Value':
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
            // IArgument
            case 'Value':
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;
        }
    }
}

return true;
