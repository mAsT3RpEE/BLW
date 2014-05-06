<?php
/**
 * PhantomJS.php | Apr 19, 2014
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
namespace BLW\Model\HTTP\Browser\Engine;

use DOMNode;
use RuntimeException;

use BLW\Type\IConfig;
use BLW\Type\IFile;
use BLW\Type\IEvent;
use BLW\Type\IMediator;
use BLW\Type\MIME\IHeader;
use BLW\Type\HTTP\IRequest;
use BLW\Type\HTTP\IBrowser;

use BLW\Model\InvalidArgumentException;
use BLW\Model\Stream\String as StringStream;
use BLW\Model\Config\Generic as GenericConfig;
use BLW\Model\Mediator\Symfony as Mediator;
use BLW\Model\HTTP\Event;
use BLW\Model\HTTP\Response\Generic as Response;
use BLW\Model\Command\Exception as CommandException;
use BLW\Model\Command\Shell as ShellCommand;
use BLW\Model\Command\Input\Generic as CommandInput;
use BLW\Model\Command\Output\Generic as CommandOutput;
use BLW\Model\Command\Option\Generic as Option;
use BLW\Model\Command\Argument\Generic as Argument;
use BLW\Model\GenericFile;
use BLW\Model\GenericURI;
use BLW\Model\MIME\ContentType;
use BLW\Model\HTTP\RequestFactory;


if (! defined('BLW')) {

    if (strstr($_SERVER['PHP_SELF'], basename(__FILE__))) {
        header("$_SERVER[SERVER_PROTOCOL] 404 Not Found");
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;

        echo "<html>\r\n<head><title>404 Not Found</title></head><body bgcolor=\"white\">\r\n<center><h1>404 Not Found</h1></center>\r\n<hr><center>nginx/1.5.9</center>\r\n</body>\r\n</html>\r\n";
        exit();
    }

    return false;
}

/**
 * PhantomJS HTTP / JS / COOKIE / CACHE engine.
 *
 * <h3>Introduction</h3>
 *
 * <p>Uses phantomjs <a href="http://phantomjs.org/">phantomjs.org</a>
 * to handle page requests and execute javascript</p>
 *
 * <h3>Dynamic Methods</h3>
 *
 * <ul>
 * <li><b>wait()</b>:
 * Executes PhantomJS Browser until new page loads.</li>
 * <li><b>evaluateNode(DOM\IElement $Element,
 * string $JavaScript)</b>:
 * Execute javascript on a particular DOM element.</li>
 * <li><b>evaluateXPath(string $XPath,
 * string $JavaScript)</b>:
 * Execute javascript on XPath query elements</li>
 * <li><b>evaluateCSS(string $Selector,
 * string $JavaScript)</b>:
 * Execute javascript on CSS3 selector elements</li>
 * </ul>
 *
 * <h3>Events</h3>
 *
 * <ul>
 * <li><b>Browser.Page.Download(IRequest $Request)</b>:
 * Engine uses this event to handle page downloads and
 * stops browser handling by executing
 * <code>IEvent::stopPropagation()</code>.</li>
 * <li><b>Browser.Phantom.Command(string $Command, mixed &$Result)</b>:
 * Executes phantom script and returns result.</li>
 * </ul>
 *
 * <hr>
 *
 * @package BLW\HTTP
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class PhantomJS extends \BLW\Type\HTTP\Browser\AEngine
{

    /**
     * Bootstrap file for PhantomJS Commands.
     *
     * @var string BOOTSTRAP
     */
    const BOOTSTRAP = <<<EOT
(function(BLW, WebPage, System, undefined)
{
	Object.defineProperty(BLW, 'lastPage', {
		value : WebPage.create(),
		writable : true,
		enumerable : true,
		configurable : true
	});

	Object.defineProperty(BLW, 'Running', {
		value : false,
		writable : true,
		enumerable : true,
		configurable : true
	});

	Object.defineProperty(BLW, 'Printing', {
		value : false,
		writable : true,
		enumerable : true,
		configurable : true
	});

	var page = BLW.lastPage;

	page.startTime = new Date();
	page.resources = [];
	page.info = {
		type : 'get',
		address : page.url,
		headers : {},
		data : undefined,
		tries : 1
	};

	page.onLoadStarted = function()
	{
		page.startTime = new Date();
		page.resources = [];
	};

	page.onResourceRequested = function(request, networkRequest)
	{
		/* console.log('SENDING REQUEST (' + request.url + ')'); */

		if ((request.id <= 1 || request.url == page.info.address)) {

			for (header in page.info.headers)
				if (page.info.headers.hasOwnProperty(header)) {

					request.headers[header] = page.info.headers[header];

					if (networkRequest.setHeader != undefined) {
						networkRequest.setHeader(header, page.info.headers[header]);
					}
				}

		}

		page.resources[request.id] = {
			request : request,
			startReply : null,
			endReply : null,
		};
	};

	page.onResourceReceived = function(response)
	{
		/* console.log('RECIEVING RESPONSE (' + response.url + ')'); */

		if (!page.resources[response.id]) {
			page.resources[response.id] = {
				request : {
					id : response.id,
					url : response.url,
					method : 'unknown',
					time : page.startTime,
					headers : {}
				},
				startReply : null,
				endReply : null,
			};
		}

		if (response.stage === 'start') {
			page.resources[response.id].startReply = response;
		}

		if (response.stage === 'end') {
			page.resources[response.id].endReply = response;
		}
	};

	page.onResourceError = function(error)
	{
		/* console.log('ERROR RECEIVING RESOURCE (' + error.url + ')'); */
	};

	page.onResourceTimeout = function(request)
	{
		/* console.log('TIMEOUT RECEIVING RESOURCE (' + request.url + ')'); */
	};

	page.onLoadFinished = function(status)
	{
		if (BLW.Printing) return;

		window.setTimeout(function()
		{
			if (!BLW.Running) return;

			else if (status !== 'success' && false) {

				BLW.error('ERROR LOADING ADDRESS (' + page.info.address + ')');

			}

			page.endTime = new Date();
			page.title = page.evaluate(function()
			{
				return document.title;
			});

			var output = JSON.stringify(BLW.createHAR(page), undefined, 4);
			var line;

			/* Output to stdoud 1kb @ a time */
			BLW.Running = false;
			BLW.Printing = true;
			output = output.match(/[\\s\\S]{1,1024}/g);

			var interval = window.setInterval(function()
			{
				if (line = output.shift()) {
					System.stdout.write(line);
					System.stdout.flush();
				}

				else {
					window.clearInterval(interval);
					BLW.stop();
				}

			}, 100);

		}, 4000);
	};

	BLW.stop = function()
	{
		BLW.Running = false;
		BLW.Printing = false;

		System.stdout.write('\\x04');
		System.stdout.flush();
	};

	BLW.error = function(message)
	{
		console.log(JSON.stringify({
			status : "error",
			message : message
		}, undefined, 4));

		BLW.stop();
	};

	BLW.createHAR = function(page)
	{
		var entries = [];

		page.resources.forEach(function(resource)
		{
			var request = resource.request;
			var startReply = resource.startReply;
			var endReply = resource.endReply;

			if (!request || !startReply || !endReply) { return; }

			/*
			 * Exclude Data URI from HAR file because they aren't included in
			 * specification
			 */
			if (request.url.match(/^data\\x3aimage\\x5c.*/i)) { return; }

			entries.push({
				pageref : page.address,
				startedDateTime : request.time.toISOString(),
				time : endReply.time - request.time,
				request : {
					method : request.method,
					url : request.url,
					httpVersion : "HTTP/1.1",
					cookies : [],
					headers : request.headers,
					queryString : [],
					headersSize : -1,
					bodySize : -1
				},
				response : {
					status : endReply.status,
					statusText : endReply.statusText,
					httpVersion : "HTTP/1.1",
					cookies : [],
					headers : endReply.headers,
					redirectURL : "",
					headersSize : -1,
					bodySize : startReply.bodySize,
					content : {
						size : startReply.bodySize,
						mimeType : endReply.contentType
					}
				},
				cache : {},
				timings : {
					blocked : 0,
					dns : -1,
					connect : -1,
					send : 0,
					wait : startReply.time - request.time,
					receive : endReply.time - startReply.time,
					ssl : -1
				}
			});
		});

		return {
			status : "ok",
			log : {
				version : '1.2',
				creator : {
					name : "BLW",
					version : "1.0.0",
					comment : ""
				},
				browser : {
					name : "PhantomJS",
					version : phantom.version.major + '.' + phantom.version.minor + '.' + phantom.version.patch,
					comment : ""
				},
				pages : [ {
					startedDateTime : page.startTime.toISOString(),
					id : page.address ? page.address : 'about:blank',
					title : page.title,
					pageTimings : {
						ononContentLoad : -1,
						onLoad : page.endTime - page.startTime,
						comment : ""
					},
					comment : "",
					url : page.url,
					html : page.content
				} ],
				entries : entries
			}
		};
	};

	BLW.send = function(type, address, headers, timeout, data)
	{
		var page = BLW.lastPage;
		var globalHeaders = [ "User-Agent", "Accept", "Accept-Charset", "Accept-Encoding", "Accept-Language" ];
		var staticHeaders = {};

		for (header in headers)
			if (headers.hasOwnProperty(header)) {

				if (globalHeaders.indexOf(header) != -1) {

					Object.defineProperty(staticHeaders, header, {
						value : headers[header],
						writable : true,
						enumerable : true,
						configurable : true
					});

					delete headers[header];
				}
			}

		page.customHeaders = staticHeaders;
		page.settings.resourceTimeout = timeout >= 1 ? timeout * 1000 : 3600000;
		page.info = {
			type : type,
			address : address,
			headers : headers,
			data : !!data ? data : undefined,
			tries : 1
		};

		if (!!page.data) {
			page.open(page.info.address, page.info.type, page.info.data);
		}

		else {
			page.open(page.info.address, page.info.type);
		}
	};

	BLW.wait = function()
	{
		var page = BLW.lastPage;

		page.startTime = new Date();
		page.info = {
			type : 'unknown',
			address : page.url,
			headers : {},
			data : undefined,
			tries : 99
		};
	};

	BLW.evaluate = function(Script)
	{
		var page = BLW.lastPage;

		try {
			var output = JSON.stringify({
				status : "ok",
				result : eval(Script)
			}, undefined, 4);

			var line;

			/* Output to stdoud 1kb @ a time */
			BLW.Running = false;
			BLW.Printing = true;
			output = output.match(/[\\s\\S]{1,1024}/g);

			var interval = window.setInterval(function()
			{
				if (line = output.shift()) {
					System.stdout.write(line);
					System.stdout.flush();
				}

				else {
					window.clearInterval(interval);
					BLW.stop();
				}

			}, 100);
		}

		catch (e) {
			BLW.error('INVALID PHANTOM SCRIPT (' + e + ')');
		}
	};

	BLW.evaluateXPath = function(XPath, JavaScript)
	{
		var page = BLW.lastPage;
		var result = page.evaluate(function(XPath, JavaScript)
		{
			var callback = new Function(JavaScript);

			try {
				var list = document.evaluate(XPath, document, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
			}

			catch (e) {
				return e;
			}

			for (var count = 0, node; node = list.iterateNext(); count++) {

				try {
					callback.call(node);
				}

				catch (e) {
					return e;
				}
			}

			return count;

		}, XPath, JavaScript);

		if (typeof result == 'number') {
			console.log(JSON.stringify({
				status : 'ok',
				result : result
			}, undefined, 4));

			BLW.stop();
		}

		else {
			console.log(JSON.stringify({
				status : 'error',
				result : result
			}, undefined, 4));

			BLW.stop();
		}
	};

	BLW.evaluateSelector = function(Selector, JavaScript)
	{
		var page = BLW.lastPage;
		var result = page.evaluate(function(Selector, JavaScript)
		{
			var callback = new Function(JavaScript);

			try {
				var list = document.querySelectorAll(Selector);
			}

			catch (e) {
				return e;
			}

			for (var current = 0, node; current < list.length; current++) {

				try {
					node = list[current];

					callback.call(node);
				}

				catch (e) {
					return e;
				}

			}

			return current;

		}, Selector, JavaScript);

		if (typeof result == 'number') {
			console.log(JSON.stringify({
				status : 'ok',
				result : result
			}, undefined, 4));

			BLW.stop();
		}

		else {
			console.log(JSON.stringify({
				status : 'error',
				result : result
			}, undefined, 4));

			BLW.stop();
		}
	};

	BLW.dispatch = function()
	{
		/* If running continue */
		if (BLW.Running || BLW.Printing) return true;

		var line = System.stdin.readLine();
		var Command;

		/* If no more input exit */
		if (!line) return false;

		/* Get command */
		try {
			Command = JSON.parse(line);
		}

		/* Error? */
		catch (e) {
			BLW.error('INVALID JSON (' + e + ')');
			return true;
		}

		/* Set running to true */
		BLW.Running = true;

		/* Interprate command */
		switch (Command.action)
		{
			case "send":
				BLW.send(Command.type, Command.address, Command.headers, Command.timeout, Command.data);
				break;
			case "wait":
				BLW.wait();
				break;
			case "evaluate":
				BLW.evaluate(Command.script);
				break;
			case "evaluateXPath":
				BLW.evaluateXPath(Command.xpath, Command.script);
				break;
			case "evaluateSelector":
				BLW.evaluateSelector(Command.selector, Command.script);
				break;
			case "error":
				BLW.error(Command.message);
				break;
			case "exit":
				return false;
			case undefined:
				BLW.error('COMMAND NOT SET');
				break;

			default:
				BLW.error('UNKNOWN COMMAND');
		}

		/* Done */
		return true;
	};

	/* Execute commands till error / exit */
	var interval = window.setInterval(function()
	{
		if (!BLW.dispatch()) {

			/* Stop interval */
			window.clearInterval(interval);

			/* Exit */
			phantom.exit();
		}

	}, 200);

})(phantom, require('webpage'), require('system'));
EOT;

    /**
     * PhantomJS process
     *
     * @see \BLW\Model\HTTP\Browser\Engine\PhantomJS::phantomStart() PhantomJS::phantomStart()
     * @see \BLW\Model\HTTP\Browser\Engine\PhantomJS::phantomRestart() PhantomJS::phantomRestart()
     * @see \BLW\Model\HTTP\Browser\Engine\PhantomJS::phantomStop() PhantomJS::phantomStop()
     * @see \BLW\Model\HTTP\Browser\Engine\PhantomJS::phantom() PhantomJS::phantom()
     * @var \BLW\Model\Command\Shell $_Process [private]
     */
    private $_Process = null;

    /**
     * Path to PhantomJS executable file.
     *
     * @var \BLT\Type\IFile $_Executable
     */
    protected $_Executable = 'phantomjs';

    /**
     * Path to PhantomJS configuration .json file.
     *
     * @var \BLT\Type\IFile $_ConfigFile
     */
    protected $_ConfigFile = null;

    /**
     * Last result from an evaluate dynamic call.
     *
     * @var string $_lastResult
     */
    protected $_lastResult = null;

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
            "$ID.Page.Download" => array(
                'doPageDownload',
                -10
            ),
            "$ID.Phantom.Command" => array(
                'doPhantomCommand',
                -10
            ),
            "$ID.wait" => array(
                'doWait',
                -10
            ),
            "$ID.evaluateNode" => array(
                'doEvaluateNode',
                -10
            ),
            "$ID.evaluateXPath" => array(
                'doEvaluateXpath',
                -10
            ),
            "$ID.evaluateCSS" => array(
                'doEvaluateCSS',
                -10
            )
        );
    }

    /**
     * Constructor
     *
     * @see \BLW\Model\Command\Shell Command\Shell()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Configuration</code> is not readable.
     * @throws \RuntimeException If there is an error starting PhantomJS.
     *
     * @param \BLW\Type\IFile $Executable
     *            Path to PhantomJS executable.
     * @param \BLW\Type\IFile $ConfigFile
     *            Path to PhantomJS configuration JSON file.
     * @param \BLW\Type\IConfig $Config
     *            Engine configuration:
	 *
     * <ul>
     * <li><b>Timeout</b>: <i>int</i> Communication timeout between PHP and PhantomJS process.</li>
     * <li><b>CWD</b>: <i>string</i> Current working directory for PhantomJS. <code>null</code> for current working directory.</li>
     * <li><b>Enviroment</b>: <i>array</i> Enviroment variables passed to PhantomJS. <code>null</code> for PHP enviroment variables.</li>
     * <li><b>Extras</b>: <i>array</i> Extra parameters passed to proc_open()</li>
     * </ul>
     */
    public function __construct(IFile $Executable, IFile $ConfigFile, IConfig $Config)
    {
        // Parent constructor
        parent::__construct(null, 'PhantomJS');

        // Validate ConfigFile
        if (! $ConfigFile->isReadable())
            throw new InvalidArgumentException(1);

        // Validate $Config
        switch (true) {
            case ! isset($Config['Timeout']):
            case ! $Config->offsetExists('CWD'):
            case ! $Config->offsetExists('Environment'):
            case ! $Config->offsetExists('Extras'):

                throw new InvalidArgumentException(2);
        }

        // Properties
        $this->_Executable  = $Executable;
        $this->_ConfigFile  = $ConfigFile;
        $this->_Config      = $Config;

        // Start PhantomJS process
        if (! $this->phantomStart())
            throw new RuntimeException('Unable to start PhantomJS');
    }

    /**
     * Gets the last result of an evaluate dynamic call.
     *
     * @return mixed Result from last evaluate*** call.
     */
    public function getLastResult()
    {
        return $this->_lastResult;
    }

    /**
     * Starts PhantomJS process.
     *
     * @return bool <code>TRUE</code> on success. <code>FALSE</code> on failure.
     */
    public function phantomStart()
    {
        // Create Command
        $this->_Process = new ShellCommand($this->_Executable, $this->_Config);

        // Start Command
        return $this->phantomRestart();
    }

    /**
     * Restarts PhantomJS process.
     *
     * @return bool <code>TRUE</code> on success. <code>FALSE</code> on failure.
     */
    public function phantomRestart()
    {
        // Close command
        $this->_Process->close();

        // Wait a bit
        usleep(100000);

        // Create bootstrap
        $Bootstrap = new GenericFile(sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5('PhantomJS') . '.js');

        $Bootstrap->putContents(self::BOOTSTRAP);

        // Open Command
        $stdIn              = '';
        $Input              = new CommandInput(new StringStream($stdIn));
        $Input->Options[]   = new Option('config', $this->_ConfigFile);
        $Input->Arguments[] = new Argument($Bootstrap);

        // If okay? return true.
        if ($this->_Process->open($Input))
            return true;

        // Error
        return false;
    }

    /**
     * Terminates PhantomJS process.
     *
     * @return void
     */
    public function phantomStop()
    {
        $Bootstrap = new GenericFile(sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5('PhantomJS') . '.js');

        // Close command
        $this->_sendJSON(array(
            'action' => 'exit'
        ));

        $this->_Process->close();

        // Delete bootsrap
        usleep(250000);

        @unlink($Bootstrap);
    }

    /**
     * Sends a JSON command to PhantomJS
     *
     * @codeCoverageIngore
     *
     * @param array $Command
     *            Command to send
     * @param real $Timeout
     *            Timeout for reads / writes to process.
     * @return string Output of command. <code>FALSE</code> on error.
     */
    private function _sendJSON(array $Command, $Timeout = 1)
    {
        static $onOutput, $Mediator;

        // ##################################################################
        // NOTE:
        // ##################################################################
        // PHP is very anoying as it will block on reads from stdout / stderr
        // pipe. This cannot be stopped with either stream_set_blocking() or
        // stream_set_timeout(). Therefore I place an end of output marker
        // (Ctrl + D) and disable stderr in order to make reads without
        // hanging.
        //
        // Times like these I love nodejs
        // ##################################################################

        // Check for Ctrl+D / Disable stdErr
        $onOutput = $onOutput ?: function (IEvent $Event)
        {
            // Prevent reading from stderr (hangs)
            unset($Event->Pipes[1]);

            // Prevent reading after last output
            if (strpos(substr($Event->Data, -3), "\x04") !== false)
                unset($Event->Pipes[0]);
        };

        // Mediator
        $Mediator = $Mediator ?: new Mediator;

        // `evaluate` command
        $Command  = json_encode($Command);

        // Add end of transmition marker
        $Command .= "\r\n";

        // Run Processes
        $Input    = new CommandInput(new StringStream($Command));
        $Output   = new CommandOutput(
            new StringStream($stdOut, 'text/plain', IFile::WRITE | IFile::TRUNCATE),
            new StringStream($stdErr, 'text/plain', IFile::WRITE | IFile::TRUNCATE)
        );

        // CommandOutput.Output hook
        $Output->setMediator($Mediator);
        $Output->setMediatorID('PhantomJS');
        $Output->_on('Output', $onOutput);

        try {
            // Transfer output
            $this->_Process->transferStreams($Input, $Output, $Timeout);
        }

        // @codeCoverageIgnoreStart
        catch (CommandException $e) {

            // Restart PhantomJS just to be sure
            $this->phantomRestart();

            return false;
        }
        // @codeCoverageIgnoreEnd

        // CommandOutput.Output hook
        $Mediator->deregister('Output', $onOutput);

        unset($Input, $Output);

        // Normalize output
        $stdOut = substr($stdOut, strpos($stdOut, '{'), strrpos($stdOut, '}') - strlen($stdOut) + 1);

        // Done
        return $stdOut;
    }

    /**
     * Executes a command to PhantomJS process.
     *
     * <h4>Note</h4>
     *
     * <p>Maximum size of script to evaluate has been capped to 16KB. Any
     * length above that will be truncated resulting in an error.</p>
     *
     * <hr>
     *
     * @link http://www.php.net/manual/en/function.json-encode.php json_encode()
     * @link http://www.php.net/manual/en/function.json-decode.php json_decode()
     *
     * @throws \BLW\Model\InvalidArgumentException If <code>$Command</code> is not a string.
     *
     * @param string $Command
     *            Script to send to PhantomJS.
     * @param int $Timeout
     *            Time in seconds to wait for execution of command.
     * @return \stdClass JSON decoded response. <code>FALSE</code> in case of error.
     */
    public function phantom($Command, $Timeout = 0.1)
    {
        // Is $Command a string?
        if (is_string($Command) ?: is_callable(array(
            $Command,
            '__toString'
        ))) {

            // `evaluate` command
            $Output = $this->_sendJSON(array(
                'action' => 'evaluate',
                'script' => @substr($Command, 0, 16 * 1024)
            ), $Timeout);

            // Done
            return $Output
                ? json_decode($Output)
                : false;
        }

        // Invalid $Command
        else
            throw new InvalidArgumentException(0);

        // Error
        return false;
    }

    /**
     * Converts a request for transport.
     *
     * @see \BLW\Type\HTTP\AClient::translate() AClient::translate()
     * @link https://github.com/ariya/phantomjs/blob/master/src/webpage.cpp PhantomJS
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to translate.
     * @return mixed Translated HTTP request.
     */
    public function translate(IRequest $Request)
    {
        static $Headers;

        // Headers
        $Headers = $Headers ?  : function (IRequest $Request)
        {
            $Converted = array();

            // Loop through each header
            foreach ($Request->Header as $Header)
                if ($Header instanceof IHeader)
                    // Convert to associative array
                    $Converted[$Header->getType()] = $Header->getValue();

            // JSON Format
            return $Converted;
        };

        // Request type
        switch ($Request->Type) {
            // Valid requests
            case IRequest::GET:
            case IRequest::POST:
            case IRequest::HEAD:
            case IRequest::PUT:
            case IRequest::DELETE:

                $Type = $Request->Type;
                break;

            // Invalid requests
            default:

                // Exception
                throw new InvalidArgumentException(0, 'IRequest::$Type must be either(GET, POST, HEAD, PUT or DELETE)');

                // Blank page
                return array(
                    'action' => 'send',
                    'type' => 'get',
                    'address' => 'about:blank',
                    'headers' => array(),
                    'data' => null
                );
        }

        // `send` command
        return array(
            'action'  => 'send',
            'type'    => $Type,
            'address' => strval($Request->URI),
            'timeout' => $Request->Config['Timeout'],
            'headers' => $Headers($Request),
            'data'    => trim($Request->Body) ?: null
        );
    }

    /**
     * Parse HAR results for information about last request.
     *
     * @param \stdClass $Result
     *            Result from either send() or wait() method.
     * @return \BLW\Type\HTTP\IResponse Parsed response. <code>FALSE</code> on error.
     */
    public function parseResults($Result)
    {
        // Invalid result
        if (! $Result instanceof \stdClass)
            return false;

        // Result not ok? return false
        if (strtolower($Result->status) != 'ok')
            return false;

        // Current URI of page
        $URI        = $Result->log->pages[0]->url;
        $ResuestURI = new GenericURI($Result->log->pages[0]->id);

        // Search for response in entries
        while (list ($k, $HAR) = each($Result->log->entries))
            if ($HAR->request->url == $URI) {

                // Redirect?

                // @codeCoverageIgnoreStart
                if ($HAR->response->redirectURL) {

                    // Update $URL
                    $URI = $HAR->response->redirectURL;

                    // Restart
                    reset($Result->log->entries);
                    continue;
                }
                // @codeCoverageIgnoreEnd

                // Return found result
                $Parts = explode('/', $HAR->response->httpVersion, 2);

                // Create response
                $Response = new Response($Parts[0], $Parts[1], $HAR->response->status);

                // Loop through headers
                foreach ($HAR->response->headers as $Header) {

                    // 1st time seing header?
                    if (! isset($Response->Header[$Header->name]))
                        // Add header with key
                        $Response->Header[$Header->name] = $Response->createHeader($Header->name, $Header->value);

                    // Not first time?
                    else
                        // Add header without key
                        $Response->Header[] = $Response->createHeader($Header->name, $Header->value);
                }

                // Content-Type
                $Response->Header['Content-Type'] = isset($Response->Header['Content-Type']) ? $Response->Header['Content-Type'] : new ContentType($HAR->response->content->mimeType);

                // URI
                $Response->URI        = new GenericURI($Result->log->pages[0]->url, $ResuestURI);
                $Response->RequestURI = $ResuestURI;

                // Content
                $Response->Body['Content'] = $Result->log->pages[0]->html;

                // Done
                return $Response;
            }

        // Error
        return false;
    }

    /**
     * Sends a request to PhantomJS for handling.
     *
     * @param \BLW\Type\HTTP\IRequest $Request
     *            Request to send.
     * @return \BLW\Type\HTTP\IResponse Response of request.
     */
    public function send(IRequest $Request)
    {
        // Send request
        $Result = $this->_sendJSON($this->translate($Request), $Request->Config['Timeout']);
        $Result = $Result
            ? json_decode($Result)
            : false;

        // Check results
        if ($Response = $this->parseResults($Result))
            // Done
            return $Response;

        // Does page exist?
        elseif (isset($Result->log->pages)) {

            // Build response
            $Response                  = new Response('HTTP', '1.1', 200);
            $Response->URI             = new GenericURI($Result->log->pages[0]->url, $Request->URI);
            $Response->Body['Content'] = $Result->log->pages[0]->html;

            // Done
            return $Response;
        }

        // Error
        return new Response('HTTP', '1.1', 0);
    }

    /**
     * Waits for new PhantomJS page to load.
     *
     * <h4>Note</h4>
     *
     * <p>This can cause PHP to hang. Use with care.</p>
     *
     * <hr>
     *
     * @return \BLW\Type\HTTP\IResponse Response of request.
     */
    public function wait()
    {
        // Send request
        $Result = $this->_sendJSON(array(
            'action' => 'wait'
        ));

        $Result = $Result ? json_decode($Result) : false;

        // Check results
        if ($Response = $this->parseResults($Result))
            // Done
            return $Response;

        // Error
        return new Response('HTTP', '1.1', 0);
    }

    /**
     * Handles Browser.Phantom.Command Event
     *
     * @param \BLW\Type\IEvent $Event
     *            Event object.
     */
    public function doPhantomCommand(IEvent $Event)
    {
        // Validate $Command
        if (! isset($Event->Command) ?  : (! is_string($Event->Command) && ! is_callable(array(
            $Event->Command,
            '__toString'
        )))) {

            // Missing request
            $Event->Subject->exception('IBrowser::Event(Phantom.Command) Invalid or missing IEvent::$Command');
            return null;
        }

        // Execute command
        $Event->Result = isset($Event->Timeout) ? $this->phantom($Event->Command, $Event->Timeout) : $this->phantom($Event->Command);

        // Done
        return null;
    }

    /**
     * Handles Browser.Phantom.Wait event.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event object
     */
    public function doWait(IEvent $Event)
    {
        // Wait for response
        $Browser  = $Event->Subject->Parent;
        $Response = $this->wait();

        // Is response known
        if ($Response->isValidCode($Response->Status)) {

            $Browser->debug(sprintf('New page loaded with code (%d).', $Response->Status));

            // Create page
            $Request = $Browser->RequestFactory->createGET($Response->URI, $Response->URI, $Browser->createHeaders());
            $Page = $Browser->createPage($Request, $Response);
        }

        // Unkown response

        // @codeCoverateIgnoreStart
        else {

            $Browser->warning(sprintf('Invalid response code (%s) while waiting for page.', $Response->Status));

            // Create page
            $Page = $Browser->createUnkownPage();
        }
        // @codeCoverateIgnoreEnd

        // Update page
        $Browser->setPage($Page);

        // Update History
        $Browser->addHistory($Page);

        // Browser.Page.Load Event
        $Browser->_do('Page.Ready', new Event($Browser));
    }

    /**
     * Handles Browser.Page.Download Event
     *
     * <h4>Note</h4>
     *
     * <p>This functions disables the browsers own handling
     * of this event by stopping propagation of event passed.</p>
     *
     * <hr>
     *
     * @param \BLW\Type\IEvent $Event
     *            Event object
     */
    public function doPageDownload(IEvent $Event)
    {
        // Validate $Request
        if (! isset($Event->Request) ?  : ! $Event->Request instanceof IRequest) {

            // Missing request
            $Event->Subject->exception('IBrowser::Event(Page.Download) Invalid or missing IEvent::$Request');
            return null;
        }

        // Navigate to requested page
        $Browser  = $Event->Subject;
        $Request  = $Event->Request;
        $Response = $this->send($Request, $this->_Config['Timeout']);

        // Is response known
        if ($Response->isValidCode($Response->Status)) {

            $Browser->debug(sprintf('Response for (%s) answered with code (%d).', $Request->URI, $Response->Status));

            // Create page
            $Page = $Browser->createPage($Request, $Response);
        }

        // Unkown response

        // @codeCoverateIgnoreStart
        else {

            $Browser->warning(sprintf('Invalid response code (%s) for url (%s).', $Response->Status, $Request->URI));

            // Create page
            $Page = $Browser->createUnkownPage();
        }
        // @codeCoverateIgnoreEnd

        // Update page
        $Browser->setPage($Page);

        // Update History
        $Browser->addHistory($Page);

        // Browser.Page.Load Event
        $Browser->_do('Page.Ready', new Event($Browser));

        // Stop native handling of Browser.Page.Download event
        $Event->stopPropagation();
    }

    /**
     * Evaluates Javascript on a particular node.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event object.
     */
    public function doEvaluateNode(IEvent $Event)
    {
        // Validate Arguments
        if (! isset($Event->Arguments) ?  : count($Event->Arguments) < 2) {

            // Generate exception
            $Event->Subject->exception('IBrowser::evaluateNode(DOMNode $Node, string $JavaScript) Missing argument');

            // Return
            return null;
        }

        // Arguments
        list ($Node, $JavaScript) = $Event->Arguments;

        // Is $Node not a node?
        if (! $Node instanceof DOMNode) {

            // Generate exception
            $Event->Subject->exception('IBrowser::evaluateNode(DOMNode $Node, string $JavaScript) Argument 1 should be an instance of DOMNode');

            // Return
            return null;
        }

        // Is $Javascript not a string?
        elseif (! is_string($JavaScript) && ! is_callable(array(
            $JavaScript,
            '__toString'
        ))) {

            // Generate exception
            $Event->Subject->exception('IBrowser::evaluateNode(DOMNode $Node, string $JavaScript) Argument 2 should be a string');

            // Return
            return null;
        }

        $JavaScript = strval($JavaScript);
        $Result     = $this->_sendJSON(array(
            'action' => 'evaluateXPath',
            'xpath'  => $Node->getNodePath(),
            'script' => $JavaScript
        )
        , 10);

        // Save result
        $Result = $this->_lastResult = $Result
            ? json_decode($Result)
            : false;

        // Check results
        if (! $Result ?: strtolower($Result->status) != 'ok') {

            // Exception
            $Event->Subject->exception(sprintf('IBrowser::evaluateNode(DOMNode $Node, string $JavaScript) Error(%s) in script (%s)', $Result->result->message, $JavaScript));
        }
    }

    /**
     * Evaluates Javascript on a particular XPath.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event object
     */
    public function doEvaluateXPath(IEvent $Event)
    {
        // Validate Arguments
        if (! isset($Event->Arguments) ?: count($Event->Arguments) < 2) {

            // Generate exception
            $Event->Subject->exception('IBrowser::doEvaluateXPath(string $XPath, string $JavaScript) Missing argument');

            // Return
            return null;
        }

        // Arguments
        list ($XPath, $JavaScript) = $Event->Arguments;

        // Is $XPath not a string?
        if (! is_string($XPath) && ! is_callable(array(
            $XPath,
            '__toString'
        ))) {

            // Generate exception
            $Event->Subject->exception('IBrowser::evaluateXPath(string $XPath, string $JavaScript) Argument 1 should be a string');

            // Return
            return null;
        }

        // Is $Javascript not a string?
        elseif (! is_string($JavaScript) && ! is_callable(array(
            $JavaScript,
            '__toString'
        ))) {

            // Generate exception
            $Event->Subject->exception('IBrowser::evaluateXPath(string $XPath, string $JavaScript) Argument 2 should be a string');

            // Return
            return null;
        }

        $JavaScript = strval($JavaScript);
        $Result     = $this->_sendJSON(array(
            'action' => 'evaluateXPath',
            'xpath'  => $XPath,
            'script' => $JavaScript
        )
        , 10);

        // Save result
        $Result = $this->_lastResult = $Result ? json_decode($Result) : false;

        // Check results
        if (! $Result ?: strtolower($Result->status) != 'ok') {

            // Exception
            $Event->Subject->exception(sprintf(
                'IBrowser::evaluateXPath(string $XPath, string $JavaScript) Error(%s) in script (%s) on XPath (%s)',
                $Result->result->message,
                $JavaScript, $XPath
            ));
        }
    }

    /**
     * Evaluates Javascript on a particular CSS Selector.
     *
     * @param \BLW\Type\IEvent $Event
     *            Event object
     */
    public function doEvaluateCSS(IEvent $Event)
    {
        // Validate Arguments
        if (! isset($Event->Arguments) ?: count($Event->Arguments) < 2) {

            // Generate exception
            $Event->Subject->exception('IBrowser::doEvaluateSelector(string $Selector, string $JavaScript) Missing argument');

            // Return
            return null;
        }

        // Arguments
        list ($Selector, $JavaScript) = $Event->Arguments;

        // Is $Selector not a string?
        if (! is_string($Selector) && ! is_callable(array(
            $Selector,
            '__toString'
        ))) {

            // Generate exception
            $Event->Subject->exception('IBrowser::evaluateSelector(string $Selector, string $JavaScript) Argument 1 should be a string');

            // Return
            return null;
        }

        // Is $Javascript not a string?
        elseif (! is_string($JavaScript) && ! is_callable(array(
            $JavaScript,
            '__toString'
        ))) {

            // Generate exception
            $Event->Subject->exception('IBrowser::doEvaluateSelector(string $Selector, string $JavaScript) Argument 2 should be a string');

            // Return
            return null;
        }

        $JavaScript = strval($JavaScript);
        $Result     = $this->_sendJSON(array(
            'action'   => 'evaluateSelector',
            'selector' => $Selector,
            'script'   => $JavaScript
        )
        , 10);

        // Save result
        $Result = $this->_lastResult = $Result
            ? json_decode($Result)
            : false;

        // Check results
        if (! $Result ?: strtolower($Result->status) != 'ok') {

            // Exception
            $Message = isset($Result->result->message) ? $Result->result->message : 'unknown';

            $Event->Subject->exception(sprintf(
                'IBrowser::doEvaluateSelector(string $Selector, string $JavaScript) Error(%s) in script (%s) on Selector (%s)',
                $Message,
                $JavaScript,
                $Selector
            ));
        }
    }
}

return true;
