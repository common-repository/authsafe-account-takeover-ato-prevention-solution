(function() {
	/**
	 * AuthSafe device initialization
	 */

	var anchors = document.querySelectorAll("a[href*='logout']");
	for(var i=0; i<anchors.length; i++) {
		if(anchors[i].addEventListener){
			anchors[i].addEventListener("click", logoutClickHandler, false);  //Modern browsers
		}else if(anchors[i].attachEvent){
			anchors[i].attachEvent('onclick', logoutClickHandler);            //Old IE
		}
	}

	function logoutClickHandler(e) {
		e.preventDefault();
		var device_id = _authsafe("getRequestString");
		var href = this.href;
		href+= '&did='+device_id;
		window.location = href;
	}

})();
