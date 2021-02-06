document.addEventListener("DOMContentLoaded", function(event) { 
	var lastContainer = null;
	var lastVideoMarkup = null;
	var lastVideoProvider = null;
	var lastCookieName = null;
	var disablerememberme, remembermedays;
	var rememberCookieName = "skvideoremember";
	var videoElements = document.getElementsByClassName("sk-video-playbutton");
	for (var i = 0; i < videoElements.length; i++) {
	    videoElements[i].addEventListener('click', confirmVideo, false);
	}
	function confirmVideo() {
		lastVideoMarkup = this.getAttribute("data-videomarkup");
		lastVideoProvider = this.getAttribute("data-type");
	    lastContainer = findAncestor(this, 'sk-video-container');
		var lastCookieName = rememberCookieName+lastVideoProvider;
	    if (getCookie(lastCookieName) == 1) {
	    	lastContainer.innerHTML = lastVideoMarkup;
	    	return;
	    }
	    var modalElement = document.querySelector(".sk-video-modal");
		var message = this.getAttribute("data-message");
		disablerememberme = this.getAttribute("data-disablerememberme");
		if (disablerememberme === '1') {
			disablerememberme = true;
		}
		else {
			disablerememberme = false;
		}
	    if (modalElement == null) {
	    	var rememberme = this.getAttribute("data-rememberme");
	    	var cancel = this.getAttribute("data-cancel");
			var continuemsg = this.getAttribute("data-continue");

			var continueclass = this.getAttribute("data-continueclass");
			var cancelclass = this.getAttribute("data-cancelclass");

			remembermedays = parseInt(this.getAttribute("data-remembermedays"));
			if (isNaN(remembermedays) || remembermedays>180 || remembermedays < 0) {
				remembermedays = 30;
			}
			var remembermeMarkup = '';
			if (!disablerememberme) {
				remembermeMarkup = '<label><input type="checkbox"> '+rememberme+'</label>';
			}

			var html = '<div class="sk-video-modal"><div><span class="disclaimer">'
				+message+'</span> '
				+remembermeMarkup+'<br><button class="cancel '+cancelclass+'">'
				+cancel+'</button> <button class="continue '+continueclass+'">'
				+continuemsg+'</button></div></div>';

			appendHtml(document.body, html);
			document.querySelector(".sk-video-modal .cancel").addEventListener("click", function(){
			  var modalElement = document.querySelector(".sk-video-modal");
			  removeClass(modalElement, "active");
			});
			document.querySelector(".sk-video-modal .continue").addEventListener("click", function(){
			  var modalElement = document.querySelector(".sk-video-modal");
			  removeClass(modalElement, "active");
			  if (!disablerememberme) {
				  handleRemember(lastCookieName);
			  }
			  lastContainer.innerHTML = lastVideoMarkup;
			});
		}
	    else {
			// reset the message as it may contain the video provider which may be different for multiple videos on same page
			document.querySelector(".sk-video-modal .disclaimer").innerHTML = message;
		}
		modalElement = document.querySelector(".sk-video-modal");

		if (!disablerememberme) {
			document.querySelector(".sk-video-modal input[type='checkbox']").checked = false;
		}
		addClass(modalElement, "active");
	};

	function handleRemember(cookieName) {
		var rememberChecked = document.querySelector(".sk-video-modal input[type='checkbox']").checked;
		if (rememberChecked) {
			setCookie(cookieName, 1, remembermedays);
		}
	}
	function setCookie(name,value,days) {
	    var expires = "";
	    if (days) {
	        var date = new Date();
	        date.setTime(date.getTime() + (days*24*60*60*1000));
	        expires = "; expires=" + date.toUTCString();
	    }
	    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
	}
	function getCookie(name) {
	    var nameEQ = name + "=";
	    var ca = document.cookie.split(';');
	    for(var i=0;i < ca.length;i++) {
	        var c = ca[i];
	        while (c.charAt(0)==' ') c = c.substring(1,c.length);
	        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	    }
	    return null;
	}
	function findAncestor (el, cls) {
		var count = 0;
	    while ((el = el.parentElement) && !el.classList.contains(cls)) {
	    	count++;
	    	if (count > 1000 || typeof el == 'undefined') {
	    		return null;
	    	}
	    };
	    return el;
	}

	function appendHtml(el, str) {
	  var div = document.createElement('div');
	  div.innerHTML = str;
	  while (div.children.length > 0) {
	    el.appendChild(div.children[0]);
	  }
	}

	function hasClass(ele,cls) {
	  return !!ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
	}

	function addClass(ele,cls) {
	  if (!hasClass(ele,cls)) ele.className += " "+cls;
	}

	function removeClass(ele,cls) {
	  if (hasClass(ele,cls)) {
	    var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
	    ele.className=ele.className.replace(reg,' ');
	  }
	}
});


