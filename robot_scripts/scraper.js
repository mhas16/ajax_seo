//example CLI execution "phantomjs scraper.js url=http://blah width=1024 height= 800"

try {

	//Setting the default values
	var config = {
		url: '',
		width: '3000',
		height: '800'
	};


	var args = require('system').args;

	//forEach is a callback, a recursion, so to skip the first loop we use return instead of continue
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

		if (key === 'url') config.url = propValue;
		if (key === 'width') config.width = propValue;
		if (key === 'height') config.height = propValue;

	});

	var fs = require('fs');

	// Outputing the data back to the Node process - NOTE: Needs further work
	var outputData = function(output){
		// console.log(JSON.stringify(output));
		fs.write('log.txt', output.html, 'w');
		fs.write('imagelog.txt', output.screenshot, 'w');
	};

	// Creating the page object representing the API to manipulate the page.
	var page = require('webpage').create();

	// Setting the viewportsize object properites
	page.viewportSize = {
		width: config.width,
		height: config.height
	};


	var requests = [];
	var requestsComplete;

	//This callback will be executed at least once, due to the initial request to the domain. 
	page.onResourceRequested = function(resource){
		
		
		requests.push(resource.id);
		requestsComplete = false;
		console.log(JSON.stringify(resource));
	};

	page.onResourceReceived = function(resource){
		var index = requests.indexOf(resource.id);
		if (index != -1) {
			requests.splice(index, 1);
		}
		if(requests.length == 0){
			requestsComplete = true;
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

	page.onError = function(msg){
		console.log(msg);
	};

	page.onAlert = function(msg){
		console.log(msg);
	};

	page.onConsoleMessage = function(msg){
		console.log(msg);
	};

	page.settings.loadImages = true;
	page.settings.localToRemoteUrlAccessEnabled = true;
	page.settings.userAgent = 'SnapSearch';
	page.settings.webSecurityEnabled = false;

	// Loading the page and executing the function on the onLoadFinished event
	page.open(config.url, function(status){
		if(status === 'success'){

			var evaluateOutput = function(){

				var screenshot = page.renderBase64('png');

				page.render('screen.png');

				page.evaluate(function(){
					document.body.style.backgroundColor = '#FFFFFF';
				});
				var html = page.evaluate(function(){
					return document.all[0].outerHTML;
				});

				outputData({
					screenshot: screenshot,
					html: html,
					date: Math.floor(Date.now()/1000) //convert to unix timestamp and round to highest seconds
				});

				phantom.exit();

			};

			var checkResourceRequests = function(){
				setTimeout(function(){
					if(requestsComplete){
						evaluateOutput();
					}else{
						checkResourceRequests();	
					}
				}, 500);
			};

			//Calling the recursive timeout func
			checkResourceRequests();
			

		}else{
			console.log('Failed Loading!');
			phantom.exit();	
		}
	});

} catch(e) {

	console.log(e.name + ': ' + e.message);

	//Making sure that we dont have any zombie process
	phantom.exit();

}