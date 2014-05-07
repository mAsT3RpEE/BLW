<?php
/**
 * IClient.php | Apr 11, 2014
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

use BLW\Type\IObjectStorage;
use BLW\Type\IFile;
use BLW\Type\IStream;
use BLW\Type\IMediatable;


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
 * Interface for HTTP client objects
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
 * command.</li>
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
 * </pre>
 *
 * <hr>
 *
 * @package BLW\HTTP
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 *
 * @property string $_CookieFormat [protected] Cookie format for clients that support more than 1 cookie format.
 * @property string $_Proxy [protected] Proxy information in the format <code>type://[username[:password]@]host[:port]</code>.

 *           <h4>Example</h4>
 *
 *           <pre>socks4://anonymous:@host.com:8080</pre>
 *
 *           <hr>
 *
 * @property \BLW\Type\IFile $_TempDir [protected] The directory to store temporary files (cache, cookies, etc).
 * @property int $_MaxConnections [protected] Maximum connections to a server to make at once.
 * @property int $_MaxRequests [protected] Maximum number of <code>IRequest</code> to run at once.
 * @property int $_MaxRate [protected] Maximum rate of <code>IRequest</code> to execute per minute.
 */
interface IClient extends \BLW\Type\IObjectStorage, \BLW\Type\IMediatable, \BLW\Type\IFactory
{
    // COOKIE TYPES
    const COOKIE_DEFAULT = 'default';
    const COOKIE_JSON    = 'json';
    const COOKIE_SQLITE  = 'sqlite';

    /**
     * Creates a temporary file to store cookies in.
     *
     * @api BLW
     * @since 1.0.0
     *
     * @param \BLW\Type\IFile $TempDir
     *            Directory to create file in. Pass <code>null</code> for sys_get_temp_dir().
     *            <h4>Note</h4>
     *
     *            <p>If an actual file is passed then the directory
     *            the file resides in will be used instead.</p>
     *
     *            <hr>
     * @return \BLW\Type\IFile Created file.
     */
    public static function createCookieFile(IFile $TempDir = null);

    /**
     * Ques a request for handling in the client.
     *
     * <h4>Note</h4>
     *
     * <p>Function calls IClient::run() for 100msec apon successfull addition</p>
     *
     * <hr>
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\HTTP\IClient::run() IClient::run()
     * @see \BLW\Type\HTTP\IClient::sendAll() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::download() IClient::downoad()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Request</code> is not valid.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to que.
     * @return bool <code>TRUE</code> if successful. <code>FALSE</code> if unsuccessful or if the request is already qued.
     */
    public function send(IRequest $Request);

    /**
     * Ques a group of requests for handling in the client
     *
     * @api BLW
     * @since 1.0.0
     * @uses \BLW\Type\HTTP\IClient::send() IClient::send()
     * @see \BLW\Type\HTTP\IClient::run() IClient::run()
     * @see \BLW\Type\HTTP\IClient::download() IClient::downoad()
     *
     * @throws \BLW\Model\InvalidArgumentException If:
     *         <ul>
     *         <li><code>$Requests</code> is not an <code>array</code> on an instance of <code>Traversable</code></li>
     *         <li>Any <code>$Requests</code> item is not valid or not an instance of <code>IRequest</code>.
     *         </ul>
     *
     * @param array|Traversable $Requests
     *            Container of requests to be added.
     * @return bool <code>TRUE</code> if successful. <code>FALSE</code> if unsuccessful or if all requests were already qued.
     */
    public function sendAll($Requests);

    /**
     * Return the number of requests scheduled to run.
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\HTTP\IClient::countFinished() IClient::countFinished()
     * @see \BLW\Type\HTTP\IClient::countRunning() IClient::countRunning()
     *
     * @return int Non-Running and Non-Finished requests.
     */
    public function countScheduled();

    /**
     * Return the number of requests that have finished.
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\HTTP\IClient::countScheduled() IClient::countScheduled()
     * @see \BLW\Type\HTTP\IClient::countRunning() IClient::countRunning()
     *
     * @return int Finished requests.
     */
    public function countFinished();

    /**
     * Return the number of requests actively being executed.
     *
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\HTTP\IClient::countScheduled() IClient::countScheduled()
     * @see \BLW\Type\HTTP\IClient::countFinished() IClient::countFinished()
     *
     * @return int Running requests.
     */
    public function countRunning();

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
     * @api BLW
     * @since 1.0.0
     *
     * @internal Implementors should manage $Response[Running] and $Response[Finished] flags.
     * @see \BLW\Type\HTTP\IClient::send() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::sendAll() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::download() IClient::download()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Timelimit</code> is not an integer.
     *
     * @param int $TimeLimit
     *            Maximum time to run in milliseconds. Enter -1 to run till all requests have finished.
     * @return int Number of new requests finished during run.
     */
    public function run($TimeLimit = -1);

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
     * @api BLW
     * @since 1.0.0
     * @see \BLW\Type\HTTP\IClient::run() IClient::run()
     * @see \BLW\Type\HTTP\IClient::send() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::sendAll() IClient::sendAll()
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to download from.
     * @param \BLW\Type\IStream $Stream
     *            Stream to download file to.
     * @return bool <code>TRUE</code> if successful. <code>FALSE</code> otherwise.
     */
    public function download(IRequest $Request, IStream $Stream);
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
