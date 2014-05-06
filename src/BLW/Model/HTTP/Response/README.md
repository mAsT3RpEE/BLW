Response Component
-----------------

This contains BLW Libraries HTTP Response classes for making and handling HTTP Respones. All classes are built on the [BLW\HTTP][BLW\HTTP Framework] framework.

A response is either created from a string or manually using [IResponse][] API

##### Inspiration #####

- [Hypertext Transfer Protocol -- HTTP/1.1][RFC 2616]
- [HTTP Authentication: Basic and Digest Access Authentication][RFC 2617]
- [Returning Values from Forms:  multipart/form-data][RFC 2388]
- [HTTP State Management Mechanism][RFC 6265]
- [Guzzle][]
- [Artax][]

##### TODO #####

Make Request classes compatible with the following libraries

- Guzzle
- Symfony
- BUZZ
- Zend

##### Example: Manual 200 #####

	<?php
		
	use BLW\Model\HTTP\Response\Generic as Response;
	
	// Create response
	$Response = new Response('HTTP','1.1',200);
	
	// Current URL of response
	$Response->URI = new URI('http://example.com');
	
	// Original request URI
	$ResponseRequestURI = new URI('http://example.com/redirect.php');
	
	// Custom Headers
	$Response->Header['Server'] = new $Response->createHeader('Server', 'PHP');
	$Response->Header['Date'] = new $Response->createHeader('Date', 'Thu, 10 Apr 2014 18:08:23 GMT');
	$Response->Header['Content-Type'] = new $Response->createHeader('Content-Type', 'text/html');
	
	// Body
	$Response->Body['Content'] = '<doctype html><html><head>....';
	
	// Custom values
	$Response['Custom'] = 'custom value';
	
	echo $Response;
	
##### Example: RAW Response #####

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
	
	print $Response->Body;
	
	//<doctype html>
	//<html>
	//<head>
	//......
	
	?>
	
[RFC 2616]: <https://tools.ietf.org/html/rfc2616> "RFC 2616"
[RFC 2617]: <https://tools.ietf.org/html/rfc2617> "RFC 2617"
[RFC 2388]: <http://tools.ietf.org/html/rfc2388> "RFC 2388"
[RFC 6265]: <http://tools.ietf.org/html/rfc6265> "RFC 6265"
[Guzzle]: <https://github.com/guzzle/guzzle>
[Artax]: <https://github.com/rdlowrey/Artax>

[BLW\HTTP Framework]: <../../../Type/HTTP/>

[IResponse]: <../../../Type/HTTP/IResponse.php>