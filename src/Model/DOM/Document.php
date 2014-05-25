<?php
/**
 * Document.php | Apr 2, 2014
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
 * @package BLW\DOM
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Model\DOM;

use DOMXPath;
use BLW\Type\ISerializer;
use BLW\Type\IDataMapper;
use BLW\Type\IObject;
use BLW\Type\IContainer;
use BLW\Type\IObjectStorage;
use BLW\Type\IWrapper;
use BLW\Model\InvalidArgumentException;
use Symfony\Component\CssSelector\CssSelector;

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
 * Replacement / Improvement of PHP Document class.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +---------------+
 * | DOMDOCUMENT                                       |<------| DOMDocument   |
 * +---------------------------------------------------+       +---------------+
 * | __construct():                                    |<------| SERIALIZABLE  |
 * |                                                   |       | ============= |
 * | $version:   string                                |       | Serializable  |
 * | $encoding:  string                                |       +---------------+
 * | $element:   string                                |<------| Iterable      |
 * +---------------------------------------------------+       +---------------+
 * | filterXPath(): IContainer                         |
 * |                                                   |
 * | $Query: String (XPath)                            |
 * +---------------------------------------------------|
 * | filter(): filterXPath()                           |
 * |                                                   |
 * | $Selector: string (CSS Selector)                  |
 * +---------------------------------------------------+
 * | __toString(): string                              |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\DOM
 * @api BLW
 * @since 0.1.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Document extends \DOMDocument implements \BLW\Type\DOM\IDocument
{

#############################################################################################
# Serializable Trait
#############################################################################################

    /**
     *
     * @var int $Status Current status flag of the object.
     */
    protected $_Status = 0;

#############################################################################################
 # Iterable Trait
#############################################################################################

    /**
     *
     * @var \BLW\Type\IObject $Parent Pointer to current parent of object.
     */
    protected $_Parent = null;

#############################################################################################
# Document Trait
#############################################################################################

    /**
     *
     * @var int $Status Current status flag of the object.
     */
    private $_HTML = '';

#############################################################################################

#############################################################################################
# Serializable Trait
#############################################################################################

    /**
     * Generate $Serializer dynamic property.
     *
     * <h4>Note:</h4>
     *
     * <p>I decided to use a global state because the serializer is
     * needed during unserialization so it is simply imposible to pass
     * it as an argument to <code>unserialize()</code>.
     *
     * <p>Please create a serializer and serialize the class manually.</p>
     *
     * <pre>ISerializable::serializeWith(ISerializer)</pre>
     *
     * <hr>
     *
     * @return \BLW\Type\ISerializer $this->Serializer
     */
    public function getSerializer()
    {
        global $BLW_Serializer;

        // @codeCoverageIgnoreStart

        if (! $BLW_Serializer instanceof ISerializer) {
            $BLW_Serializer = new \BLW\Model\Serializer\PHP;
        }

        // @codeCoverageIgnoreEnd
        return $BLW_Serializer;
    }

    /**
     * Clears the status flag of the current object.
     *
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function clearStatus()
    {
        // Reset Status
        $this->_Status = 0;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Return a string representation of the object.
     *
     * @link http://www.php.net/manual/en/serializable.serialize.php Serializable::serialize()
     *
     * @return string $this
     */
    final public function serialize()
    {
        // Call serializer
        return $this->serializeWith($this->getSerializer());
    }

    /**
     * Return an object state from it serialized string.
     *
     * @link http://www.php.net/manual/en/serializable.unserialize.php Serializable::unserialize()
     *
     * @param string $serialized
     * @return boolean Returns <code>TRUE</code> on success and <code>FALSE</code> on failure.
     */
    final public function unserialize($serialized)
    {
        try {
            // Unserialize object
            return $this->unserializeWith($this->getSerializer(), $serialized);

        // @codeCoverageIgnoreStart

        } catch (\RuntimeException $e) {

            // Error status
            $this->_Status |= $e->getCode();

            // Error
            return false;

        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Return a string representation of the object.
     *
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @param integer $flags
     *            Serialization flags.
     * @return string $this
     */
    final public function serializeWith(ISerializer $Serializer, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        return $Serializer->encode($this, @intval($flags));
    }

    /**
     * Return an object state from it serialized string.
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Data</code> is not a string.
     *
     * @param ISerializer $Serializer
     *            Serializer handler to use.
     * @param string $Data
     *            Serialized data.
     * @param integer $flags
     *            De-Serialization flags.
     * @return boolean Returns <code>TRUE</code> on success and false on failure.
     */
    final public function unserializeWith(ISerializer $Serializer, $Data, $flags = ISerializer::SERIALIZER_FLAGS)
    {
        // Is $Data is not a string?
        if (! is_string($Data)) {
            throw new InvalidArgumentException(1);
        }

        return $Serializer->decode($this, $Data, @intval($flags));
    }

    /**
     * Hook that is called just before an object is serialized.
     */
    final public function doSerialize()
    {
        // Store HTML
        $this->_HTML = $this->saveHTML() ?: '';
    }

    /**
     * Hook that is called just after an object is unserialized.
     */
    final public function doUnSerialize()
    {
        // Restore HTML
        @$this->loadHTML($this->_HTML);

        // Unstore HTML
        $this->_HTML = '';
    }

#############################################################################################
# Iterable Trait
#############################################################################################

    /**
     * Retrieves the current parent of the object.
     *
     * @return \BLW\Type\IObject Returns <code>null</code> if no parent is set.
     */
    final public function getParent()
    {
        return $this->_Parent;
    }

    /**
     * Sets parent of the current object if null.
     *
     * @internal This is a one shot function (Only works once).
     *
     * @param mised $Parent
     *            New parent of object. (IObject|IContainer|IObjectStorage)
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    final public function setParent($Parent)
    {
        // Make sur object is not a parent of itself
        if ($Parent === $this) {
            return IDataMapper::INVALID;

        // Make sure parent is valid
        } elseif (! $Parent instanceof IObject && ! $Parent instanceof IContainer && ! $Parent instanceof IObjectStorage && ! $Parent instanceof IWrapper) {
            return IDataMapper::INVALID;

        // Make sure parent is not already set
        } elseif (! $this->_Parent instanceof IObject && ! $this->_Parent instanceof IContainer && ! $this->_Parent instanceof IObjectStorage) {

            // Update parent
            $this->_Parent = $Parent;

            return IDataMapper::UPDATED;

        // Else dont update parent
        } else {
            return IDataMapper::ONESHOT;
        }
    }

    /**
     * Clears parent of the current object.
     *
     * @access private
     * @internal For internal use only.
     *
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    final public function clearParent()
    {
        $this->_Parent = null;

        return IDataMapper::UPDATED;
    }

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return sprintf('[Document:]', basename(get_class($this)));
    }

#############################################################################################
# Document Trait
#############################################################################################

    /**
     * Constructor
     *
     * @codeCoverageIgnore
     *
     * @link http://www.php.net/manual/en/Document.construct.php Document::__construct()
     *
     * @param string $version
     *            [optional] The version number of the document as part of the XML declaration.
     * @param string $encoding
     *            [optional] The encoding of the document as part of the XML declaration.
     * @param string $element
     *            [optional] Class used to create elements in the document. Must extend <code>DOMElement</code>.
     */
    public function __construct($version = '1.0', $encoding = 'UTF-8', $element = null)
    {
        $element = @substr($element, 0, 256) ?: '\\BLW\\Model\\DOM\\Element';

        // Parent Constructor
        parent::__construct($version, $encoding);

        // Update Element class.
        parent::registerNodeClass('DOMElement', $element);
    }

    /**
     * Filters the document for elements matching the xpath query.
     *
     * @param string $Query
     *            XPath.
     * @return \BLW\Model\DOM\NodeList Returns an instance of <code>IContainer</code> containing all matched elements.
     */
    public function filterXPath($Query)
    {
        // Run XPath
        $XPath = new DOMXPath($this);
        $List  = $XPath->evaluate($Query);

        unset($XPath);

        // Create NodeList
        return new NodeList($List);
    }

    /**
     * Filters the document for elements matching the css selector.
     *
     * @link https://github.com/symfony/CssSelector/blob/master/CssSelector.php CssSelector
     * @see \BLW\Type\DOM\IDocument::filterXPath() IDOMDocument::filterXPath()
     *
     * @param string $Selector
     *            CSS Selector.
     * @return \BLW\Model\DOM\NodeList Returns an instance of <code>IContainer</code> containing all matched elements.
     */
    public function filter($Selector)
    {
        // Generate XPath
        $Query = CssSelector::toXPath($Selector);

        // Run XPath
        return $this->filterXPath($Query);
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
        return $this->saveHTML();
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
