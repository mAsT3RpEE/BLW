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
			output = output.match(/[\s\S]{1,1024}/g);

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

		System.stdout.write('\x04');
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
			if (request.url.match(/^data\x3aimage\x5c.*/i)) { return; }

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
			output = output.match(/[\s\S]{1,1024}/g);

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