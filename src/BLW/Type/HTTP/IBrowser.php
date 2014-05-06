<?php
/**
 * IBrowser.php | Apr 14, 2014
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
 * @version GIT 0.2.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */
namespace BLW\Type\HTTP;

use BLW\Type\IEvent;
use BLW\Type\HTTP\Browser\IPage;


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
 * Interface for HTTP Browser objects.
 *
 * <h3>Note to Implementors</h3>
 *
 * <ul>
 * <li>All browsers should have the mediator ID set to `Browser`.</li>
 * </ul>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +----------------------+       +--------------------+
 * | BROWSER                                           |<------| WRAPPER              |<--+---| SERIALIZABLE       |
 * +---------------------------------------------------+       | ==================== |   |   | ================== |
 * | _Mediator:        IMediator                       |       | Browser\Page         |   |   | Serializable       |
 * | _History:         IContainer(IPage)               |       +----------------------+   |   +--------------------+
 * | _MaxHistory:      int                             |<------| FACTORY              |   +---| COMPONENT MAPABLE  |
 * | _CurrentPage:     int                             |       | ==================== |   |   +--------------------+
 * | _Client:          IClient                         |       | createHeaders        |   +---| ITERABLE           |
 * | _RequestFactory:  IRequestFactory                 |       | createPage           |       +--------------------+
 * | _Plugins:         SubscriberContainer(Plugin)     |       | createTimeoutPage    |
 * | _Engines:         SubscriberContainer(Engine)     |       | createUnknownPage    |
 * | _UserAgent:       string                          |       +----------------------+
 * | #Client:          _Client                         |<------| LoggerAwareInterface |
 * | #RequestFactory:  _RequestFactory                 |       +----------------------+
 * | #Plugins:         _Plugins                        |
 * | #Engines:         _Engines                        |
 * | #UserAgent:       getUserAgent()                  |
 * |                   setUserAgent()                  |
 * +---------------------------------------------------+
 * | Browser.debug                                     |
 * | Browser.notice                                    |
 * | Browser.warning                                   |
 * | Browser.error                                     |
 * | Browser.exception                                 |
 * |                                                   |
 * | Browser.Headers                                   |
 * |                                                   |
 * | Browser.Navigate                                  |
 * | Browser.Back                                      |
 * | Browser.Forward                                   |
 * |                                                   |
 * | Browser.Page.Change                               |
 * | Browser.Page.Download                             |
 * | Browser.Page.Load                                 |
 * | Browser.Page.Ready                                |
 * +---------------------------------------------------+
 * | createHeaders(): IContainer(MIME\IHeader)         |
 * +---------------------------------------------------+
 * | createPage(): IPage                               |
 * |                                                   |
 * | $Request:   IRequest                              |
 * | $Response:  IResponse                             |
 * +---------------------------------------------------+
 * | createStatusPage(): IPage                         |
 * |                                                   |
 * | $Request:   IRequest                              |
 * | $Response:  IResponse                             |
 * +---------------------------------------------------+
 * | createTimeoutPage(): IPage                        |
 * |                                                   |
 * | $Request:  IRequest                               |
 * +---------------------------------------------------+
 * | createUnknownPage(): IPage                        |
 * |                                                   |
 * | $Request:  IRequest                               |
 * +---------------------------------------------------+
 * | getUserAgent(): string                            |
 * +---------------------------------------------------+
 * | setUserAgent(): IDataMapper::STATUS               |
 * |                                                   |
 * | $UserAgent:  string                               |
 * +---------------------------------------------------+
 * | setPage(): IDataMapper::STATUS                    |
 * |                                                   |
 * | $Page:  IPage                                     |
 * +---------------------------------------------------+
 * | addHistory(): IDataMapper::STATUS                 |
 * |                                                   |
 * | $Page:  IPage                                     |
 * +---------------------------------------------------+
 * | doNavigate():                                     |
 * |                                                   |
 * | $Event:  IEvent                                   |
 * +---------------------------------------------------+
 * | doBack():                                         |
 * |                                                   |
 * | $Event:  IEvent                                   |
 * +---------------------------------------------------+
 * | doForward():                                      |
 * |                                                   |
 * | $Event:  IEvent                                   |
 * +---------------------------------------------------+
 * </pre>
 *
 * <hr>
 *
 * @package BLW\HTTP
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\IMediator $_Mediator [protected] Browser Mediator.
 * @property \BLW\Type\IContainer $_History [protected] Stores an acurate history of previously navigated pages.
 * @property int $_MaxHistory [protected] Maximum pages to keep in history.
 * @property int $_Current [protected] Current position in history.
 * @property \BLW\Type\HTTP\IClient $_Client [protected] HTTP Client used by browser to handle requests.
 * @property \BLW\Type\HTTP\IRequestFactory $_RequestFactory [protected] Factory for <code>IRequest</code>.
 * @property \BLW\Model\Container\SubscriberContainer $_Plugins [protected] Browser plugins.
 * @property \BLW\Model\Container\SubscriberContainer $_Engines [protected] Browser engines.
 * @property string $_UserAgent [protected] User agent string of browser.
 * @property \BLW\Type\HTTP\IClient $Client [readonly] $_Client
 * @property \BLW\Type\HTTP\IRequestFactory $RequestFactory [readonly] $_RequestFactory
 * @property \BLW\Model\Container\SubscriberContainer $Plugins [readonly] $_Plugins
 * @property \BLW\Model\Container\SubscriberContainer $Engines [readonly] $_Engines
 * @property string $UserAgent [dynamic] Invokes getUserAgent() and setUserAgent().
 */
interface IBrowser extends \BLW\Type\IWrapper, \BLW\Type\IFactory, \Psr\Log\LoggerAwareInterface
{

    const PAGE = '\\BLW\\Type\\HTTP\\Browser\\IPage';

    const MEDIATOR_ID = 'Browser';

    /**
     * Creates Headers to add to HTTP Requests.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return \BLW\Type\IContainer Headers for browser.
     */
    public function createHeaders();

    /**
     * Creates a page from a HTTP Response.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request that produced response.
     * @param \BLW\Type\HTTP\IResponse $Response
     *            Response associated with request.
     * @return \BLW\Type\HTTP\IPage Generated Page.
     */
    public function createPage(IRequest $Request, IResponse $Response);

    /**
     * Creates an error page from HTTP Response code.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request that produced response.
     * @param \BLW\Type\HTTP\IResponse $Response
     *            Response associated with request.
     * @return \BLW\Type\HTTP\IPage Generated Page.
     */
    public function createStatusPage(IRequest $Request, IResponse $Response);

    /**
     * Creates a default page for request Timeouts.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request that produced timeout.
     * @return \BLW\Type\HTTP\IPage Generated Page.
     */
    public function createTimeoutPage(IRequest $Request);

    /**
     * Creates a default page for exceptional errors / responses.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            [optional] Request that produced timeout.
     * @return \BLW\Type\HTTP\IPage Generated Page.
     */
    public function createUnknownPage(IRequest $Request = null);

    /**
     * Retrieve the current User Agent string of the browser.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @return string $_UserAgent
     */
    public function getUserAgent();

    /**
     * Sets the User Agent string of the browser.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param string $UserAgent
     *            New User Agent.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setUserAgent($UserAgent);

    /**
     * Sets the current page of browser.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\HTTP\Browser\IPage $Page
     *            Page to set.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function setPage($Page);

    /**
     * Adds a page to history.
     *
     * <h4>Note</h4>
     *
     * <p>If history is full, oldest value is removed to make space
     * for new entry.</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLWt\Type\HTTP\Browser\IPage $Page
     *            Page to add.
     * @return int Returns a <code>DataMapper</code> status code.
     */
    public function addHistory($Page);

    /**
     * Handles the go() dynamic call.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @event Page.Change Called at begining of call when a new page is requested.
     * @event Page.Download Called after request has been sent to HTTP client.
     * @event Page.Load Called after request has finised.
     * @event Page.Ready Called after request has been loaded into a page.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by navigate().
     */
    public function doGo(IEvent $Event);

    /**
     * Handles the back() dynamic call.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by back().
     */
    public function doBack(IEvent $Event);

    /**
     * Handles the forward() dynamic call.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by forward().
     */
    public function doForward(IEvent $Event);
}

return true;
