<?php
/**
 * Nexus.php | Apr 19, 2014
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
namespace BLW\Model\HTTP\Browser;

use BLW\Type\IMediator;
use BLW\Type\HTTP\IClient;
use BLW\Type\IConfig;
use BLW\Type\MIME\IHead;
use BLW\Type\HTTP\IBrowser;
use BLW\Model\GenericContainer;
use BLW\Model\Mediator\SubscriberContainer;
use BLW\Model\Mediator\Symfony as Mediator;
use BLW\Model\MIME\UserAgent;
use BLW\Model\MIME\Accept;
use BLW\Model\MIME\AcceptLanguage;
use BLW\Model\MIME\AcceptEncoding;
use BLW\Model\MIME\CacheControl;
use BLW\Model\HTTP\RequestFactory;
use BLW\Model\HTTP\Event;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
 * Google nexus browser
 *
 * @package BLW\HTTP
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Nexus extends \BLW\Type\HTTP\ABrowser implements \BLW\Type\IEventSubscriber
{
    const USER_AGENT = 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.72 Safari/537.36';
    const ACCEPT     = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';

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
     * @return array The event names to listen to.
     */
    public static function getSubscribedEvents()
    {
        $ID = IBrowser::MEDIATOR_ID;

        return array(
            "$ID.debug" => array(
                'doDebug',
                -10
            ),
            "$ID.notice" => array(
                'doNotice',
                -10
            ),
            "$ID.warning" => array(
                'doWarning',
                -10
            ),
            "$ID.error" => array(
                'doError',
                -10
            ),
            "$ID.exception" => array(
                'doException',
                -10
            ),
            "$ID.go" => array(
                'doGo',
                0
            ),
            "$ID.post" => array(
                'doPost',
                0
            ),
            "$ID.back" => array(
                'doBack',
                0
            ),
            "$ID.forward" => array(
                'doForward',
                0
            ),
            "$ID.Page.Download" => array(
                'doPageDownload',
                -20
            ),
        );
    }

    /**
     * Constructor
     *
     * @param \BLW\Type\HTTP\IClient $Client
     *            Browser HTTP Client.
     * @param \BLW\Type\IConfig $Config
     *            Browser configuration.
     * @param \Psr\Log\LoggerAwareInterface $Logger
     *            [optional] Logger for browser.
     * @param \BLW\Type\IMediator $Mediator
     *            [optional] Mediator of browser
     */
    public function __construct(IClient $Client, IConfig $Config, LoggerInterface $Logger = null, IMediator $Mediator = null)
    {
        // Properties
        $this->_Client         = $Client;
        $this->_Current        = -1;
        $this->_History        = new GenericContainer(IBrowser::PAGE);
        $this->_RequestFactory = new RequestFactory();

        // Logger
        $this->setLogger($Logger ?: new NullLogger);

        // Mediator
        $this->_Mediator = $Mediator ?: new Mediator;

        $this->_Mediator->addSubscriber($this);

        // Plugins / Engines
        $this->_Engines = new SubscriberContainer($this->_Mediator);
        $this->_Plugins = new SubscriberContainer($this->_Mediator);

        // Usr agent
        $this->setUserAgent(self::USER_AGENT);

        // Config
        $this->_MaxHistory = isset($Config['MaxHistory'])
            ? $Config['MaxHistory']
            : 0;

        // Page
        $this->setPage($this->createUnknownPage());
    }

    /**
     * Creates Headers to add to HTTP Requests.
     *
     * @event Browser.Headers
     *
     * @return \BLW\Type\IContainer Headers for browser.
     */
    public function createHeaders()
    {
        // Default Headers
        $Headers                    = new GenericContainer(IHead::HEADER);
        $Headers['User-Agent']      = new UserAgent(self::USER_AGENT);
        $Headers['Accept']          = new Accept(self::ACCEPT);
        $Headers['Accept-Language'] = new AcceptLanguage('en-US, en;q=0.5');
        $Headers['Accept-Encoding'] = new AcceptEncoding('deflate');
        $Headers['Cache-Control']   = new CacheControl('max-age=0');

        // Browser.Headers event
        $this->_do('Headers', new Event($this, array(
            'Headers' => &$Headers
        )));

        // Done
        return $Headers;
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
