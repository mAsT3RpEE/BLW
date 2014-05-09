<?php
/**
 * IEventSubscriber.php | Mar 06, 2014
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
 * Interface for all EventSubscibers.
 *
 * <h3>Introduction</h3>
 *
 * <p>An EventSubscriber produces an array of events to register.</p>
 *
 * <p>When added to an Mediator, it invokes
 * <code>IEventSubscriber::getSubscribedEvents()</code> and
 * registers the subscriber as a listener for all returned events.</p>
 *
 * <hr>
 *
 * @package BLW\Core
 * @api     BLW
 * @since   1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 * @link https://github.com/symfony/EventDispatcher/blob/master/EventSubscriberInterface.php Original
 */
interface IEventSubscriber
{

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * <h3>Introduction</h3>
     *
     * <p>The array keys are event names and the value can be:</p>
     *
     * <ul>
     * <li>The method name to call (priority defaults to 0)</li>
     * <li>An array composed of the method name to call and the priority</li>
     * <li>An array of arrays composed of the method names to call and respective
     * priorities, or 0 if unset.</li>
     * </ul>
     *
     * <h4>Example:</h4>
     *
     * <pre>
     * array('eventName' => 'methodName')
     * array('eventName' => array('methodName', $priority))
     * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     * </pre>
     *
     * @api BLW
     * @since   1.0.0
     *
     * @return array The event names to listen to.
     */
    public static function getSubscribedEvents();
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
