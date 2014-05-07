<?php
/**
 * SubscriberContainer.php | Apr 16, 2014
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
namespace BLW\Model\Mediator;

use ArrayObject;
use UnexpectedValueException;

use BLW\Type\IMediator;
use BLW\Type\IEventSubscriber;
use BLW\Type\IContainer;


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
 * Container for event subscribers.
 *
 * <h3>Introduction</h3>
 *
 * <p>This class acceps an <code>IMediator</code> Object
 * on construction.</p>
 *
 * <p>When <code>IEventSubscriber</code> objects are added
 * it automatically registers them with mediator.</p>
 *
 * <p>When <code>IEventSubscriber</code> objects are removed
 * it automatically de-registers them with mediator.</p>
 *
 * <hr>
 *
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class SubscriberContainer extends \BLW\Type\AContainer
{
    // Event subcriber interface
    const EVENT_SUBSCRIBER = '\\BLW\\Type\\IEventSubscriber';

    /**
     *
     * @var \BLW\Type\IMediator $_Mediator Mediator to register / de-register subscribers with.
     */
    private $_Mediator = null;

    /**
     * Sets the value at the specified index to newval
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param mixed $index
     *            The index being set.
     * @param mixed $newval
     *            The new value for the index.
     */
    public function offsetSet($index, $newval)
    {
        // Is object an instance of IEventSubscriber?
        if ($newval instanceof IEventSubscriber) {

            // Add object
            ArrayObject::offsetSet($index, $newval);

            // Register Object
            if ($this->_Mediator instanceof IMediator)
                $this->_Mediator->addSubscriber($newval);
        }

        // Object not an event subscriber
        else
            throw new UnexpectedValueException(sprintf('Invalid value: (%s). Instance of IEventSubscriber expected', is_object($newval) ? get_class($newval) : gettype($newval)));
    }

    /**
     * Appends the value
     *
     * @param mixed $value
     *            The value being appended.
     */
    public function append($value)
    {
        // Is object an instance of IEventSubscriber?
        if ($newval instanceof IEventSubscriber) {

            // Add object
            ArrayObject::append($newval);

            // Register Object
            if ($this->_Mediator instanceof IMediator)
                $this->_Mediator->addSubscriber($value);
        }

        // Object not an event subscriber
        else
            throw new UnexpectedValueException(sprintf('Invalid value: (%s). Instance of IEventSubscriber expected', is_object($newval) ? get_class($newval) : gettype($newval)));
    }

    /**
     * Unsets the value at the specified index
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param mixed $index
     *            The index being unset.
     */
    public function offsetUnset($index)
    {
        // Does index exist?
        if (ArrayObject::offsetExists($index)) {

            // Deregister object
            $this->_Mediator->remSubscriber(ArrayObject::offsetGet($index));

            // Unset object
            ArrayObject::offsetUnset($index);
        }
    }

    /**
     * Constructor
     *
     * @param IMediator $Mediator
     *            Mediator to register / de-register subscribers with.
     */
    public function __construct(IMediator $Mediator)
    {
        // ArrayObject constructor
        ArrayObject::__construct(array(), IContainer::FLAGS, IContainer::ITERATOR);

        // Types
        $this->_Types[] = self::EVENT_SUBSCRIBER;

        // Properties
        $this->_Mediator = $Mediator;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
