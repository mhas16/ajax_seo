var fs = require('fs'),
	system = require('system'),
	page = require('webpage').create(),
	server = require('webserver').create();

phantom.onError = function (msg, stack) {
	var msg = "\nScript Error: "+msg+"\n";
	if (stack && stack.length) {
		msg += "       Stack:\n";
		stack.forEach(function(t) {
			msg += '         -> ' + (t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function + ')' : '')+"\n";
		})
	}

	fs.write('log.txt', msg + "\n", 'a');

	phantom.exit();
}

var defaultConfig = {
	ipaddress: '127.0.0.1',
	port: 8989,
	width: '1024',
	height: '800',
	imgformat: 'png',
	useragent: 'SnapSearch',
	loadimages: true,
	javascriptenabled: true,
	maxtimeout: 5000
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

});

/**
	** Creating the webserver object **
**/

var service = server.listen(defaultConfig.ipaddress + ':' + defaultConfig.port, function(request, response){

	console.log('Slimerjs Server started at: ' + defaultConfig.ipaddress + ':' + defaultConfig.port);	

	var input = request.post;
	input = JSON.parse(input);

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

	// support cliprect
	// support dom mutation for animation

	// Adding the properties of the input object to the defaultConfig object
	for (var key in input) {
		defaultConfig[key] = input[key];
	}

	page.viewportSize = {
		width: 1024,
		height: 768
	};

	page.settings.userAgent = defaultConfig.useragent;
	page.settings.loadImages = defaultConfig.loadimages;
	page.settings.javascriptEnabled = defaultConfig.javascriptenabled;

	var beginExtraction = function(url){

		var requests = [];
		var requestsComplete;
		var redirectUrl = false;

		page.onNavigationRequested = function(newUrl, type, willNavigate, main) {
        	if (main && newUrl != url && newUrl.replace(/\/$/,"") != url && (type == "Other" || type == "Undefined")){
        		redirectUrl = newUrl;
        	}
        };		

		//This callback will be executed at least once, due to the initial request to the domain. 
		page.onResourceRequested = function(resource){

			requests.push(resource.id);
			requestsComplete = false;
		};

		page.onResourceReceived = function(resource){

			if (response.stage == 'end'){

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

			var index = requests.indexOf(resource.id);
			if (index != -1) {
				requests.splice(index, 1);
			}
			if(requests.length == 0){
				requestsComplete = true;
			}

		};

		var outputData  = function(message, html, screenshot, code){

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
			
			response.write(payload);
			response.closeGracefully();

		};

		page.open(url).then(function(status){

			if (redirectURL){

				page.close();
				beginExtraction(redirectUrl);

			}else if (status == "success") {

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

					if (defaultConfig.loadimages) {
						screenshot = page.renderBase64(defaultConfig.imgformat);
					}
					
					var html = page.evaluate(function(){
						return document.all[0].outerHTML;
					});

					page.close();

					outputData('Succesfully loaded page.', html, screenshot, 200);

				};

				// setting up a recursive function to check when the requestsComplete is true to start outputing data
				var checkResourceRequests = function(){
					setTimeout(function(){
						if(requestsComplete){
							evaluateOutput();
						}else{
							checkResourceRequests();	
						}
					}, 500);
				};

				//Calling the recursive timeout func at least once
				checkResourceRequests();

			}else{

				page.close();
				outputData('Failed to load URL: ' + defaultConfig.url, '', '', 400);

			}

		});

	};

	beginExtraction(defaultConfig.url);

});	
	
if (!service){
	console.log('Unable to start webserver on ' + defaultConfig.ipaddress + ':' + defaultConfig.port);
	phantom.exit();
}