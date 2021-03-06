<?php
/**
 * APage.php | Apr 13, 2014
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
 * @package BLW\HTTP
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\HTTP\Browser;

use DateTime;
use BLW\Type\IMediator;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;
use BLW\Type\IFile;
use BLW\Type\DOM\IDocument;
use BLW\Model\GenericEvent as Event;
use BLW\Type\AMediatableWrapper;

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
 * Base class for all HTTP Browser pages
 *
 * <h3>Note to Implementors</h3>
 *
 * <ul>
 * <li><code>IPage</code> objects should have <code>$_MediatorID</code>
 * set to `Browser`</li>
 * </ul>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +----------------+       +--------------------+
 * | BROWSER\PAGE                                      |<------| WRAPPER        |<--+---| SERIALIZABLE       |
 * +---------------------------------------------------+       | ============== |   |   | ================== |
 * | _Base:          IURI                              |       | DOM\Document   |   |   | Serializable       |
 * | _RequestHead:   MIME\IHead                        |       +----------------+   |   +--------------------+
 * | _ResponseHead:  MIME\IHead                        |<------| MEDIATABLE     |   +---| COMPONENT MAPABLE  |
 * | _Created:       DateTime                          |       +----------------+   |   +--------------------+
 * | _Modified:      DateTime                          |                            +---| ITERABLE           |
 * | #RequestHead:   _RequestHead                      |                                +--------------------+
 * | #ResponseHead:  _ResponseHead                     |
 * | #Base:          _Base                             |
 * | #Created:       getCreated()                      |
 * |                 setCreated()                      |
 * | #Modified:      getModified()                     |
 * |                 setModified()                     |
 * | #Document:      _Component                        |
 * | #File:          _Component                        |
 * | __###():        _Component->###()                 |
 * |                 _Mediator->Trigger()              |
 * +---------------------------------------------------+
 * | getCreated(): DateTime                            |
 * +---------------------------------------------------+
 * | setCreated(): IDataMapper::STATUS                 |
 * |                                                   |
 * | $Date:  DateTime                                  |
 * +---------------------------------------------------+
 * | getModified(): DateTime                           |
 * +---------------------------------------------------+
 * | setModified(): IDataMapper::STATUS                |
 * |                                                   |
 * | $Date:  DateTime                                  |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\HTTP
 * @api BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\MIME\IHead $RequestHead [readonly] $_RequestHead
 * @property \BLW\Type\MIME\IHead $ResponseHead [readonly] $_ResponseHead
 * @property \BLW\Type\IURI [readonly] $_ResponseHeader->URI
 * @property \DateTime $Created [dynamic] Invokes getCreated() and setCreated().
 * @property \DateTime $Modified [dynamic] Invokes getModified() and setModified().
 * @property \BLW\Type\DOM\IDocument $Document [readonly] $_Component
 * @property \BLW\Type\IFile $File [readonly] $_Component
 */
abstract class APage extends \BLW\Type\AMediatableWrapper implements \BLW\Type\HTTP\Browser\IPage
{

#############################################################################################
# Page Trait
#############################################################################################

    /**
     * Base URI of page that relative URL's are resolved against.
     *
     * @var \BLW\Type\IURI $_Base
     */
    protected $_Base = null;

    /**
     * Request Headers.
     *
     * @var \BLW\Type\MIME\IHead $_RequestHead
     */
    protected $_RequestHead = null;

    /**
     * Response Headers.
     *
     * @var \BLW\Type\MIME\IHead $_ResponseHead
     */
    protected $_ResponseHead = null;

    /**
     * Date of creation of page.
     *
     * @var \DateTime $_Created
     */
    protected $_Created = null;

    /**
     * Date of modification of page.
     *
     * @var \DateTime $_Modified
     */
    protected $_Modified = null;

#############################################################################################




#############################################################################################
# Page Trait
#############################################################################################

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return strval($this->_Base);
    }

    /**
     * Returns the date of creation of page.
     *
     * @return \DateTime $_Created
     */
    public function getCreated()
    {
        return $this->_Created;
    }

    /**
     * Sets the date of creation of the page.
     *
     * @param \DateTime $Created
     *            New date.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function setCreated($Created)
    {
        // Validate $Created
        if ($Created instanceof DateTime) {

            // Update date
            $this->_Created = $Created;

            // Done
            return IDataMapper::UPDATED;
        }

        // Error
        return IDataMapper::INVALID;
    }

    /**
     * Returns the date of modification of the page.
     *
     * @return \DateTime $_Modified
     */
    public function getModified()
    {
        return $this->_Modified;
    }

    /**
     * Sets the date of modification of the page.
     *
     * @param \DateTime $Modified
     *            New date.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    public function setModified($Modified)
    {
        // Validate $Modified
        if ($Modified instanceof DateTime) {

            // Update date
            $this->_Modified = $Modified;

            // Done
            return IDataMapper::UPDATED;
        }

        // Error
        return IDataMapper::INVALID;
    }

    /**
     * Import component methods.
     *
     * @event IPage.###
     *
     * @param string $name
     *            Label of method to look for.
     * @param array $arguments
     *            Arguments to pass to method.
     * @return mixed Component method return value.
     */
    public function __call($name, array $arguments)
    {
        // Import component methods
        if (is_callable(array(
            $this->_Component,
            $name
        ))) {
            return call_user_func_array(array(
                $this->_Component,
                $name
            ), $arguments);

        // Vaiable functions
        } elseif (isset($this->{$name}) ? is_callable($this->{$name}) : false) {
            return call_user_func_array($this->{$name}, $arguments);

        // Undefined method
        } elseif ($this->_Mediator instanceof IMediator) {

            // Create event.
            $this->_do($name, new Event($this, array(
                'Arguments' => $arguments
            )));

            // Done
            return true;
        }

        return null;
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
            // ISerializable
            case 'Status':
                return $this->_Status;
            case 'Serializer':
                return $this->getSerializer();
            // IIterable
            case 'Parent':
                return $this->_Parent;
            case 'ID':
                return $this->getID();
            // IComponentMapable
            case 'Component':
                return $this->_Component;
            // Mediatable
            case 'Mediator':
                return $this->getMediator();
            case 'MediatorID':
                return $this->getMediatorID();
            // Page
            case 'RequestHead':
                return $this->_RequestHead;
            case 'ResponseHead':
                return $this->_ResponseHead;
            case 'Base':
                return $this->_Base;
            case 'Created':
                return $this->getCreated();
            case 'Modified':
                return $this->getModified();
            case 'Document':
                return $this->_Component instanceof IDocument ? $this->_Component : null;
            case 'File':
                return $this->_Component instanceof IFile ? $this->_Component : null;

            default:

                // Component property
                if (isset($this->_Component->{$name})) {
                    return $this->_Component->{$name};

                // Undefined property
                } else {
                    trigger_error(sprintf('Undefined property: %s::$%s', get_class($this), $name), E_USER_NOTICE);
                }
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
            // ISerializable
            case 'Status':
            case 'Serializer':
                return true;
            // IIterable
            case 'Parent':
                return $this->_Parent !== null;
            case 'ID':
                return $this->getID() !== null;
            // IMediatable
            case 'Mediator':
                return $this->getMediator() !== null;
            case 'MediatorID':
                return $this->getMediatorID() !== null;
            // IComponentMapable
            case 'Component':
                return $this->_Component !== null;
            // Page
            case 'RequestHead':
                return $this->_RequestHead !== null;
            case 'ResponseHead':
                return $this->_ResponseHead !== null;
            case 'Base':
                return $this->_Base !== null;
            case 'Created':
                return $this->getCreated() !== null;
            case 'Modified':
                return $this->getModified() !== null;
            case 'Document':
                return $this->_Component instanceof IDocument;
            case 'File':
                return $this->_Component instanceof IFile;

            default:
                return isset($this->_Component->{$name});
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
        switch ($name) {
            // ISerializable
            case 'Status':
            case 'Serializer':
            // IIterable
            case 'ID':
                $result = IDataMapper::READONLY;
                break;
            case 'Parent':
                $result = $this->setParent($value);
                break;
            // IMediatable
            case 'Mediator':
                $result = $this->setMediator($value);
                break;
            case 'MediatorID':
            // IComponentMapable
            case 'Component':
            // Page
            case 'RequestHead':
            case 'ResponseHead':
            case 'Base':
                $result = IDataMapper::READONLY;
                break;
            case 'Created':
                $result = $this->setCreated($value);
                break;
            case 'Modified':
                $result = $this->setModified($value);
                break;
            case 'Document':
            case 'File':
                $result = IDataMapper::READONLY;
                break;

            default:

                // Try to set component property
                try {
                    $this->_Component->{$name} = $value;
                    $result = IDataMapper::UPDATED;
                }

                // @codeCoverageIgnoreStart

                // Error
                catch (\Exception $e) {
                    $result = IDataMapper::UNDEFINED;
                }

                // @codeCoverageIgnoreEnd
        }

        // Check results
        if (list($Message, $Level) = ADataMapper::getErrorInfo($result, get_class($this), $name)) {
            trigger_error($Message, $Level);
        }
    }

    /**
     * Dynamic properties.
     *
     * @param string $name
     *            Label of dynamic property. (case sensitive)
     */
    public function __unset($name)
    {
        // Try to set property
        switch ($name) {
            // ISerializable
            case 'Status':
                $this->clearStatus();
                break;
            // IIterable
            case 'Parent':
                $this->clearParent();
                break;
            // IMediatable
            case 'Mediator':
                $this->clearMediator();
                break;
            // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
