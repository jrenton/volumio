module.exports = {
	get : function(action, data, callback, error, nestedCaller) {
		//var caller = arguments.callee.caller.name.toString();

		this.ajax(action, "GET", data, callback, error, null, nestedCaller);
	},
	post : function(action, data, callback, error, nestedCaller) {
		//var caller = arguments.callee.caller.name.toString();

		this.ajax(action, "POST", data, callback, error, null, nestedCaller);
	},
	ajax: function(action, method, data, callback, error, caller, nestedCaller) {
		var debugText = "STARTING " + method + " request " + action + " from func " + caller;
		if (nestedCaller) {
			debugText += " via func " + nestedCaller;
		}

		console.debug(debugText + " with data=");
		console.log(data);
	    $.ajax({
	        type : method,
	        url : action,
	        dataType: "json",
			data: data,
	        async : true,
	        cache : false,
	        success : function(data) {
	            console.debug("FINISHED " + method + " request " + action + " from func " + caller + " with response =");
	            console.log(data);
	            if (typeof callback === "function") {
	            	callback(data);
	            }
	        },
	        error : function(a, b, c) {
	            console.error("FAILED " + method + " request " + action + " from func " + caller);
	            console.log(a);
                if (a.responseText) {
                    $('#errorResponseUrl').text(action);
                    $('#errorResponseContent').html(a.responseText);
                    $('#errorResponseModal').modal({
                        show: true
                    });
                }
	            console.log(b);
	            console.log(c);
	            if (typeof error === "function") {
	            	error(a, b, c);
	            }
	        }
	    });
	},
    ajaxPromise: function(action, method, data) {
        return $.ajax({
	        type : method,
	        url : action,
	        dataType: "json",
			data: data,
	        async : true,
	        cache : false
	    });
    }
}