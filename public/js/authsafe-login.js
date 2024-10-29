(function() {
	/**
	 * AuthSafe user initialization
	 */

	var formElem = document.querySelector(".login form");
	if(formElem.addEventListener){
		formElem.addEventListener("submit", submitHandler, false);  //Modern browsers
	}else if(formElem.attachEvent){
		formElem.attachEvent('onsubmit', submitHandler);            //Old IE
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
