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

	var formElem = document.querySelector(".lost_reset_password,form.login");
	if(formElem) {
		if(formElem.addEventListener){
			formElem.addEventListener("click", submitHandler, false);  // Modern browsers
		}else if(formElem.attachEvent){
			formElem.attachEvent('onclick', submitHandler);            // Old IE
		}
	}

	function submitHandler() {
		var device_id = _authsafe("getRequestString");
		var deviceidelement = document.createElement('input');
		deviceidelement.setAttribute('type', 'hidden');
		deviceidelement.setAttribute('name', 'device_id');
		deviceidelement.setAttribute('value', device_id);
		formElem.appendChild(deviceidelement);
	}

})();
