<?php
/**
 * Section.php | Mar 21, 2014
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
 * @package BLW\MIME
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\MIME;

use ReflectionMethod;

use BLW\Model\InvalidArgumentException;
use BLW\Type\MIME\ISection;


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
 * Class which helps create and organize mime parts / mime boundaries.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+      +----------------+       +-----------------+
 * | SECTION                                           |<-----| CONTAINER      |<--+---| ArrayObject     |
 * +---------------------------------------------------+      | ============== |   |   +-----------------+
 * | _CRLF:      "\r\n"                                |      | HEADER         |   +---| SERIALIZABLE    |
 * | _Type:      string                                |      +----------------+   |   | =============== |
 * | _Boundary:  string                                |      | FACTORY        |   |   | Serializable    |
 * +---------------------------------------------------+      | ============== |   |   +-----------------+
 * | createStart(): ContentType                        |      | createStart    |   +---| ITERABLE        |
 * +---------------------------------------------------+      | createBoundary |       +-----------------+
 * | createBoundary(): Boundary                        |      | createEnd      |
 * +---------------------------------------------------+      +----------------+
 * | createEnd(): Boundary                             |
 * +---------------------------------------------------+
 * | __construct():                                    |
 * |                                                   |
 * | $Type:      string                                |
 * | $Boundary:  string                                |
 * +---------------------------------------------------+
 * | buildBoundary(): string                           |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\MIME
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Section extends \BLW\Type\AContainer implements \BLW\Type\MIME\ISection
{

#############################################################################################
    // Section Trait
#############################################################################################

    /**
     *
     * @var string $_CRLF [protected] "\r\n"
     */
    protected $_CRLF = "\r\n";

    /**
     *
     * @var string $_Type [protected] Content type.
     */
    protected $_Type = 'multipart/mixed';

    /**
     *
     * @var string $_Boundary [protected] MIME boundary.
     */
    protected $_Boundary = null;

#############################################################################################

#############################################################################################
    // Factory Trait
#############################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createStart'),
            new ReflectionMethod(get_called_class(), 'createBoundary'),
            new ReflectionMethod(get_called_class(), 'createEnd')
        );
    }

    /**
     * Create start of section header.
     *
     * @return \BLW\Model\MIME\ContentType MIME header.
     */
    public function createStart()
    {
        return new ContentType($this->_Type, array(
            'boundary' => '"' . $this->_Boundary . '"'
        ));
    }

    /**
     * Create mime boundary header.
     *
     * @return \BLW\Model\MIME\Boundary MIME header.
     */
    public function createBoundary()
    {
        return new Boundary($this->_Boundary, false);
    }

    /**
     * Create mime boundary header.
     *
     * @return \BLW\Model\MIME\Boundary MIME header.
     */
    public function createEnd()
    {
        return new Boundary($this->_Boundary, true);
    }

#############################################################################################
    // Section Trait
#############################################################################################

    /**
     * Constructor
     *
     * @param string $Type
     *            [optional] Content type:
	 *
     * <ul>
     * <li>multipart/mixed</li>
     * <li>multipart/alternative</li>
     * <li>multipart/related</li>
     * </ul>
	 *
     * @param string $Boundary
     *            [optional] MIME boundary
     * @return void
     */
    public function __construct($Type = null, $Boundary = null)
    {
        // IContainer constructor
        parent::__construct('object', 'string');

        $this->_Type = strval($Type) ?: $this->_Type;
        $this->_Boundary = strval($Boundary) ?  : $this->buildBoundary();

        // Check boundary / type
        if (empty($this->_Type))
            throw new InvalidArgumentException(0);
        if (empty($this->_Boundary))
            throw new InvalidArgumentException(1);
    }

    /**
     * Generate a random mime boundary.
     *
     * @return string Boundary string.
     */
    public static function buildBoundary()
    {
        return rand(0, 9) . '-' . rand(10000, 99999) . rand(10000, 99999) . '-' . rand(10000, 99999) . rand(10000, 99999) . '=:' . rand(10000, 99999);
    }

    /**
     * Retrieve current section boundary.
     *
     * @return string $_Boundary
     */
    public function getBoundary()
    {
        return $this->_Boundary;
    }

    /**
     * All objects must have a string representation.
     *
     * @return string $this
     */
    public function __toString()
    {
        // String value
        $return = '';

        // Start
        $return .= $this->createStart();
        $return .= $this->_CRLF;

        // Add content
        foreach ($this as $v)
            if (is_string($v) ?  : is_callable(array(
                $v,
                '__toString'
            ))) {
                $return .= $v;
            }

            // End
        $return .= $this->_CRLF;
        $return .= $this->createEnd();

        // Done
        return $return;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
