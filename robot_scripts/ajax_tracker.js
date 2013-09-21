var _ss_ajax_tracker = function(){

	var requests = [];
	var ajaxIsDone = false;

	var oldOpen = XMLHttpRequest.prototype.open;
	XMLHttpRequest.prototype.open = function(){
		
		this.addEventListener("loadstart", function(event){
			requests.push(event.target);
		}, false);

		this.addEventListener("loadend", function(event){

			for( var i = requests.length - 1; i >= 0; i-- ){
				if( requests[i] === event.target ){
					requests.splice(i, 1);
				}
			}

		}, false);

		oldOpen.apply(this, arguments);
	};

	setTimeout(function(){

		var checkAjaxRequests = function(){
			setTimeout(function(){
				if (requests.length === 0){
					ajaxIsDone = true;
				}else{
					checkAjaxRequests();
				}
			}, 500);
		};

		checkAjaxRequests();

	}, 1000);

	return {
		checkAjax: function(){
			return ajaxIsDone;
		}
	};

}();