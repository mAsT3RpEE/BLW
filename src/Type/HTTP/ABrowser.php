<?php
/**
 * ABrowser.php | Apr 14, 2014
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
namespace BLW\Type\HTTP;

use ReflectionMethod;
use RuntimeException;
use BLW\Type\IDataMapper;
use BLW\Type\ADataMapper;
use BLW\Type\IURI;
use BLW\Type\IEvent;
use BLW\Type\MIME\IHead;
use BLW\Type\HTTP\Browser\IPage;
use BLW\Model\GenericURI;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericContainer;
use BLW\Model\GenericFile;
use BLW\Model\MIME\Accept;
use BLW\Model\MIME\AcceptLanguage;
use BLW\Model\MIME\AcceptEncoding;
use BLW\Model\MIME\UserAgent;
use BLW\Model\MIME\Head\RFC2616 as RFC2616Head;
use BLW\Model\DOM\Document;
use BLW\Model\HTTP\Event;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\HTTP\Browser\Page\HTML as HTMLPage;
use BLW\Model\HTTP\Browser\Page\File as FilePage;
use Psr\Log\LoggerInterface;

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
 * @since   1.0.0
 * @author  mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property \BLW\Type\HTTP\IClient $Client [readonly] $_Client
 * @property \BLW\Type\HTTP\IRequestFactory $RequestFactory [readonly] $_RequestFactory
 * @property \BLW\Model\Mediator\SubscriberContainer $Plugins [readonly] $_Plugins
 * @property \BLW\Model\Mediator\SubscriberContainer $Engines [readonly] $_Engines
 * @property string $UserAgent [dynamic] Invokes getUserAgent() and setUserAgent().
 *
 * @method void _on(string $EventName, callable $Callback, integer $Priority) Registers a function to execute on a mediator event.
 * @method void _do(string $EventName, \BLW\Type\IEvent $Event) Activates a mediator event.
 * @method bool debug(string $message)
 * @method bool notice(string $message)
 * @method bool warning(string $message)
 * @method bool error(string $message)
 * @method bool exception(string $message)
 * @method bool go(string|\BLW\Type\IURI $Target)
 * @method bool forward()
 * @method bool back()
 */
abstract class ABrowser extends \BLW\Type\AWrapper implements \BLW\Type\HTTP\IBrowser
{

#############################################################################################
# LoggerAwareInterface Trait
#############################################################################################

    /**
     * PSR Logger.
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

#############################################################################################
# Browser Trait
#############################################################################################

    /**
     * Browser Mediator.
     *
     * @var \BLW\Type\IMediator $_Mediator
     */
    protected $_Mediator = null;

    /**
     * Stores an acurate history of previously navigated pages.
     *
     * @var \BLW\Type\IContainer $_History
     */
    protected $_History = null;

    /**
     * Maximum pages to keep in history.
     *
     * @var int $_MaxHistory
     */
    protected $_MaxHistory = 4;

    /**
     * Current position in history.
     *
     * @var int $_Current
     */
    protected $_Current = - 1;

    /**
     * HTTP Client used by browser to handle requests.
     *
     * @var \BLW\Type\HTTP\IClient $_Client
     */
    protected $_Client = null;

    /**
     * Factory for <code>IRequest</code>.
     *
     * @var \BLW\Type\HTTP\IRequestFactory $_RequestFactory
     */
    protected $_RequestFactory = null;

    /**
     * Browser plugins.
     *
     * @var \BLW\Model\Container\SubscriberContainer $_Plugins
     */
    protected $_Plugins = null;

    /**
     * Browser engines.
     *
     * @var \BLW\Model\Container\SubscriberContainer $_Engines
     */
    protected $_Engines = null;

    /**
     * User agent string of browser.
     *
     * @var string $_UserAgent
     */
    protected $_UserAgent = '';

#############################################################################################




#############################################################################################
# LoggerAwareInterface
#############################################################################################

    /**
     * Sets a logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *            New Logger.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setLogger(LoggerInterface $logger)
    {
        // Update Logger
        $this->logger = $logger;

        // Done
        return IDataMapper::UPDATED;
    }

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
            new ReflectionMethod(get_called_class(), 'createHeaders'),
            new ReflectionMethod(get_called_class(), 'createPage'),
            new ReflectionMethod(get_called_class(), 'createStatusPage'),
            new ReflectionMethod(get_called_class(), 'createTimeoutPage'),
            new ReflectionMethod(get_called_class(), 'createUnknownPage')
        );
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
        $Headers['User-Agent']      = new UserAgent($this->_UserAgent);
        $Headers['Accept']          = new Accept('text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8');
        $Headers['Accept-Language'] = new AcceptLanguage('en-US, en;q=0.5');
        $Headers['Accept-Encoding'] = new AcceptEncoding('gzip, deflate');

        // Browser.Headers event
        $this->_do('Headers', new Event($this, array(
            'Headers' => &$Headers
        )));

        // Done
        return $Headers;
    }

    /**
     * Creates a page from a HTTP Response.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request that produced response.
     * @param \BLW\Type\HTTP\IResponse $Response
     *            Response associated with request.
     * @return \BLW\Type\HTTP\IPage Generated Page.
     */
    public function createPage(IRequest $Request, IResponse $Response)
    {
        // Status of 200
        if ($Response->Status == 200 && isset($Response->Header['Content-Type'])) {

            // Test Content-Type
            $ContentType = $Response->Header['Content-Type'];

            switch (1) {
                // Text document
                case preg_match('!text/.*!', $ContentType):

                    // CreateDocument
                    $Document = new Document();

                    $Document->loadHTML(strval($Response->Body) ?: '<html><title></title><body></body></html>');

                    // Create Page
                    $Base = $Response->URI ?: $Request->URI ?: new GenericURI('about:none');
                    $Page = new HTMLPage($Document, $Base, $Request->Header, $Response->Header, $this->_Mediator);

                    // Done
                    break;

                // File
                default:

                    // CreateFile
                    $File = new GenericFile('php://tmp');

                    $File->putContents(strval($Response->Body));

                    // Create Page
                    $Base = $Response->URI ?: $Request->URI ?: new GenericURI('about:none');
                    $Page = new FilePage($File, $Base, $Request->Header, $Response->Header, $this->_Mediator);
            }
        }

        // Other responses
        elseif ($Response->isValidCode($Response->Status)) {
            $Page = $this->createStatusPage($Request, $Response);

        // Invalid Responses
        } else {
            $Page = $this->createUnknownPage();
        }

        // Done
        return $Page;
    }

    /**
     * Creates an error page from HTTP Response code.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request that produced response.
     * @param \BLW\Type\HTTP\IResponse $Response
     *            Response associated with request.
     * @return \BLW\Type\HTTP\Browser\IPage Generated Page.
     */
    public function createStatusPage(IRequest $Request, IResponse $Response)
    {
        // CreateDocument
        $Document = new Document();
        $HTML = "<html>\r\n".
                    "<head><title>%1\$d %2\$s</title></head>" .
                    "<body bgcolor=\"white\">\r\n<center><h1>%1\$d %2\$s</h1></center>\r\n<hr>\r\n<center>BLW/HTTP 1.0.0</center>\r\n</body>\r\n" .
                "</html>\r\n";

        $Document->loadHTML(sprintf($HTML, $Response->Status, $Response->getCodeString($Response->Status)));

        // Create Page
        $Base = $Response->URI ?: $Request->URI ?: new GenericURI('about:none');
        $Page = new HTMLPage($Document, $Base, $Request->Header, $Response->Header, $this->_Mediator);

        // Done
        return $Page;
    }

    /**
     * Creates a default page for request Timeouts.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request that produced timeout.
     * @return \BLW\Type\HTTP\Browser\IPage Generated Page.
     */
    public function createTimeoutPage(IRequest $Request)
    {
        // Create Document
        $Document = new Document();
        $HTML     = "<html>\r\n" .
                        "<head><title>%1\$d %2\$s</title></head>" .
                        "<body bgcolor=\"white\">\r\n<center><h1>%1\$d %2\$s</h1></center>\r\n<hr>\r\n<center>BLW/HTTP 1.0.0</center>\r\n</body>\r\n" .
                    "</html>\r\n";

        $Document->loadHTML(sprintf($HTML, 408, 'Request Timeout'));

        // Create Page
        $Base = $Request->URI ?: new GenericURI('about:none');
        $Page = new HTMLPage($Document, $Base, $Request->Header, new RFC2616Head(), $this->_Mediator);

        // Done
        return $Page;
    }

    /**
     * Creates a default page for exceptional errors / responses.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            [optional] Request that produced timeout.
     * @return \BLW\Type\HTTP\Browser\IPage Generated Page.
     */
    public function createUnknownPage(IRequest $Request = null)
    {
        $Request  = $Request ?: new Request();

        // Create Document
        $Document = new Document();
        $HTML     = "<html>\r\n<head><title>Untitled</title></head>\r\n<body bgcolor=\"white\"></body>\r\n</html>\r\n";

        $Document->loadHTML($HTML);

        // Create Page
        $Base = $Request->URI ?: new GenericURI('about:none');
        $Page = new HTMLPage($Document, $Base, $Request->Header, new RFC2616Head(), $this->_Mediator);

        // Done
        return $Page;
    }

#############################################################################################
# Browser Trait
#############################################################################################

    /**
     * Get the ID of the object.
     *
     * @return string Current ID.
     */
    public function getID()
    {
        return sprintf('[Browser:%s]', basename(get_class($this)));
    }

    /**
     * Retrieve the current User Agent string of the browser.
     *
     * @return string $_UserAgent
     */
    public function getUserAgent()
    {
        return $this->_UserAgent;
    }

    /**
     * Sets the User Agent string of the browser.
     *
     * @uses \BLW\Model\MIME\UserAgent::parseUserAgent() UserAgent::parseUserAgent()
     *
     * @param string $UserAgent
     *            New User Agent.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setUserAgent($UserAgent)
    {
        // Is user agent a string?
        if (is_string($UserAgent) ?: is_callable(array(
            $UserAgent,
            '__toString'
        ))) {

            // Update
            $this->_UserAgent = UserAgent::parseUserAgent($UserAgent);

            // Done
            return IDataMapper::UPDATED;

        // Error
        } else {
            return IDataMapper::INVALID;
        }
    }

    /**
     * Sets the current page of browser.
     *
     * @param \BLW\Type\HTTP\Browser\IPage $Page
     *            Page to set.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function setPage($Page)
    {
        // Is $Page a real page?
        if ($Page instanceof IPage) {

            // Update Page
            $this->_Component = $Page;
            $Page->Parent = $this;

            // Done
            return IDataMapper::UPDATED;

        // Invalid
        } else {
            return IDataMapper::INVALID;
        }
    }

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
     * @param \BLW\Type\HTTP\Browser\IPage $Page
     *            Page to add.
     * @return integer Returns a <code>DataMapper</code> status code.
     */
    public function addHistory($Page)
    {
        // Is $Page a real page?
        if ($Page instanceof IPage) {

            // Reindex
            $History  = iterator_to_array($this->_History);
            $Length   = min($this->_MaxHistory - 1, $this->_Current + 1); // Begining or array up to Current position or (Maxlength - 1)
            $Start    = max(0, $this->_Current + 2 - $this->_MaxHistory); // Current posistion stepped back (Maxlength - 1) spaces
            $History  = array_slice($History, $Start, $Length, false);

            // Add to history
            $History[] = $Page;

            // Update
            $this->_History->exchangeArray($History);

            $this->_Current = count($this->_History) - 1;

            // Done
            return IDataMapper::UPDATED;

        // Invalid
        } else {
            return IDataMapper::INVALID;
        }
    }

    /**
     * Parses target
     *
     * @ignore
     * @param string|\BLW\Type\IURI $Target
     */
    private function _getURI($Target)
    {
        // Is $Target a URI?
        if ($Target instanceof IURI) {

            // Convert to string
            $URI    = $Target;
            $Target = strval($Target);

            return $URI;
        }

        // Is $Target is a string?
        elseif (is_string($Target) ?: is_callable(array(
            $Target,
            '__toString'
        ))) {

            // CreateURIs
            return new GenericURI($Target);

        // Error
        } else {
            return false;
        }
    }

    /**
     * Handles the go() dynamic call.
     *
     * <h3>Summarry</h3>
     *
     * <pre>void go(string|IURI $Target)</pre>
     *
     * <hr>
     *
     * @event Page.Change Called at begining of call when a new page is requested.
     * @event Page.Download Called before a request is sent to HTTP client.
     * @event Page.Load Called after request has finised.
     * @event Page.Ready Called after request has been loaded into a page.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by go().
     * @return void
     */
    public function doGo(IEvent $Event)
    {
        // Validate Arguments
        if (! isset($Event->Arguments) ?: count($Event->Arguments) < 1 ?: !(list ($Target) = $Event->Arguments)) {

            // Generate exception
            $this->exception(sprintf('%s::go(string|IURI $Target) Missing argument 1', get_class($this)));

        // Arguments
        } elseif (! $URI = $this->_getURI($Target)) {
            // Generate exception
            $this->exception(sprintf('%s::go(string|IURI $Target) $Target (%s) is invalid', get_class($this), is_object($Target) ? get_class($Target) : gettype($Target)));

        // Is $URI invalid()
        } elseif (! $URI->isValid()) {

            // Error
            $this->error(sprintf('Invalid URI (%s).', $Target));

            // Error Page
            $this->setPage($this->createUnknownPage());

        // All okay
        } else {

            // Browser.Page.Change event
            $this->_do('Page.Change', new Event($this, array(
                'Target'  => &$Target,
                'URI'     => &$URI,
                'BaseURI' => &$this->Base
            )));

            // Create request
            $Request = $this->_RequestFactory->createGET($URI, $this->Base, $this->createHeaders());

            // Browser.Page.Download event
            $this->_do('Page.Download', new Event($this, array(
                'Request' => &$Request
            )));
        }
    }

    /**
     * Handles the post() dynamic call.
     *
     * <h3>Summarry</h3>
     *
     * <pre>void post(string|IURI $Target, array|Traversable $Data)</pre>
     *
     * <hr>
     *
     * @event Page.Change Called at begining of call when a new page is requested.
     * @event Page.Download Called before a request is sent to HTTP client.
     * @event Page.Load Called after request has finised.
     * @event Page.Ready Called after request has been loaded into a page.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by post().
     * @return void
     */
    public function doPost(IEvent $Event)
    {
        // Validate Arguments
        if (! isset($Event->Arguments) ?: count($Event->Arguments) < 2 ?: !(list ($Target, $Data) = $Event->Arguments)) {
            // Generate exception
            $this->exception(sprintf('%s::post(string|IURI $Target, array|Traversable $Data) Missing argument', get_class($this)));

        // Arguments
        } elseif (! $URI = $this->_getURI($Target)) {
            // Generate exception
            $this->exception(sprintf('%s::post(string|IURI $Target, array|Traversable $Data) $Target (%s) is invalid', get_class($this), is_object($Target) ? get_class($Target) : gettype($Target)));

        } elseif (! is_array($Data) && ! $Data instanceof \Traversable) {
            // Generate exception
            $this->exception(sprintf('%s::post(string|IURI $Target, array|Traversable $Data) $Data (%s) is invalid', get_class($this), is_object($Data) ? get_class($Data) : gettype($Data)));

        // Is $URI invalid()
        } elseif (! $URI->isValid()) {

            // Error
            $this->error(sprintf('Invalid URI (%s).', $Target));

            // Error Page
            $this->setPage($this->createUnknownPage());

        // All okay
        } else {

            // Browser.Page.Change event
            $this->_do('Page.Change', new Event($this, array(
                'Target'  => &$Target,
                'URI'     => &$URI,
                'BaseURI' => &$this->Base
            )));

            // Create request
            $Request = $this->_RequestFactory->createPOST($URI, $this->Base, $Data, $this->createHeaders());

            // Browser.Page.Download event
            $this->_do('Page.Download', new Event($this, array(
                'Request' => &$Request
            )));
        }
    }

    /**
     * Downloads a page into browser.
     *
     * @event Page.Ready Called after request has been loaded into a page.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event associated with Page.Download
     */
    public function doPageDownload(IEvent $Event)
    {
        // Validate event
        if (!isset($Event->Request) ?: !$Event->Request instanceof IRequest) {
            $this->warning('Page.Download(Request): Invalid Request');
            return;
        }

        $Request = $Event->Request;

        // Send request to client
        try {

            $this->debug(sprintf('Sending request to (%s)', $Request->URI));

            $this->_Client->send($Request);
            $this->_Client->run($Request->Config['Timeout']);

        // @codeCoverageIgnoreStart

        // cURL Errors
        } catch (RuntimeException $e) {

            // Exception
            $this->exception($e->getMessage());

            // Return
            return null;

        // Invalid $Request
        } catch (InvalidArgumentException $e) {

            // Error
            $this->error($e->getMessage());

            // Return
            return null;
        }

        // @codeCoverageIgnoreEnd

        // Get response
        $Response = $this->_Client[$Request];

        // Free up memmory
        $this->_Client->detach($Request);

        // Browser.Page.Load Event
        $this->_do('Page.Load', new Event($this, array(
            'Request'  => &$Request,
            'Response' => &$Response
        )));

        // Request still running
        if ($Response['Running']) {

            // Timeout
            $this->notice(sprintf('Request timeout for (%s)', $Request->URI));

            // Create page
            $Page = $this->createTimeoutPage($Request);

        // Is response known
        } elseif ($Response->isValidCode($Response->Status)) {

            $this->debug(sprintf('Response for (%s) answered with code (%d).', $Request->URI, $Response->Status));

            // Create page
            $Page = $this->createPage($Request, $Response);

        // @codeCoverageIgnoreStart

        // Unkown response
        } else {

            $this->warning(sprintf('Invalid response code (%s) for url (%s).', $Response->Status, $Request->URI));

            // Create page
            $Page = $this->createUnknownPage();
        }
        // @codeCoverageIgnoreEnd

        // Update page
        $this->setPage($Page);

        // Update History
        $this->addHistory($Page);

        // Browser.Page.Load Event
        $this->_do('Page.Ready', new Event($this));
    }

    /**
     * Handles the back() dynamic call.
     *
     * @event Page.Change Called at begining of call when a new page is requested.
     * @event Page.Ready Called after request has been loaded into a page.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by back().
     * @return void
     */
    public function doBack(IEvent $Event)
    {
        // Check current page
        if (min($this->_Current, count($this->_History)) > 0) {

            // Retrieve page
            $this->_Current --;

            $Page = $this->_History[$this->_Current];

            // Browser.Page.Change event
            $this->_do('Page.Change', new Event($this, array(
                'Target' => 'back',
                'URI' => &$Page->Base,
                'BaseURI' => &$this->Base
            )));

            $this->setPage($Page);

            $this->debug(sprintf('Navigated back to (%s)', $Page->Base));

            // Browser.Page.Load Event
            $this->_do('Page.Ready', new Event($this));

        // No more history
        } else {
            $this->notice('Tried to navigate back to non existant page');
        }
    }

    /**
     * Handles the forward() dynamic call.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by forward().
     */
    public function doForward(IEvent $Event)
    {
        // Check current page
        if ($this->_Current < count($this->_History) - 1) {

            // Retrieve page
            $this->_Current ++;

            $Page = $this->_History[$this->_Current];

            // Browser.Page.Change event
            $this->_do('Page.Change', new Event($this, array(
                'Target'  => 'forward',
                'URI'     => &$Page->Base,
                'BaseURI' => &$this->_Component->Base
            )));

            $this->setPage($Page);

            $this->debug(sprintf('Navigated forward to (%s)', $Page->Base));

            // Browser.Page.Ready Event
            $this->_do('Page.Ready', new Event($this));
        }

        // No more history
        else {
            $this->notice('Tried to navigate forward to non existant page');
        }
    }

    /**
     * Handles the debug() dynamic call.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by debug().
     */
    public function doDebug(IEvent $Event)
    {
        // Validate Arguments
        if (isset($Event->Arguments) ? count($Event->Arguments) > 0 : false) {

            // Get arguments
            list ($Message) = $Event->Arguments;

            // Is $Message a string?
            if (is_string($Message) ?  : is_callable(array(
                $Message,
                '__toString'
            ))) {

                // Does logger exist? Log
                if ($this->logger instanceof LoggerInterface) {
                    $this->logger->debug($Message);
                }
            }

            // Stop further event triggers
            $Event->stopPropagation();
        }
    }

    /**
     * Handles the notice() dynamic call.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by notice().
     */
    public function doNotice(IEvent $Event)
    {
        // Validate Arguments
        if (isset($Event->Arguments) ? count($Event->Arguments) > 0 : false) {

            // Get arguments
            list ($Message) = $Event->Arguments;

            // Is $Message a string?
            if (is_string($Message) ?  : is_callable(array(
                $Message,
                '__toString'
            ))) {

                // Does logger exist? Log
                if ($this->logger instanceof LoggerInterface) {
                    $this->logger->notice($Message);
                }
            }

            // Stop further event triggers
            $Event->stopPropagation();
        }
    }

    /**
     * Handles the warning() dynamic call.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by warning().
     */
    public function doWarning(IEvent $Event)
    {
        // Validate Arguments
        if (isset($Event->Arguments) ? count($Event->Arguments) > 0 : false) {

            // Get arguments
            list ($Message) = $Event->Arguments;

            // Is $Message a string?
            if (is_string($Message) ?  : is_callable(array(
                $Message,
                '__toString'
            ))) {

                // Does logger exist? Log
                if ($this->logger instanceof LoggerInterface) {
                    $this->logger->warning($Message);
                }

                // Error message
                trigger_error($Message, E_USER_NOTICE);
            }
        }
    }

    /**
     * Handles the error() dynamic call.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by error().
     */
    public function doError(IEvent $Event)
    {
        // Validate Arguments
        if (isset($Event->Arguments) ? count($Event->Arguments) > 0 : false) {

            // Get arguments
            list ($Message) = $Event->Arguments;

            // Stop further event triggers
            $Event->stopPropagation();

            // Is $Message a string?
            if (is_string($Message) ?  : is_callable(array(
                $Message,
                '__toString'
            ))) {

                // Does logger exist? Log
                if ($this->logger instanceof LoggerInterface) {
                    $this->logger->error($Message);
                }

                // Error message
                trigger_error($Message, E_USER_WARNING);
            }
        }
    }

    /**
     * Handles the exception() dynamic call.
     *
     * @throws \RuntimeException With message passed to exception()
     *
     * @param \BLW\Type\IEvent $Event
     *            Event triggered by exception().
     * @return void
     */
    public function doException(IEvent $Event)
    {
        // Validate Arguments
        if (isset($Event->Arguments) ? count($Event->Arguments) > 0 : false) {

            // Get arguments
            list ($Message) = $Event->Arguments;

            // Stop further event triggers
            $Event->stopPropagation();

            // Is $Message a string?
            if (is_string($Message) ?  : is_callable(array(
                $Message,
                '__toString'
            ))) {

                // Does logger exist? Log
                if ($this->logger instanceof LoggerInterface) {
                    $this->logger->critical($Message);
                }

                // Exception
                throw new RuntimeException($Message, E_USER_ERROR);
            }
        }
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
            // IBrowser
            case 'Client':
                return $this->_Client;
            case 'RequestFactory':
                return $this->_RequestFactory;
            case 'Plugins':
                return $this->_Plugins;
            case 'Engines':
                return $this->_Engines;
            case 'UserAgent':
                return $this->getUserAgent();
            // IWrapper
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
                return $this->_Status !== null;
            case 'Serializer':
                return $this->getSerializer() !== null;
            // IIterable
            case 'Parent':
                return $this->_Parent !== null;
            case 'ID':
                return $this->getID() !== null;
            // IBrowser
            case 'Client':
                return $this->_Client !== null;
            case 'RequestFactory':
                return $this->_RequestFactory !== null;
            case 'Plugins':
                return $this->_Plugins !== null;
            case 'Engines':
                return $this->_Engines !== null;
            case 'UserAgent':
                return $this->getUserAgent() !== null;
            // IComponentMapable
            case 'Component':
                return $this->_Component !== null;
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
        // Try to set property
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
            // IComponentMapable
            case 'Component':
            // IBrowser
            case 'Client':
            case 'RequestFactory':
            case 'Plugins':
            case 'Engines':
                $result = IDataMapper::READONLY;
                break;
            case 'UserAgent':
                $result = $this->setUserAgent($value);
                break;
            // IWrapper
            default:

                // Try to set component property
                try {
                    $this->_Component->{$name} = $value;
                    $result = IDataMapper::UPDATED;

                // @codeCoverageIgnoreStart

                // Error
                } catch (\Exception $e) {
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
     * Unmap dynamic properties from DataMapper.
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
            // Undefined property
        }
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
