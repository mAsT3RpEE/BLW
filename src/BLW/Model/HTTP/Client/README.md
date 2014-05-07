Client Component
----------------

These are BLW Libraries HTTP Clients. All classes are built on the [BLW\HTTP][BLW\HTTP Framework] framework.

They are responsible for quing and executing HTTP Requests implementing [IRequest][] interface and returning Responses based on [IResponse][] interface. They are also responsible for handling header information in browser requests / responses.

For more information, see [IClient][] interface and [API Documentation][Client API]

##### Example: cURL Basics #####

```php
<?php

use BLW\Model\GenericURI as URL;
use BLW\Model\HTTP\Client\cURL as Client;
use BLW\Model\HTTP\Request\Generic as Request;

// Create Request	
$Request 		= new Request;
$Request->URI		= new URL('http://example.com');
$Request->Referer	= new URL('http://searchengine.com');

// Create client
$Client = new Client;

// Add request to que
$Client->send($Request);

// Execute request
$Client->run();

// Retrieve response
$Response = $Client[$Request];

// Free up slot
$Client->detach($Request);

echo $Response;

?>
```
	
##### Example: cURL Parrallel requests #####

```php
<?php

use BLW\Model\HTTP\Client\cURL as Client;

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

##### Example: cURL file download #####

```php
<?php
use BLW\Model\GenericFile as File;
use BLW\Model\Stream\File as FileStream;
use BLW\Model\GenericURI as URL;
use BLW\Model\HTTP\Client\cURL as Client;
use BLW\Model\HTTP\Request\Generic as Request;

// Create Request	
$Request 		= new Request;
$Request->URI		= new URL('http://example.com');
$Request->Referer	= new URL('http://searchengine.com');

// Create stream
$File   = 'path/to/file';
$Stream = new FileStream(new File($File));

// Download File into stream
$Client = new Client;

$Client->download($Request, $Stream);

// Call stream destructor / flush stream
unset($Stream);

// or
// $Client->download($Request, new FileStream(new File($File)));

echo file_get_contents($File);

?>
```
 
[BLW\HTTP Framework]: <../../../Type/HTTP/>
 
[IRequest]: <../../../Type/HTTP/IRequest.php>
[IResponse]: <../../../Type/HTTP/IResponse.php>
[IClient]: <../../../Type/HTTP/IClient.php>
[Client API]: <javascript:;>
['Finished']: <javascript:;>
['Running']: <javascript:;>