<?php
/**
 * AOption.php | Mar 28, 2014
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
 * Interface for Command\Input options.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+
 * | COMMAND\ARGUMENT                                  |<------| FACTORY          |
 * +---------------------------------------------------+       | ================ |
 * | _Value:  string                                   |       | createFromArray  |
 * | #Value:  string                                   |       | createFromString |
 * +---------------------------------------------------+       +------------------+
 * | createFromArray(): IContainer(Command\Argument)   |
 * |                                                   |
 * | $Arguments:  array()                              |
 * +---------------------------------------------------+
 * | createString(): createFromArray()                 |
 * |                                                   |
 * | $Arguments:  string                               |
 * +---------------------------------------------------+
 * | __construct():                                    |
 * |                                                   |
 * | $Value:  string                                   |
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
 * @property string $Name [readonly] $_Name
 * @property string $Value [readonly] $_Value
 */
abstract class AOption implements \BLW\Type\Command\IOption
{

#############################################################################################
    // Option Trait
#############################################################################################

    /**
     * Label of option.
     *
     * @var string $_Name
     */
    protected $_Name = '';

    /**
     * Value of option.
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
     * ASCII Control characers excluding whitespace characters / NL.
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
     * Creates an instance of IContainer containing options parsed from an array.
     *
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
    public static function createFromArray($Arguments, array $NoValue = array())
    {
        // Return value
        $Container = new GenericContainer(IOption::CLASSNAME);

        // Check if next argument is a descriptor or a value
        $CheckNext = function ($Name, &$Value) use(&$Arguments, &$NoValue)
        {

            // Check if next argument is a descriptor or a value
            $NextArgument = next($Arguments);

            prev($Arguments);

            if (! in_array($Name, $NoValue) &&      // Name of option not in $NoValue
                $Value === true &&                  // Value not parsed with equals sign
                $NextArgument !== false &&          // Next argument exists
                substr($NextArgument, 0, 1) != '-'  // Next argument is not an option
            ) {
                // Update value
                $Value = next($Arguments);
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
                    $CheckNext ($Name, $Value);

                    // Option
                    $Container[$Name] = new static($Name, $Value);
                }

                // Option -<option> or /<option>
                elseif (substr($Argument, 0, 1) == '-') {

                    $Name  = substr($Argument, 1, 1);
                    $Value = substr($Argument, 2) ?: true;

                    // Check next argument for value
                    $CheckNext ($Name, $Value);

                    // Option
                    $Container[$Name] = new static($Name, $Value);
                }
            }
        }

        // Invalid $Arguments
        else
            throw new InvalidArgumentException(0);

        // Done
        return $Container;
    }

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
     * @link http://www.php.net/manual/en/reserved.variables.argv.php $argv
     *
     * @param string $Arguments
     *            Command line
     * @return array Split command line.
     */
    public static function splitCommandLine($Arguments)
    {
        // Convert to string
        $Arguments = strval($Arguments);
        $split     = uniqid('__') . '__';
        $splitlen  = strlen($split) - 1;

        // Move through arguments 1 character @ a time and sanitize string for explode
        for ($i = 0, $inQuotes = false, $inEscaped = false; $i < strlen($Arguments); $i ++) {

            // Get character
            $Current = substr($Arguments, $i, 1);

            // Character is \
            if ($Current == '\\') {

                // If previous character was not \ prepare for an escape sequence
                // If previous character was \ end escape sequence
                $inEscaped = ! $inEscaped;
            }

            // Character is "
            elseif ($Current == '"') {

                // if previous character was \ end escape sequence
                if ($inEscaped)
                    $inEscaped = false;

                // If not in quote start quote
                elseif (! $inQuotes) {
                    // Save quote
                    $inQuotes = '"';

                    // Replace quote with newline
                    $Arguments  = substr_replace($Arguments, $split, $i, 1);
                    $i         += $splitlen;
                }

                // If in quotes end quotes
                elseif ($inQuotes == '"') {
                    // End quotes
                    $inQuotes = false;

                    // Replace quote with newline
                    $Arguments  = substr_replace($Arguments, $split, $i, 1);
                    $i         += $splitlen;
                }
            }

            // Character is '
            elseif ($Current == "'") {

                // if previous character was \ end escape sequence
                if ($inEscaped)
                    $inEscaped = false;

                // If not in quote start quote
                elseif (! $inQuotes) {
                    // Save quote
                    $inQuotes = "'";

                    // Replace quote with newline
                    $Arguments  = substr_replace($Arguments, $split, $i, 1);
                    $i         += $splitlen;
                }

                // If in quotes end quotes
                elseif ($inQuotes == "'") {
                    // End quotes
                    $inQuotes = false;

                    // Replace quote with newline
                    $Arguments  = substr_replace($Arguments, $split, $i, 1);
                    $i         += $splitlen;
                }
            }

            // character is whitespace
            elseif (in_array($Current, self::$_WS)) {
                // If not quotes and not escaped
                if (! $inQuotes && ! $inEscaped) {
                    // Replace whitespace with newline
                    $Arguments  = substr_replace($Arguments, $split, $i, 1);
                    $i         += $splitlen;
                }
            }

            // Character is not a printable caracter
            elseif (in_array($Current, self::$_NOWS_CTRL)) {
                // If not quotes and not escaped
                if (! $inQuotes && ! $inEscaped) {
                    // Replace character with newline
                    $Arguments  = substr_replace($Arguments, $split, $i, 1);
                    $i         += $splitlen;
                }
            }
        }

        // Explode string and filter empty items
        $Arguments = array_filter(explode($split, $Arguments), function ($v)
        {
            return ! empty($v)
                ? ! in_array($v, self::$_NOWS_CTRL)
                : false;
        });

        // Reindex
        return array_values($Arguments);
    }

    /**
     * Creates an instance of IContainer containing options parsed from a string.
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
        if (is_string($Arguments) ?  : is_callable(array(
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
# Option Trait
###############################################################################################

    /**
     * Constructor
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Value</code> is not <code>scalar</code>.
     *
     * @param string $Name
     *            Label of option.
     * @param string $Value
     *            Value of option.
     */
    public function __construct($Name, $Value)
    {
        // Is $Name scalar?
        if (is_string($Name) ?: is_callable(array(
            $Name,
            '__toString'
        ))) {

            // Set name
            $this->_Name = strval($Name);

            // is $Value TRUE
            if ($Value === true) {
                // No Value
                $this->_Value = '';
            }

            // Is $Value scalar?
            elseif (is_scalar($Value) ?: is_callable(array(
                $Value,
                '__toString'
            ))) {
                // Set value
                $this->_Value = strval($Value);
            }

            // Invalid $Value
            else
                throw new InvalidArgumentException(1);
        }

        // Invalid $Name
        else
            throw new InvalidArgumentException(0);
    }

    /**
     * All objects must have a string representation.
     *
     * @link http://www.php.net/manual/en/function.escapeshellarg.php escapeshellarg()
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

        // Starting space
        if ($Value)
            $Value = ' ' . $Value;

        // Format option
        return sprintf('%s%s%s', strlen($this->_Name) > 1 ? '--' : '-', $this->_Name, $Value);
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
            // IOption
            case 'Name':
                return $this->_Name;
            case 'Value':
                return $this->_Value;
            # Undefined property
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
            // IOption
            case 'Name':
                return true;
            case 'Value':
                return true;
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
            // IOption
        	case 'Name':
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
            // IOption
        	case 'Name':
            case 'Value':
                trigger_error(sprintf('Cannot modify readonly property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                break;
        }
    }
}

return true;
