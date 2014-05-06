TODO
====

- Switch from phantomjs.exe to nodejs.exe as main JavaScript Engine
- Add more browsers
- Add forms plugin with dynamic methods:
		
		type(DOMNode $Field, scalar|null $Value)
		select(DOMNode $Field, scalar|null $Value)
		fill(DOMNode $Form, array $Values)
		submit(DOMNode $Form)

- Add OAuth Engine and plugins for: Google, Facebook, Twitter
- Add Cookie Engine for cookies support. (Should be compatible with PhantomJS and NodeJS Engines)
- Add Generic Cookie / Cache handler for use with clients.
- `Request::createFromString()`
- Make Request classes compatible with the following libraries: `Guzzle, Symfony, BUZZ, Zend`
