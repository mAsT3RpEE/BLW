Request Component
-----------------

This contains BLW Libraries HTTP Request classes for making and handling HTTP Requests. All classes are built on the [BLW\HTTP][BLW\HTTP Framework] framework.

A request is either created from a string or manually using [IRequest][] API

##### Inspiration #####

- [Hypertext Transfer Protocol -- HTTP/1.1][RFC 2616]
- [HTTP Authentication: Basic and Digest Access Authentication][RFC 2617]
- [Returning Values from Forms:  multipart/form-data][RFC 2388]
- [HTTP State Management Mechanism][RFC 6265]
- [Guzzle][]
- [Artax][]

##### Example: GET: Basics #####

```php
<?php

use BLW\Model\GenericURI as URL;
use BLW\Model\Config\Generic as Config;
use BLW\Model\HTTP\Request\Generic as Request;
use BLW\Model\MIME;

// Create Request
$Request = new Request(IRequest::GET, new Config(array(
	'Timeout'		=> 10, 		// Abort request after 10 seconds
	'MaxRedirects'	=> 4, 		// Maximum redirects to follow
	'EnableCookies'	=> true,	// Use cookies

)));

// Target
$Request->URI = new URL('http://example.com/get.php?foo=1');

// Add Get var
$Request->URI['query']['bar'] = 1;

// 'http://example.com/get.php?foo=1&bar=1'

// Edit Foo var
$Request->URI['query']['foo'] = 2;

// 'http://example.com/get.php?foo=2&bar=1'

// Change config
$Request->Config['Timeout'] = 30;

// Referer
$Request->Referer = new URL('http://example.com');

// Custom Header
$Request->Headers['Accept'] = $Request->createHeader('Accept', '*/*;q=0.8');

// See results
echo $Request;

?>
```
	
##### Example: POST: application/x-www-form-urlencoded #####

```php
<?php

use BLW\Model\GenericEmail as Email;
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

// Content Type Header
$Request->Headers['Content-Type'] = new MIME\ContentType('application/x-www-form-urlencoded');

// Fields
$Fields = array();

// Field with encoding
$Fields[] = new MIME\Part\Field('name', 'text/html', 'Alexander', 'ASCII');

// Objects implementing `__toString`
$Fields[] = new MIME\Part\Field('email', 'text/html', new Email('alex@example.com'));

// Arrays
$Fields[] = new MIME\Part\FormField('info[country]', 'text/plain', 12);
$Fields[] = new MIME\Part\FormField('info[state]', 'text/plain', 27);

// Message Body
$Request->Body['Content'] = new FormData($Fields);

echo strval($Request);
?>
```

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

echo $Request;
?>
```
	
##### Example: RAW #####

```php
<?php

use BLW\Model\HTTP\Request\Generic as Request;


$String = <<<EOT
GET /post.php?foo=1 HTTP/1.1
Host: example.com
....';

$Request = Request::createFromString($String);

?>
```

[RFC 2616]: <https://tools.ietf.org/html/rfc2616> "RFC 2616"
[RFC 2617]: <https://tools.ietf.org/html/rfc2617> "RFC 2617"
[RFC 2388]: <http://tools.ietf.org/html/rfc2388> "RFC 2388"
[RFC 6265]: <http://tools.ietf.org/html/rfc6265> "RFC 6265"
[Guzzle]: <https://github.com/guzzle/guzzle>
[Artax]: <https://github.com/rdlowrey/Artax>

[BLW\HTTP Framework]: <../../../Type/HTTP/>

[IRequest]: <../../../Type/HTTP/IRequest.php>
['bar']: <javascript:;>
['foo']: <javascript:;>