/*
	gui.js

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met: 

	1. Redistributions of source code must retain the above copyright notice, this
	   list of conditions and the following disclaimer. 
	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution. 

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies, 
	either expressed or implied, of the FreeBSD Project.
*/

// backward compatibility
function showElementById(id, state) {
	switch (state) {
		case "show":
	    		jQuery('#'+id).show(); break;
		case "hide":
			jQuery('#'+id).hide(); break;
	}
}

// prevent enter key in the form
jQuery(document).keypress(function(e){
	if (e.which == 13) {
		if (e.target.type == "text" || e.target.type == "checkbox") {
			e.preventDefault();
			return;
		}
	}
});

// gui constructor and methods
var GUI = function(){
	this.timer = null;
	this.setup();
};
GUI.prototype = {
	setup: function() {
		var self = this;
		// other setup...
	},
	recall: function(firstTime, nextTime, url, data, callback) {
		var self = this;
		self.timer = setTimeout(function ajaxFunc() {
			jQuery.when(
				jQuery.ajax({
					type: 'GET',
					url: url,
					dataType: 'json',
					data: data,
				})
			).then(function(data, textStatus, jqXHR) {
				callback(data, textStatus, jqXHR);
				self.timer = setTimeout(ajaxFunc, nextTime);
			}, function(jqXHR, textStatus, errorThrown) {
				clearTimeout(self.timer);
			});
		}, firstTime);
		return self;
	},
	ajax: function(url, data, callback) {
		var self = this;
		jQuery.when(
			jQuery.ajax({
				type: 'GET',
				url: url,
				dataType: 'json',
				data: data,
			})
		).then(function(data, textStatus, jqXHR) {
			callback(data, textStatus, jqXHR);
		}, function(jqXHR, textStatus, errorThrown) {
		});
		return self;
	}
};
