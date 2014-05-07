<?php
/**
 * cURL.php | Apr 11, 2014
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
namespace BLW\Model\HTTP\Client;

use DateTime;
use RuntimeException;

use BLW\Type\IStream;
use BLW\Type\IFile;
use BLW\Type\IMediator;
use BLW\Type\MIME\IHeader;
use BLW\Type\HTTP\IRequest;
use BLW\Type\HTTP\IResponse;
use BLW\Type\HTTP\IClient;

use BLW\Model\InvalidArgumentException;
use BLW\Model\GenericURI;
use BLW\Model\Event\Generic as Event;
use BLW\Model\MIME\Referer;
use BLW\Model\HTTP\Response\Generic as Response;
use BLW\Model\HTTP\Request\Generic as Request;


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
 * cURL based HTTP client
 *
 * @package BLW\HTTP
 * @api BLW
 * @since 1.0.0
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class cURL extends \BLW\Type\HTTP\AClient
{

    /**
     *
     * @var \BLW\Model\HTTP\Client\cURL\Helper $_Helper [private] cURL helper class.
     */
    private $_Helper = null;

    /**
     * Constructor
     *
     * @uses \BLW\Model\HTTP\Client\cURL\Helper Helper
     *
     * @throws \RuntimeException If cURL is not detected.
     *
     * @param string $Cookie
     *            [optional] Type of cookie (Only IClient::COOKIE_DEFAULT) for now.
     * @param
     *            \BLW\Type\IFile [optional] $TempDir The directory to store temporary files (cache, cookies, etc).
     * @param string $Proxy
     *            [optional] Proxy information in the format <code>type://[username[:password]@]host[:port]</code>.
     *
     *            <h4>Example</h4>
     *
     *            <pre>socks4://anonymous:@host.com:8080</pre>
     *
     *            <hr>
     *
     * @param int $MaxConnections
     *            [optional] Maximum connections to a server to make at once.
     * @param int $MaxRequests
     *            [optional] Maximum number of <code>IRequest</code> to run at once.
     * @param int $MaxRate
     *            [optional] Maximum rate of <code>IRequest</code> to execute per minute.
     */
    public function __construct($Cookie = IClient::COOKIE_DEFAULT, IFile $TempDir = null, $Proxy = '', $MaxConnections = 4, $MaxRequests = 16, $MaxRate = 6)
    {
        // @codeCoverageIgnoreStart

        // Check cURL
        if (! extension_loaded('curl') || ! is_callable('curl_init') || ! is_callable('curl_multi_init'))
            throw new RuntimeException('cURL extention required');

        // @codeCoverageIgnoreEnd

        // Parameters
        $this->_CookieFormat   = @substr($Cookie, 0, 16); // I use substr instead of strval
        $this->_TempDir        = $TempDir;                // cause strval can stop execution
        $this->_Proxy          = @substr($Proxy, 0, 1024);
        $this->_MaxConnections = @intval($MaxConnections);
        $this->_MaxRequests    = @intval($MaxRequests);
        $this->_MaxRate        = @intval($MaxRate);

        // CurlHelper
        $this->_Helper = new cURL\Helper($MaxRequests);

        // Mediator ID
        $this->_MediatorID = basename(get_class($this));
    }

    /**
     * Converts a request for transport.
     *
     * <h4>Note</h4>
     *
     * <p>Returns empty array on error</p>
     *
     * <hr>
     *
     * @api BLW
     *
     * @since 1.0.0
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to translate.
     * @return mixed Translated HTTP request.
     */
    public function translate(IRequest $Request)
    {
        static $Headers;

        // Return format (Array for curl_set_opt_array())
        $Options = array();

        // Filters illegal headers and returns cURL compatible headers
        $Headers = $Headers ?: function ($Request)
        {
            // Default
            $return = array();

            // Loop through each header
            foreach ($Request->getHeader() as $Header)
                if ($Header instanceof IHeader) {

                    // Filter out headers cURL should handle
                    switch ($Header->getType()) {
                        case 'Content-Length':
                        case 'User-Agent':
                        case 'Referer':
                        case 'Accept-Encoding':
                            continue;

                        // Convert header
                        default:
                            $return[] = strval($Header);
                    }
                }

            // Done
            return $return;
        };

        // Just in case
        if (($Response = $this[$Request]) instanceof IResponse) {

            // Build options
            $Options = array(
                CURLOPT_CUSTOMREQUEST => $Request->Type,                    // Request type (GET | POST | ETC)
                CURLOPT_HTTP_VERSION => $Response->Protocol === '1.0' ?     // Request protoccol
                CURL_HTTP_VERSION_1_0 : CURL_HTTP_VERSION_1_1,              // Default protocol

                CURLOPT_CONNECTTIMEOUT => $Request->Config['Timeout'],      // Connection timeout
                CURLOPT_MAXREDIRS => $Request->Config['MaxRedirects'],      // Maximum number of redirects
                CURLOPT_RETURNTRANSFER => true,                             // Return transfer
                CURLOPT_HEADER => true,                                     // Return header
                CURLOPT_HTTPHEADER => $Headers($Request),                   // Headers
                CURLOPT_ENCODING => "",                                     // Accept-Encoding
                CURLOPT_AUTOREFERER => true,                                // Automatically update 'Referer:'
                CURLOPT_FOLLOWLOCATION => true,                             // Follow 'Location:' headers
                CURLOPT_SSL_VERIFYPEER => 1,                                // Verify certs
                CURLOPT_SSL_VERIFYHOST => 2                                 // Verify hosts
            );

            // URI
            $URI                    = $Request->URI;
            $Parts                  = iterator_to_array($URI);      // Al parts
            $Parts['fragment']      = '';                           // strip fragment
            $Parts['port']          = '';                           // strip port
            $Parts['userinfo']      = '';                           // strip userinfo
            $Options[CURLOPT_URL]   = $URI->createURIString($Parts);// rebuild

            // Port
            if ($URI['port'])
                $Options[CURLOPT_PORT] = intval($URI['port']);

            // Authorization
            if ($URI['userinfo'])
                $Options[CURLOPT_USERPWD] = $URI['userinfo'];

            // Referer
            if ($URI = $Request->Referer) {

                // Create header
                $Header = new Referer($URI);

                // Set referer
                $Options[CURLOPT_REFERER] = $Header->getValue();
            }

            elseif (($Header = $Request->Header->getHeader('Referer')) instanceof IHeader) {

                // Set referer
                $Options[CURLOPT_REFERER] = $Header->getValue();
            }

            // User-Agent
            if (($Header = $Request->Header->getHeader('User-Agent')) instanceof IHeader) {

                // set User Agent
                $Options[CURLOPT_USERAGENT] = $Header->getValue();
            }

            // Body
            $Body = rtrim($Request->Body);

            if (! empty($Body))
                $Options[CURLOPT_POSTFIELDS] = $Body;

            // Cookies
            if ($Request->Config['EnableCookies']) {

                // File
                $CookieFile = strval($this->createCookieFile($this->_TempDir));

                $Options[CURLOPT_COOKIEFILE] = $CookieFile;
                $Options[CURLOPT_COOKIEJAR]  = $CookieFile;
            }
        }

        // Done
        return $Options;
    }

    /**
     * Checks cURL multi for event messages.
     *
     * <h4>Note</h4>
     *
     * <p>Function changes <code>$Response[Finished]</code>
     * / <code>$Response[Running]</code> flags.</p>
     *
     * <hr>
     *
     * @api BLW
     *
     * @since 1.0.0
     * @internal Finishes requests with <code>cURL::_finish()</code>.</p>
     *
     * @event Request.Finished
     *
     * @return int Number of finished Requests found.
     */
    public function processMessages()
    {
        // Storage for finished requests
        $count = 0;

        // Are there any finished sessions?
        while ($Info = curl_multi_info_read($this->_Helper->MainHandle))
            switch ($Info['msg']) {

                // Finished requests
            case CURLMSG_DONE:

                // Finish request / Free resources
                $this->_finish($this->_findByHandle($Info['handle']));

                // Update count
                $count ++;

                // Done
                break;
            }

        // Done
        return $count;
    }

    /**
     * Updates running requests.
     *
     * @api BLW
     * @since 1.0.0
     * @event Request.Update
     */
    public function update()
    {
        // Loop through each request
        foreach ($this as $Request)
            if (($Response = $this[$Request]) instanceof IResponse) {

                // Is request Running?
                if (isset($Response['Running']) ? !! $Response['Running'] : false) {

                    // Does Handle exist?
                    if (isset($Response['handle']) ? is_resource($Response['handle']) : false)
                        // Update
                        $this->_update($Request);

                    // No hande?
                    else
                        // Re-schedule request
                        $this->_reschedule($Request);
                }
            }
    }

    /**
     * Schedule requests to run.
     *
     * @codeCoverageIgnore
     */
    public function schedule()
    {
        // Loop through each request
        foreach ($this as $Request)
            if (($Response = $this[$Request]) instanceof IResponse) {

                // Is request Running? next.
            if (isset($Response['Running']) ? ! ! $Response['Running'] : false)
                    continue;

                    // Is request Finished? next.
            if (isset($Response['Finished']) ? ! ! $Response['Finished'] : false)
                    continue;

                    // Retrieve resources
            $Handle = $this->_lock($Request->URI['host']);

                // Check results. Send request to transport
            if (is_resource($Handle))
                    $this->_start($Request, $Handle);
            }
    }

    /**
     * Execute requests in que.
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
     * @throws \RuntimeException If a curl exception with a message is generated.
     * @throws \BLW\Model\InvalidArgumentException If <code>$Timelimit</code> is not an integer.
     *
     * @param int $TimeLimit
     *            Maximum time to run in milliseconds. Enter -1 to run till all requests have finished.
     * @return int Number of new requests finished during run.
     */
    public function run($TimeLimit = -1)
    {
        // Validate $TimeLimit
        if (! is_int($TimeLimit)) {
            throw new InvalidArgumentException(0);
            return 0;
        }

        // Is time limit positive
        $TimeLimit = $TimeLimit >= 0
            ? floatval($TimeLimit) / 1000   // Time limit in microtime format
            : (float) PHP_INT_MAX;          // Default to PHP_INT_MAX

        // Repeat until all requests are handled or time limit has passed
        $Start = microtime(true);
        $Running = true;
        $return = 0;

        while ($Running && microtime(true) - $Start < $TimeLimit) {
            // 1. Schedule new requests
            $this->schedule();

            // 2. Start new requests
            while (($result = curl_multi_exec($this->_Helper->MainHandle, $Running)) == CURLM_CALL_MULTI_PERFORM);

            // Check results
            if ($result != CURLM_OK) {

                // Is there a message?
                if ($Message = curl_error($this->_Helper->MainHandle)) {

                    // Throw exception
                    throw new RuntimeException($Message, curl_errno($this->_Helper->MainHandle));
                }

                // Error. stop execution
                break;
            }

            // 3. Wait
            $result = curl_multi_select($this->_Helper->MainHandle, 0.2);

            // 4. Check results
            if ($result > 0 || ! $Running) {

                // Process messages
                $return += $this->processMessages();

                // Update Running requests
                $this->update();
            }

            // 5. Check que
            if (! $Running && $this->countScheduled())
                $Running = 1;

            // 6. Allow event ques to empty (don't know if this is how to do it in PHP)
            else
                usleep(10000);
        }

        // Done
        return $return;
    }

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
     * @link http://www.php.net/manual/en/function.curl-init.php curl_init()
     * @link http://www.php.net/manual/en/function.curl-setopt-array.php curl_setopt_array()
     * @link http://www.php.net/manual/en/function.curl-exec.php curl_exec()
     *
     * @see \BLW\Type\HTTP\IClient::run() IClient::run()
     * @see \BLW\Type\HTTP\IClient::send() IClient::sendAll()
     * @see \BLW\Type\HTTP\IClient::sendAll() IClient::sendAll()
     *
     * @throws \RuntimeException if there is an error executing curl_init() or curl_setopt_array()
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to download from.
     * @param \BLW\Type\IStream $Stream
     *            Stream to download file to.
     * @return bool <code>TRUE</code> if successful. <code>FALSE</code> otherwise.
     */
    public function download(IRequest $Request, IStream $Stream)
    {
        // Readonly options
        $Options = array(
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_WRITEFUNCTION  => function ($h, $d) use($Stream)
            {
                return fwrite($Stream->fp, $d);
            }
        );

        if ($Handle = curl_init()) {

            // Set up options
            if (! curl_setopt_array($Handle, $Options + $this->translate($Request)))

                throw new RuntimeException(curl_error($Handle), curl_errno($Handle));

            // Block and execute till finished
            $return = curl_exec($Handle);

            // Close handle
            curl_close($Handle);

            // Flush output
            fflush($Stream->fp);

            // Done
            return !! $return;
        }

        // Error
        else
            throw new RuntimeException('Unable to initialize cURL session');

        // Error
        return false;
    }

    /**
     * Retrieves a curl connection to use.
     *
     * @ignore
     * @codeCoverageIgnore
     *
     * @param string $Host
     * @return resource Locked resource. <code>FALSE</code>
     */
    private function _lock($Host)
    {
        // Is current request rate too high
        if ($this->_Helper->getRate() >= floatval($this->_MaxRate))
            return false;

        // Are connections to host maxed out?
        if ($this->_Helper->getConnections($Host) >= $this->_MaxConnections)
            return false;

        // Return free handle if any exists
        return $this->_Helper->getFreeHandle();
    }

    /**
     * Start a Request.
     *
     * @ignore
     * @codeCoverageIgnore
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     * @param resource $Handle
     */
    private function _start(IRequest $Request, $Handle)
    {
        // Validate $Request and $Handle
        if (($Response = $this[$Request]) instanceof IResponse && is_resource($Handle)) {

            // Execute request
            try {

                $this->_Helper->execute($Handle, $this->translate($Request));

                // Store handle
                $Response['handle']   = $Handle;

                // Update running flag
                $Response['Running']  = true;
                $Response['Finished'] = false;
            }

            // Forward exceptions
            catch (RuntimeException $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * Reschedules a request for processing.
     *
     * @ignore
     * @codeCoverageIgnore
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     */
    private function _reschedule(IRequest $Request)
    {
        $Response = new Response('HTTP', '1.0', 0);
        $Response['Running'] = false;
        $Response['Finished'] = false;

        $Response->setRequestURI($Request->getURI());

        $this[$Request] = $Response;
    }

    /**
     * Does grunt work of finishing a request.
     *
     * @ignore
     * @codeCoverageIgnore
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     */
    private function _finish(IRequest $Request)
    {
        // Validate $Request
        if (($Response = $this[$Request]) instanceof IResponse) {

            $Handle = $Response['handle'];

            // Create Response

            // Is code known?
            if ($Response->isValidCode(intval(curl_getinfo($Handle, CURLINFO_HTTP_CODE)))) {

                // parse response
                $Response = Response::createFromString(curl_multi_getcontent($Handle));

                // Update handle
                $Response['handle'] = $Handle;

                // RequestURI
                $Response->setRequestURI($Request->getURI());

                // Update
                $this[$Request] = $Response;
            }

            // Update finished flag
            $Response['Running'] = false;
            $Response['Finished'] = true;

            // Update info
            $this->_update($Request);

            // Free Handle
            $this->_Helper->freeHandle($Handle);

            // Does mediator exist?
            if ($this->_Mediator instanceof IMediator) {

                // Request.Finished event
                $this->_do('Request.Finished', new Event($this, array(
                    'Request' => &$Request,
                    'Response' => &$Response
                )));
            }
        }
    }

    /**
     * Updates a running request.
     *
     * @ignore
     * @codeCoverageIgnore
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     */
    private function _update(IRequest $Request)
    {
        if (($Response = $this[$Request]) instanceof IResponse) {

            // Update info
            foreach (curl_getinfo($Response['handle']) as $k => $v)
                if ($k != 'request_header') {
                    $Response[$k] = $v;
                }

                // Update $_URI
            if (! empty($Response['url'])) {

                // Create URI
                $URI = ! ! $Request->URI ? new GenericURI($Response['url'], $Request->URI) : new GenericURI($Response['url']);

                // Is it valid? update
                if ($URI->isValid())
                    $Response->URI = $URI;

                    // Does mediator exist?
                if ($this->_Mediator instanceof IMediator) {

                    // Request.Finished event
                    $this->_do('Request.Update', new Event($this, array(
                        'Request' => &$Request,
                        'Response' => &$Response
                    )));
                }
            }
        }
    }

    /**
     * Finds a request by its assosciated cURL handle.
     *
     * @ignore @codeCoverageIgnore
     *
     * @param resource $Handle
     * @return \BLW\Type\HTTP\IRequest Found request or a new request in case of error.
     */
    private function _findByHandle($Handle)
    {
        // Loop through each request
        foreach ($this as $Request)
            if (($Response = $this[$Request]) instanceof IResponse) {

                // Does Response handle match?
            if (isset($Response['handle']) ? $Response['handle'] == $Handle : false)
                    return $Request;
            }

            // Error new request
        return new Request();
    }
}

// @codeCoverageIgnoreStart
return true;
// @codeCoverageIgnoreEnd
