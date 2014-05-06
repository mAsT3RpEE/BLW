Browser Component
-----------------
These are BLW Libraries HTTP Browsers. All classes are built on the [BLW\HTTP][BLW\HTTP Framework] framework.

They are responsible for managing the [IMediator][] class that handles interclass communication as well as managing browser [DOM][IDocument], [Engines][IEngine], [Plugins][IPlugin] and [HTTP Client][IClient].

For more information, see [IBrowser][] interface and [API Documentation][Browser API]

##### Example: Basics #####

	<?php
	
	use BLW\Model\GenericURI as URL;
	use BLW\Model\HTTP\Browser\Nexus as Browser;
	
	$Browser = new Browser('MaxHistory' => 3);
	
	// Basic navigation
	$Browser->go('http://example.com/page/1');
	$Browser->go('http://example.com/page/2');
	$Browser->go(new URL('http://example.com/page/3'));
	
	// Forwards and backwords
	$Browser->back(); // page/2
	$Browser->back(); // page/1
	$Bworser->foward(); // page/2
	
	// Print out headings
	$Browser->filter('h1, h2, h3, h4, h5')->each(function($Node, $index){
		echo $Node->textContent . "\r\n";
	});
	
	// DOM Manipulation
	$Browser->loadHTML('<html><head><title>foo</title></head><body><h1>Heading</h1><p>Paragraph 1</p></body></html>';
	
	$Node = $Browser->createFromString('<p>Paragraph 2</p>');
	
	$Browser->filter('p')->offsetGet(0)->parentNode->append($Node);
	
	echo $Browser->saveHTML();
	
	?>
	
##### Example: Dynamic functions #####

	<?php
	
	use BLW\Model\GenericURI as URL;
	use BLW\Model\HTTP\Browser\Nexus as Browser;
	
	$Browser = new Browser;
	
	$Browser->go('http://example.com');
	
	// Register dynamic function (click)
	$Browser->_on('click', function(BLW\Type\IEvent $Event) {
		
		// If 1st element is not a DOMElement
		if (count($Event->Arguments) < 1 ?: !$Event->Arguments[0] instanceof DOMElement) {
		
			// Exception
			$this->exception('IBrowser::click(DOMElement $Element) Argument 1 is invalid');
			return NULL;
		} 

		// Extract arguments		
		list($Node) = $Event->Arguments;
		
		// Get browser
		$Browser = $Event->Subject->Parent
		
		// Event->Subject == current page
		// current page->parent == browser

		// Does current element have an address?			
		$Address = $Node->getAttribute('href') ?: $Node->getAttribute('src');
		$URI	 = new URL($Address, $Browser->URI);
		
		/*
		 * Note:
		 * $this->URI is passed in order to resolve relative URI's
		 */
			
		if ($URI->isValid() && $URI->isAbsolute() && $URI != $Browser->URI) {
		
			$Browser->go($URI);
		}
	});
	
	// Use new dynamic function
	$Browser->click($Browser->filter('a[href]')->offsetGet(0));
	
	?>


##### Example: Click Plugin #####

	<?php
	
	use BLW\Type\HTTP\IBrowser;
	use BLW\Model\GenericURI as URL;
	use BLW\Model\HTTP\Browser\Nexus as Browser;
	
	class ClickPlugin extends BLW\Type\HTTP\Browser\APlugin
	{
		public static function getSubscribedEvents()
		{
			$ID = IBrowser::MEDIATOR_ID;
	
			return array(
				 "$ID.click" => array('doClick', -10)
			);
		}
		
		pubic function doClick(BLW\Type\IEvent $Event)
		{	
			// If 1st element is not a DOMElement
			if (count($Event->Arguments) < 1 ?: !$Event->Arguments[0] instanceof DOMElement) {
			
				// Exception
				$this->exception('IBrowser::click(DOMElement $Element) Argument 1 is invalid');
				return NULL;
			} 
	
			// Extract arguments		
			list($Node) = $Event->Arguments;
			
			// Get browser
			$Browser = $Event->Subject->Parent
			
			// Debug info
			$Browser->debug(sprintf('Clicked on node (%s)', $Node->getNodePath());
			
			// Event->Subject == current page
			// current page->parent == browser
	
			// Does current element have an address?			
			$Address = $Node->getAttribute('href') ?: $Node->getAttribute('src');
			$URI     = new URL($Address, $Browser->URI);
			
			/*
			 * Note:
			 * $Browser->URI is passed in order to resolve relative URI's
			 */
				
			if ($URI->isValid() && $URI->isAbsolute() && $URI != $Browser->URI) {
				// Always give debug info beforea any action that can fail
				$Browser->debug(sprintf('Clicked navigation to (%s)', $URI);
				$Browser->go($URI);
			}
		}
	}
	
	$Browser                   = new Browser;
	$Browser->Plugins['click'] = new ClickPlugin;
	
	$Browser->go('http://example.com');
	
	// Use new dynamic function
	$Browser->click($Browser->filter('a[href]')->offsetGet(0));
	
	?>

##### Example: JavaScript Emulation with PhantomJS #####

	<?php
	
	use BLW\Model\GenericFile as File;
	
	use BLW\Model\Config\Generic as Config;
	
	use BLW\Model\HTTP\Client\cURL
	use BLW\Model\HTTP\Browser\Nexus;
	
	use Monolog\Logger;
	use Monolog\Handler\StreamHandler;
	
	$Logger  = new Logger('browser', new StreamHandler(
		sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'browser.log',
		Logger::DEBUG
	));
	
	$Browser = new Nexus(new cURL, NULL, $Logger);
	
	$Browser->Engines['PhantomJS'] = new PhantomJS(
		new File('phantomjs'),
		new File('path/to/phantomjs/config.json'),
		new Config(array(
			'Timeout'    => 30,	
			'CWD'        => NULL,
			'Enviroment' => NULL,
			'Extras'     => NULL,
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
	
[BLW\HTTP Framework]: <../../../Type/HTTP/>
 
[IMediator]: <../../../Type/IMediator.php>
[IDocument]: <../../../Type/DOM/IDocument.php>
[IEngine]: <../../../Type/HTTP/Browser/IEngine.php>
[IPlugin]: <../../../Type/HTTP/Browser/IPlugin.php>
[IClient]: <../../../Type/HTTP/IClient.php>
[IBrowser]: <../../../Type/HTTP/IBrowser.php>
[Browser API]: <>