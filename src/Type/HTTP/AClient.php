<?php
/**
 * AClient.php | Apr 11, 2014
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

use Traversable;
use ReflectionMethod;
use BLW\Type\IURI;
use BLW\Type\IFile;
use BLW\Type\IStream;
use BLW\Type\IMediator;
use BLW\Model\GenericFile;
use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericEvent as Event;
use BLW\Model\HTTP\Response\Generic as Response;
use BLW\Type\AMediatableObjectStorage;

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
 * Base class for HTTP client objects
 *
 * <h3>Introduction</h3>
 *
 * <p>The <coce>Client</code> class is responsible for handling
 * all HTTP requests. It is used by <code>IBrowser</code> objects
 * but can also be used stand alone.</p>
 *
 * <p>Class responsibilities include:</p>
 *
 * <ul>
 * <li>Validating requests and placing them into a que for
 * processing.</li>
 * <li>Converting <code>IRequest</code> objects into a format
 * ready for transport.</li>
 * <li>Creation and set up of Transport class / management of
 * API to handle actual sending of requests.</li>
 * <li>Handling HTTP cookie headers.</li>
 * <li>Handling HTTP caching headers.</li>
 * <li>Handling HTTP redirect headers.</li>
 * </ul>
 *
 * <h3>Methodology</h3>
 *
 * <p>It recieves instances of <code>IRequest</code> which it
 * validates and stores into a que for processing</p>
 *
 * <p>Once in the que, the client should handle requests in
 * a 1st come 1st served fasion. Optimally requests should
 * be sent in parrallel but this is up to the class itself.</p>
 *
 * <p>Operations should be managed by the
 * <code>IClient::run()</code><p>
 *
 * <h3>Note to users</h3>
 *
 * <ul>
 * <li>Requests should be added to que with
 * <code>IClient::send()</code> and <code>IClient::sendAll()</code>
 * methods.</li>
 * <li>Requests that are no longer in use should be removed with
 * <code>IClient::detach()</code> in order to conserve ram.</li>
 * <li>The <code>IClient::run()</code> method allows for
 * intermittent running of client. However this should not
 * be abused. The run command should be allowed to run to
 * completion at some point in your code. Instead use timeouts
 * to limit execution of requests. Errors due to corruption of
 * data caused by misuse of <code>IClient::run()</code> will not
 * be considered bugs.</li>
 * <li>The <code>IClient::run()</code> method should not be used
 * as an indication of what is happening inside the client.
 * Interaction between client and outside world should instead
 * be managed using events that the client produces during its
 * execution</li>
 * <li>Client should never be serialized except for debugging
 * porposes. Instead keep a separate list of Requests and
 * their corresponding respones and serialise those instead.
 * <b>Do not attempt to serialize a running request</b>.</li>
 * <li>Requests whose response bodies are larger than <b>8MB</b>
 * should be made using the <code>IClient::download()</code>
 * command.
 * </ul>
 *
 * <h3>Note to Implementors</h3>
 *
 * <ul>
 * <li>In addition to handling HTTP requests a http client
 * is also responsible for handling cookies an browser
 * cache using header information from server. This should
 * be delagated to another class as a client extention /
 * plugin.</li>
 * <li>Communication between client and other classes should
 * be done via the event mechanism built into BLW library.</li>
 * <li>Requests initiated by <code>send()</code> and
 * <code>sendALL()</code> should cap responses to 8MB.</li>
 * <li>Running requests removed with <code>detach()</code>
 * should be handled gracefully by client.</li>
 * </ul>
 *
 * <h3>Summary</h3>
 *
 * <pre>
 * +---------------------------------------------------+       +------------------+       +-------------------+
 * | CLIENT                                            |<------| OBJECTSTORAGE    |<--+---| SplObjectStorage  |
 * +---------------------------------------------------+       | ================ |   |   +-------------------+
 * | COOKIE_DEFAULT                                    |       | Request          |   |   | SERIALIZABLE      |
 * | COOKIE_JSON                                       |       +------------------+   |   | ================= |
 * | COOKIE_SQLITE                                     |<------| MEDIATABLE       |   +---| Serializable      |
 * +---------------------------------------------------+       +------------------+   |   +-------------------+
 * | _CookieFormat:    string                          |<------| FACTORY          |   +---| ITERABLE          |
 * | _Proxy:           string                          |       | ================ |       +-------------------+
 * | _TempDir:         IFile                           |       | createCookieFile |
 * | _MaxConnections:  int                             |       +------------------+
 * | _MaxRequests:     int                             |
 * | _MaxRate:         int                             |
 * +---------------------------------------------------+
 * | createCookieFile(): IFile                         |
 * |                                                   |
 * | $TempDir:  IFile                                  |
 * +---------------------------------------------------+
 * | validateRequest(): bool                           |
 * |                                                   |
 * | $Request:  IRequest                               |
 * +---------------------------------------------------+
 * | send(): bool                                      |
 * |                                                   |
 * | $Request:  IRequest                               |
 * +---------------------------------------------------+
 * | sendAll(): bool                                   |
 * |                                                   |
 * | $Requests:  array(IRequest)                       |
 * +---------------------------------------------------+
 * | run(): int                                        |
 * |                                                   |
 * | $Time:  int(msec)                                 |
 * +---------------------------------------------------+
 * | download(): bool                                  |
 * |                                                   |
 * | $Request:  IRequest                               |
 * | $Stream:   IStream                                |
 * +---------------------------------------------------+
 * | countScheduled(): int                             |
 * +---------------------------------------------------+
 * | countFinished(): int                              |
 * +---------------------------------------------------+
 * | countRunning(): int                               |
 * +---------------------------------------------------+
 * | translate(): mixed                                |
 * |                                                   |
 * |                                                   |
 * | $Request:  IRequest                               |
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
 */
abstract class AClient extends \BLW\Type\AMediatableObjectStorage implements \BLW\Type\HTTP\IClient
{

#############################################################################################
# Client Trait
#############################################################################################

    /**
     * Cookie format for clients that support more than 1 cookie format.
     *
     * @var string $_CookieFormat
     */
    protected $_CookieFormat = IClient::COOKIE_DEFAULT;

    /**
     * Proxy information in the format <code>type://[username[:password]@]host[:port]</code>.
     *
     * <h4>Example</h4>
     *
     * <pre>socks4://anonymous:@host.com:8080</pre>
     *
     * <hr>
     *
     * @var string $_Proxy
     */
    protected $_Proxy = '';

    /**
     * The directory to store temporary files (cache, cookies, etc).
     *
     * @var \BLW\Type\IFile $_TempDir
     */
    protected $_TempDir = null;

    /**
     * Maximum connections to a server to make at once.
     *
     * @var int $_MaxConnections
     */
    protected $_MaxConnections = 4;

    /**
     * Maximum number of <code>IRequest</code> to run at once.
     *
     * @var int $_MaxRequests
     */
    protected $_MaxRequests = 16;

    /**
     * Maximum rate of <code>IRequest</code> to execute per minute.
     *
     * @var int $_MaxRate
     */
    protected $_MaxRate = 3;

#############################################################################################




#############################################################################################
# ObjectStorage Trait
#############################################################################################

    /**
     * Adds an object in the storage.
     *
     * @link http://www.php.net/manual/en/splobjectstorage.attach.php SplObjectStorage::attatch()
     *
     * @event Request.New
     *
     * @param \BLW\Type\HTTP\IRequest $object
     *            The object to add.
     * @param \BLW\Type\HTTP\IResponse $data
     *            [optional] The data to associate with the object.
     */
    public function attach($object, $data = null)
    {
        // Validate $object
        if (! $this->validateRequest($object)) {
            throw new InvalidArgumentException(0);

        // Validate $data
        } elseif (! $data instanceof IResponse) {
            throw new InvalidArgumentException(1);

        } else {

            // Does mediator exist?
            if ($this->_Mediator instanceof IMediator) {
                // Request.New event
                $this->_do('Request.New', new Event($this, array(
                    'Request'  => &$object,
                    'Response' => &$data
                )));

            }

            // Add request
            return parent::attach($object, $data);
        }
    }

    /**
     * Removes an object from the storage.
     *
     * @link http://www.php.net/manual/en/splobjectstorage.detach.php SplObjectStorage::detatch()
     *
     * @event Request.Remove
     *
     * @param object $object
     *            The object to remove.
     * @return object The object to remove.
     */
    public function detach($object)
    {
        // Does the object exist?
        if ($this->contains($object)) {

            // Does mediator exist?
            if ($this->_Mediator instanceof IMediator) {
                // Request.Remove event
                $this->_do('Request.Remove', new Event($this, array(
                    'Request'  => &$object,
                    'Response' => $this[$object]
                )));
            }

            // Remove request
            return parent::detach($object);
        }
    }

#############################################################################################
# Factory trait
#############################################################################################

    /**
     * Return an array of factory methods associated with the class.
     *
     * @return \ReflectionMethod[] Array of factory methods.
     */
    public static function getFactoryMethods()
    {
        return array(
            new ReflectionMethod(get_called_class(), 'createCookieFile')
        );
    }

    /**
     * Creates a temporary file to store cookies in.
     *
     * @link http://www.php.net/manual/en/function.sys-get-temp-dir.php sys_get_temp_dir()
     *
     * @throws \BLW\Model\InvalidArgumentException If $TempDir or sys_get_temp_dir() is not writable.
     *
     * @param \BLW\Type\IFile $TempDir
     *            Directory to create file in. Pass <code>null</code> for sys_get_temp_dir().
     *
     * <h4>Note</h4>
     *
     * <p>If an actual file is passed then the directory
     * the file resides in will be used instead.</p>
     *
     * <hr>
     *
     * @return \BLW\Type\IFile Created file.
     */
    public static function createCookieFile(IFile $TempDir = null)
    {
        // Default $TempDir
        $TempDir = $TempDir ?: sys_get_temp_dir();

        // Handle Files
        if (is_file($TempDir) && ! is_dir($TempDir)) {
            $TempDir = dirname($TempDir);
        }

        // Test Directory
        if (is_dir($TempDir) && is_writable($TempDir)) {

            // Build Path
            $File = sprintf('%s%s%s.cookie.jar', $TempDir, DIRECTORY_SEPARATOR, basename(get_called_class()));

            // Return file
            return new GenericFile($File);

        // Directory not writable
        } else {
            throw new InvalidArgumentException(0, '%header% Directory not writable');
        }
    }

#############################################################################################
    // Client Trait
#############################################################################################

    /**
     * Validates an instance of IRequest.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Object to test.
     * @return boolean <code>TRUE</code> if valid. <code>FALSE</code> otherwise.
     */
    public static function validateRequest($Request)
    {
        // Validate $Request
        switch (true) {
            // $Request not a request
            case ! $Request instanceof IRequest:
            // $Request has no target URI
            case ! $Request->getURI() instanceof IURI:
            // No Timeout
            case ! isset($Request->Config['Timeout']):
            // No MaxRedirects
            case ! isset($Request->Config['MaxRedirects']):
            // No EnableCookies
            case ! isset($Request->Config['EnableCookies']):
                return false;
            // All is well
            default:
                return true;
        }
    }

    /**
     * Ques a request for handling in the client.
     *
     * @see \BLW\Type\HTTP\IClient::run() IClient::run()
     * @see \BLW\Type\HTTP\IClient::sendAll() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::download() IClient::downoad()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Request</code> is not valid.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to que.
     * @return boolean <code>TRUE</code> if successful. <code>FALSE</code> if unsuccessful or if the request is already qued.
     */
    public function send(IRequest $Request)
    {
        // Does Client contain request?
        if ($this->contains($Request)) {
            return false;
        }

        // Is request $Valid? Add to que.
        if (! $this->validateRequest($Request)) {
            throw new InvalidArgumentException(0);
        }

        // Create Response
        $Response             = new Response('HTTP', '1.0', 0);
        $Response['Running']  = false;
        $Response['Finished'] = false;

        $Response->setRequestURI($Request->getURI());

        // Add to que
        $this->attach($Request, $Response);

        // Done
        return true;
    }

    /**
     * Ques a group of requests for handling in the client
     *
     * @uses \BLW\Type\HTTP\IClient::send() IClient::send()
     * @see \BLW\Type\HTTP\IClient::run() IClient::run()
     * @see \BLW\Type\HTTP\IClient::download() IClient::downoad()
     *
     * @throws \BLW\Model\InvalidArgumentException If:
     *
     * <ul>
     * <li><code>$Requests</code> is not an <code>array</code> on an instance of <code>Traversable</code></li>
     * <li>Any <code>$Requests</code> item is not valid or not an instance of <code>IRequest</code>.
     * </ul>
     *
     * @param array|Traversable $Requests
     *            Container of requests to be added.
     * @return integer Total number of successful requests.
     */
    public function sendAll($Requests)
    {
        $self     = $this;
        $Validate = function (&$v, $i) use ($self) {
            return $v = $v && $self->validateRequest($i);
        };

        // Is $Requests traversable? Convert.
        if ($Requests instanceof Traversable) {
            $Requests = iterator_to_array($Requests);
        }

        // Is $Requests an array?
        if (! is_array($Requests)) {
            throw new InvalidArgumentException(0);
        }

        // Validate each item
        elseif (! array_reduce($Requests, $Validate, true)) {
            throw new InvalidArgumentException(0);
        }

        // Okay all are valid. add them 1 by 1
        $count = 0;

        foreach ($Requests as $Request) {

            // If error return false
            if ($this->send($Request)) {
                $count ++;
            }
        }

        // Done
        return $count;
    }

    /**
     * Return the number of requests scheduled to run.
     *
     * @see \BLW\Type\HTTP\IClient::countFinished() IClient::countFinished()
     * @see \BLW\Type\HTTP\IClient::countRunning() IClient::countRunning()
     *
     * @return integer Non-Running and Non-Finished requests.
     */
    public function countScheduled()
    {
        // Loop through each request
        $count = 0;

        foreach ($this as $Request) {
            if (($Response = $this[$Request]) instanceof IResponse) {

                // Response not running and not finished
                if ((! isset($Response['Running']) ?: ! $Response['Running']) && (! isset($Response['Finished']) ?: ! $Response['Finished'])) {
                    $count ++;
                }
            }
        }

        // Done
        return $count;
    }

    /**
     * Return the number of requests that have finished.
     *
     * @see \BLW\Type\HTTP\IClient::countScheduled() IClient::countScheduled()
     * @see \BLW\Type\HTTP\IClient::countRunning() IClient::countRunning()
     *
     * @return integer Finished requests.
     */
    public function countFinished()
    {
        // Loop through each request
        $count = 0;

        foreach ($this as $Request) {
            if (($Response = $this[$Request]) instanceof IResponse) {

                // Response is finished
                if (isset($Response['Finished']) ? (bool) $Response['Finished'] : false) {
                    $count ++;
                }
            }
        }

        // Done
        return $count;
    }

    /**
     * Return the number of requests actively being executed.
     *
     * @see \BLW\Type\HTTP\IClient::countScheduled() IClient::countScheduled()
     * @see \BLW\Type\HTTP\IClient::countFinished() IClient::countFinished()
     *
     * @return integer Running requests.
     */
    public function countRunning()
    {
        // Loop through each request
        $count = 0;

        foreach ($this as $Request) {
            if (($Response = $this[$Request]) instanceof IResponse) {

                // Response not running and not finished
                if (isset($Response['Running']) ? (bool) $Response['Running'] : false) {
                    $count ++;
                }
            }
        }

        // Done
        return $count;
    }

    /**
     * Execute requests.
     *
     * <h4>Note</h4>
     *
     * <p>Function returns after the specified ammount
     * of time or until all requests have finished.</p>
     *
     * <hr>
     *
     * @internal Implementors should manage $Response[Running] and $Response[Finished] flags.
     * @see \BLW\Type\HTTP\IClient::send() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::sendAll() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::download() IClient::download()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Timelimit</code> is not an integer.
     *
     * @param integer $TimeLimit
     *            Maximum time to run in milliseconds. Enter -1 to run till all requests have finished.
     * @return integer Number of new requests finished during run.
     */
    abstract public function run($TimeLimit = -1);

    /**
     * Downloads a file into a stream.
     *
     * <h4>Note</h4>
     *
     * <p>This is used to handle responses larger than 8MB.</p>
     *
     * <p>This is a blocking function and will not return
     * till either the request is finished on an error
     * occurs.</p>
     *
     * <hr>
     *
     * @see \BLW\Type\HTTP\IClient::run() IClient::run()
     * @see \BLW\Type\HTTP\IClient::send() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::sendAll() IClient::sendAll()
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to download from.
     * @param \BLW\Type\IStream $Stream
     *            Stream to download file to.
     * @return boolean <code>TRUE</code> if successful. <code>FALSE</code> otherwise.
     */
    abstract public function download(IRequest $Request, IStream $Stream);

    /**
     * Converts a request for transport.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to translate.
     * @return mixed Translated HTTP request.
     */
    abstract public function translate(IRequest $Request);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
