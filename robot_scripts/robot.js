var fs = require('fs'),
	system = require('system'),
	page = require('webpage').create(),
	server = require('webserver').create();

var defaultConfig = {
	ipaddress: '127.0.0.1',
	port: 8989,
	width: '1024',
	height: '800',
	imgformat: 'png',
	useragent: 'SnapSearch',
	loadimages: true,
	javascriptenabled: true,
	maxtimeout: 5000,
	logfile: 'log.txt' // Log file is recorded in the current working directory of where you started the web server, it is not the same as this script's path
};

var args = system.args;

args.forEach(function(value, index){

	//skip the first arg (the script name)
	if(index === 0){
		return;
	}
	
	var key = value.substr(0, value.indexOf('='));
	var propValue = value.substr(value.indexOf('=') + 1);

	if (!key || !propValue) {
		console.log('Incorrect parameters format, use key=value');
		phantom.exit();
	}

	if (key === 'ipaddress') defaultConfig.ipaddress = propValue;
	if (key === 'port') defaultConfig.port = propValue;
	if (key === 'width') defaultConfig.width = propValue;
	if (key === 'height') defaultConfig.height = propValue;
	if (key === 'imgformat') defaultConfig.imgformat = propValue;
	if (key === 'useragent') defaultConfig.useragent = propValue;
	if (key === 'loadimages') defaultConfig.loadimages = propValue;
	if (key === 'javascriptenabled') defaultConfig.javascriptenabled = propValue;
	if (key === 'maxtimeout') defaultConfig.maxtimeout = propValue;
	if (key === 'logfile') defaultConfig.logfile = propValue;

});

var logError = function(exception){
	var msg = exception.message + ' - ' + exception.fileName + ' - ' + exception.lineNumber + ' - ' + (new Date()).toString();
	console.log('Robot hit an error: ' + msg);
	fs.write(defaultConfig.logfile, msg + "\n", 'a');
};

var busy = false;

/**
	** Creating the webserver object **
**/

var service = server.listen(defaultConfig.ipaddress + ':' + defaultConfig.port, function(request, response){

	var outputData  = function(message, html, screenshot, code){

		console.log('Robot is giving back data');

		response.statusCode = code;

		var payload = {

			message: message,
			html: html,
			screenshot: screenshot,
			date: Math.floor(Date.now()/1000),

		};

		payload = JSON.stringify(payload);

		response.headers = {
			'Cache' : 'no-cache',
			'Content-Type' : 'application/json',
			'Content-Length' : payload.length
		};
		
		try{
			
			response.write(payload);

			response.closeGracefully();

		}catch(e){

			console.log(e.message);


		}

	};

	if(busy){
		console.log('Robot is busy and cannot process this task');
		outputData('Robot is busy', '', '', 429);
	}

	/*
		{
			url:
			width:
			height:
			imgformat:
			useragent: 
			loadimages:
			javascriptenabled:
			maxtimeout:
			animation:
		}
	*/

	//maxtimeout
	// support cliprect
	// support dom mutation for animation

	console.log('Robot is running a task');

	busy = true;

	var currentConfig = JSON.parse(JSON.stringify(defaultConfig));

	try{
		var input = String.trim(request.post);
		input = JSON.parse(input);
	}catch(e){
		logError(e);
	}

	// Adding the properties of the input object to the currentConfig object
	for (var key in input) {
		currentConfig[key] = input[key];
	}

	page.viewportSize = {
		width: currentConfig.width,
		height: currentConfig.height
	};

	page.settings.userAgent = currentConfig.useragent;
	page.settings.loadImages = currentConfig.loadimages;
	page.settings.javascriptEnabled = currentConfig.javascriptenabled;

	var requestPage = function(url){

		var requests = [];
		var requestsComplete;
		var redirectUrl = false;

		page.onClosing = function(){

			console.log('Robot has closed page');
			busy = false;
		
		};

		page.onNavigationRequested = function(newUrl, type, willNavigate, main){

			console.log('Robot is navigating to: ' + newUrl);
			if (main && newUrl != url && newUrl.replace(/\/$/,"") != url && (type == "Other" || type == "Undefined")){
				redirectUrl = newUrl;
			}

		};

		// This callback will be executed at least once, due to the initial request to the domain. 
		page.onResourceRequested = function(resource){

			console.log('Robot is requesting: ' + resource.url);
			requests.push(resource.id);
			requestsComplete = false;

		};

		page.onResourceReceived = function(resource){

			console.log('Robot is receiving: ' + resource.url + ' at ' + resource.stage);
			if (resource.stage == 'end'){
				var index = requests.indexOf(resource.id);
				if (index != -1) {
					requests.splice(index, 1);
				}
				if(requests.length == 0){
					requestsComplete = true;
				}
			}

		};

		page.onResourceError = function(resource){

			console.log('Robot could receive: ' + resource.url);
			var index = requests.indexOf(resource.id);
			if (index != -1) {
				requests.splice(index, 1);
			}
			if(requests.length == 0){
				requestsComplete = true;
			}

		};

		page.open(url).then(function(status){

			outputData('success', '', '', 200);

			if (redirectUrl){

				console.log('Robot is redirecting to ' + redirectUrl);
				page.close();
				requestPage(redirectUrl);

			}else if(status == "success"){

				console.log('Robot has loaded all synchronous requests');

				// declaring the output data function that will be used after all requests are recie
				var evaluateOutput = function(){

					page.evaluate(function(){
						var style = document.createElement('style'),
						text = document.createTextNode('body { background: #fff }');
						style.setAttribute('type', 'text/css');
						style.appendChild(text);
						document.head.insertBefore(style, document.head.firstChild);
					});

					var screenshot = '';

					if (currentConfig.loadimages) {
						screenshot = page.renderBase64(currentConfig.imgformat);
					}
					
					var html = page.evaluate(function(){
						return document.documentElement.outerHTML;
					});

					outputData('Success', html, screenshot, 200);

					page.close();

					console.log('Robot has finished task');

				};

				outputData('Success', '', '', 200);


				// var checkResourceRequests = function(){
					
				// 	if (requestsComplete){
				// 		console.log('Robot loaded all asynchronous requests');
				// 		evaluateOutput();
				// 	}else{
				// 		slimer.wait(500);
				// 		checkResourceRequests();
				// 	}

				// };
				// setting up a recursive function to check when the requestsComplete is true to start outputing data
				// var checkResourceRequests = function(){
				// 	setTimeout(function(){
				// 		if(requestsComplete){
				// 			console.log('Robot loaded all asynchronous requests');
				// 			evaluateOutput();
				// 		}else{
				// 			checkResourceRequests();	
				// 		}
				// 	}, 500);
				// };

				// //Calling the recursive timeout func at least once
				// checkResourceRequests();

			}else{

				console.log('Robot failed to open page');
				outputData('Failure: ' + currentConfig.url, '', '', 400);
				page.close();

			}

		});

	};

	requestPage(currentConfig.url);

});

if (service){
	console.log('Slimerjs Server started at: ' + defaultConfig.ipaddress + ':' + defaultConfig.port);
}else{
	console.log('Unable to start webserver on ' + defaultConfig.ipaddress + ':' + defaultConfig.port);
	phantom.exit();
}