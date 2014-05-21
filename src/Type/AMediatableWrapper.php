<?php
/**
 * AMediatableWrapper.php | Feb 05, 2014
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
 * @version GIT: 0.2.0
 * @author  Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type;

use BLW\Model\InvalidArgumentException;

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
 * Abstract class for all objects that can be mediated.
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +----------------+       +-------------------+
 * | MEDIATABLEWRAPPER                                 |<------| WRAPPER        |<--+---| SERIALIZABLE      |
 * +---------------------------------------------------+       +----------------+   |   | ================= |
 *                                                             | MEDIATABLE     |   |   | Serializable      |
 *                                                             +----------------+   |   +-------------------+
 *                                                                                  +---| COMPONENTMAPABLE  |
 *                                                                                  |   +-------------------+
 *                                                                                  +---| ITERABLE          |
 *                                                                                      +-------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property $Mediator \BLW\Type\IMediator [dynamic] Invokes setMediator() and getMediator().
 * @property $MediatorID string [readonly] Invokes getMediator().
 */
abstract class AMediatableWrapper extends \BLW\Type\AWrapper implements \BLW\Type\IMediatable
{

#############################################################################################
# Mediatable Trait
#############################################################################################

    /**
     * Pointer to current mediator.
     *
     * @var \BLW\Type\IMediator $_Mediator
     */
    protected $_Mediator = null;

    /**
     * Current mediator id of object.
     *
     * @var string $_MediatorID
     */
    protected $_MediatorID = null;

#############################################################################################




#############################################################################################
# Mediatable Trait
#############################################################################################

    /**
     * Get the current mediator of the object.
     *
     * @return \BLW\Type\IMediator Returns <code>null</code> if no mediator is set.
     */
    final public function getMediator()
    {
        return $this->_Mediator;
    }

    /**
     * Set $Mediator dynamic property.
     *
     * @param \BLW\Type\IMediator $Mediator
     *            New mediator.
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    final public function setMediator($Mediator)
    {
        // Is mediator valid
        if ($Mediator instanceof IMediator) {
            $this->_Mediator = $Mediator;

            return IDataMapper::UPDATED;

        // Invalid mediator
        } else {
            return IDataMapper::INVALID;
        }
    }

    /**
     * Clear $Mediator dynamic property.
     *
     * @return integer Returns a <code>IDataMapper</code> status code.
     */
    final public function clearMediator()
    {
        // Clear Mediator
        $this->_Mediator = null;

        // Done
        return IDataMapper::UPDATED;
    }

    /**
     * Generates a unique id used to identify object in mediator.
     *
     * @return string Hash of object.
     */
    public function getMediatorID()
    {
        if (! $this->_MediatorID) {
            $this->_MediatorID = spl_object_hash($this);
        }

        return $this->_MediatorID;
    }

    /**
     * Registers a function to execute on a mediator event.
     *
     * Registers a function to execute on a mediator event.
     *
     * <h4>Format:</h4>
     *
     * <pre>mixed function (\BLW\Type\IEvent $Event)</pre>
     *
     * <hr>
     *
     * @param string $EventName
     *            Event ID to attach to.
     * @param callable $Callback
     *            Function to call.
     * @param integer $Priority
     *            Priotory of $Callback. (Higher priority = Higher Importance)
     */
    final public function _on($EventName, $Callback, $Priority = 0)
    {
        // Parameter validation

        // Is $EventName a string?
        if (! is_scalar($EventName) && ! is_callable(array(
            $EventName,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);

        // Is $Callback callable
        } elseif (! is_callable($Callback)) {
            throw new InvalidArgumentException(1);

        } else {

            // Register event
            $Mediator = $this->getMediator();
            $ID       = $this->getMediatorID();

            if ($Mediator instanceof IMediator) {
                $Mediator->register("$ID.$EventName", $Callback, @intval($Priority));
            } else {
                trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
            }
        }
    }

    /**
     * Activates a mediator event.
     *
     * @param string $EventName
     *            Event ID to activate.
     * @param \BLW\Type\IEvent $Event
     *            Event object associated with the event.
     */
    final public function _do($EventName, IEvent $Event = null)
    {
        // Parameter validation

        // Is $EventName a string
        if (! is_scalar($EventName) && ! is_callable(array(
            $EventName,
            '__toString'
        ))) {
            throw new InvalidArgumentException(0);
        }

        // Trigger event
        $Mediator = $this->getMediator();
        $ID       = $this->getMediatorID();

        if ($Mediator instanceof IMediator) {
            $Mediator->trigger("$ID.$EventName", $Event);
        } else {
            trigger_error(sprintf('Mediator not set with %s::setMediator()', get_class($this)), E_USER_NOTICE);
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
