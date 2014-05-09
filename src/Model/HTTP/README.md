HTTP Module
===========

This module is used to handle all HTTP functionality. It is based on the [BLW\HTTP][BLW\HTTP Framework] framework.

##### Requirements #####

- PHP (ext-curl, ext-DOM)
- BLW\Core
- BLW\MIME
- BLW\DOM

##### Components #####

1. [Request][]: For HTTP request messages.
2. [Response][]: For HTTP response messages.
3. [Client][]: For transporting HTTP request and response messages.
4. [Browser][]: For emulating various browsers.

##### See Also #####

- [CHANGELOG][]
- [TODO][]


Request
-------

This is a [RFC][RFC 2616] implementation of a HTTP/1.0 or HTTP/1.1 request. It is based on [BLW\MIME][BLW\MIME Framework] frameworks' [IMessage][]

- [API Documentation][Request API]
- [IRequest Interface][IRequest]
- [More Info / Examples][Request]

##### Example: POST Multipart/Form #####

```php
<?php

use BLW\Model\GenericURI as URL;
use BLW\Model\GenericFile as File;
use BLW\Model\Config\Generic as Config;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\MIME;

$Request = new Request(IRequest::GET, new Config(array(
	'Timeout'		=> 10, 		// Abort request after 10 seconds
	'MaxRedirects'	=> 4, 		// Maximum redirects to follow
	'EnableCookies'	=> true,	// Use cookies
));

// Target
$Request->URI = new URL('http://example.com/post.php?secret=foo');

// User Agent
$Request->Headers['User-Agent'] = $Request->createHeader('UserAgent', 'The foo society');

// Accept Header
$Request->Headers['Accept'] = $Request->createHeader('Accept', '*/*;q=0.8');

/*
 * Note:
 * MIME\Section helps to create Content-Type and
 * MIME Boundary Headers
 */
 
$Section = new MIME\Section('multipart/form-data');

// MIME Part header placed in message header
$Request->Headers['Content-Type'] = $Section->createStart();

// Regular text value with encoding
$Request->Body[] = $Section->createBoundary();
$Request->Body[] = new MIME\Part\FormField('name', 'text/plain', 'Alexander', 'UTF-8');

$Request->Body[] = $Section->createBoundary();
$Request->Body[] = new MIME\Part\FormField('email', 'text/plain', 'me@domain.com', 'UTF-8');

// Arrays
$Request->Body[] = $Section->createBoundary();
$Request->Body[] = new MIME\Part\FormField('info[country]', 'text/plain', 12);

$Request->Body[] = $Section->createBoundary();
$Request->Body[] = new MIME\Part\FormField('info[state]', 'text/plain', 27);

// Files
$Request->Body[] = $Section->createBoundary();
$Request->Body[] = new MIME\Part\FormFile('cv', new File('path/to/file.doc'));

// End MIME Part
$Request->Body[] = $Section->createEnd();

echo strval($Request);
?>
```

Response
--------

This is a [RFC][RFC 2616] implementation of a HTTP/1.0 or HTTP/1.1 response. It is based on [BLW\MIME][] frameworks' [IMessage][]

- [API Documentation][Response API]
- [IResponse Interface][IResponse]
- [More Info / Examples][Response]

##### Example: RAW Response #####

```php
<?php

use BLW\Model\HTTP\Response\Generic as Response;

// ...
// Some code to open a socket an make a http request
// ...

$RAW		= stream_get_contents($stream);
$Response	= Response::createFromString($RAW);

var_dump($Response->Version);		// string 'HTTP'
var_dump($Response->Protocol);		// string '1.1'
var_dump($Response->Status);		// int 200, 404, 503, etc
var_dump(strval($Response->URI));	// string 'http://domain.com/path/file?query#fragment'
var_dump(strval($Response->RequestURI));// string 'http://domain.com/redirect.php'

var_dump($Response->Headers['Content-Type']->Type);
var_dump($Response->Headers['Content-Type']->Value);

// string 'Content-Type'
// string 'text/html; charset=ISO-1344-1'

print strval($Response->Body);

//<doctype html>
//<html>
//<head>
//......

?>
```
	 
Client
------
Clients are responsible for Handling / Transporting HTTP Requests to and from the server. They are also responsible for handling HTTP headers such as cache control and cookie management.

- [API Documentation][Client API]
- [IClient Interface][IClient]
- [More Info / Examples][Client]

##### Example: Parrallel requests #####

```php
<?php

use BLW\Model\HTTP\Client\cURL as Client

// Generate Requests
$Requests = generate_1000_requests();

// Configure client
$Client   = new Client(
	IClient::COOKIE_DEFAULT,			// Default cookie handling
	new File(sys_get_temp_dir())		// Storage directory for cache / cookies (must be writable)
	'socks4://username:password@proxyserver'	// Proxy config
	4,						// Maximum number of connections to make to 1 server @ a time
	32,						// Maximum number of requests to execute in parrallel
	60						// Maximum number of requests to make per minute
);

// Que requests for processing
$Client->send(array_shift($Requests));
$Client->send(array_shift($Requests));
$Client->send(array_shift($Requests));
$Client->send(array_shift($Requests));

$Client->sendAll($Requests);

// Run for 10 minutes
$Client->run(10*60);

// Get request status
$Request = $Requests[0];

echo "Request 1 is " . ($Client[$Request]['Finished']? 'Finished' : 'Not Finished');
echo "Request 1 is " . ($Client[$Request]['Running']? 'Running' : 'Not Running');
echo "Request 1 is " . ($Client->contains($Request)? 'Present' : 'Not Present');

// Stats
echo "Finished requests: " . $Client->countFinished();
echo "Running requests: " . $Client->countRunning();
echo "Scheduled requests: " . $Client->countScheduled();

// Run till completion
$Client->run();

// Retrieve Responses
$Responses = array_map(function(){

	// Get response
	$Response = $Client[$Request];
	
	// Remove request from client
	$Client->detach($Request);
	
	return $Response;
	
},$Requests);
?>
```

Browser
-------
Browser emulates a real browser by making requests through an interchangable HTTP client. It is easily extendible by either creating engines or plugins.

- [API Documentation][Browser API]
- [IBrowser Interface][IBrowser]
- [More Info / Examples][Browser]

##### Example: JavaScript Emulation with PhantomJS #####

```php
<?php

use BLW\Model\GenericFile as File;

use BLW\Model\Config\Generic as Config;

use BLW\Model\HTTP\Client\cURL
use BLW\Model\HTTP\Browser\Nexus;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$Config  = new Config('MaxHistory' => 2);
$Logger  = new Logger('browser', new StreamHandler(
	sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'browser.log',
	Logger::DEBUG
));

$Browser = new Nexus(new cURL, $Config, $Logger);

$Browser->Engines['PhantomJS'] = new PhantomJS(
	new File('phantomjs'),
	new File('path/to/phantomjs/config.json'),
	new Config(array(
		'Timeout'	=> 10,	
		'CWD'		=> NULL,
		'Enviroment'	=> NULL,
		'Extras'	=>	NULL,
	)
));

// Go to search page
$Browser->go('http://www.google.com/');

// Wait a bit, Google has a lot of ajax
sleep(4);

// Search
$Browser->evaluateCSS('form[action="/search"] input[name="q"]', 'this.value = "BLW Library"; this.form.submit();');

// Wait for next page to load
$Browser->wait();

// Print out results
echo $Browser->filter('title')->offsetGet(0)->textContent . "\r\n=================================\r\n";

foreach($Browser->filter('li.g h3 > a') as $link) {

	echo $link->textContent . "(" . $link->getAttribute('href') . ")\r\n";
}

?>
```

[CHANGELOG]: <CHANGELOG.md>
[TODO]: <TODO.md>
[RFC 2616]: <https://tools.ietf.org/html/rfc2616#page-31> "RFC 2616 - HTTP Message"

[BLW\MIME]: <../MIME/>
[BLW\MIME Framework]: <../../Type/MIME/>
[BLW\HTTP Framework]: <../../Type/HTTP/>

[IMessage]: <../../Type/MIME/IMessage.php>
[IRequest]: <../../Type/HTTP/IRequest.php>
[IResponse]: <../../Type/HTTP/IResponse.php>
[IClient]: <../../Type/HTTP/IClient.php>
[IBrowser]: <../../Type/HTTP/IBrowser.php>

[Request]: <./Request/>
[Request API]: <http://api.mast3rpeee.tk/BLW/namespaces/BLW.Model.HTTP.html>
[Response]: <./Response/>
[Response API]: <http://api.mast3rpeee.tk/BLW/namespaces/BLW.Model.HTTP.Response.html>
[Client]: <./Client/>
[Client API]: <http://api.mast3rpeee.tk/BLW/namespaces/BLW.Model.HTTP.Client.html>
[Browser]: <./Browser/>
[Browser API]: <http://api.mast3rpeee.tk/BLW/namespaces/BLW.Model.HTTP.Browser.html>
['Running']: <javascript:;>
['Finished']: <javascript:;>