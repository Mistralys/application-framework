/**
 * AJAX helper class used to simplify working with AJAX
 * requests within the application. Wraps around jquery's
 * ajax methods, and features automatic error handling.
 * 
 * To create a new AJAX handler instance:
 * 
 * <pre>
 * application.createAJAX('MethodName');
 * </pre>
 * 
 * @class Application_AJAX
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see application.createAJAX()
 */
var Application_AJAX =
{
	'ERROR_UNEXPECTED_RETURN_FORMAT':12301,
	'ERROR_UNHANDLED_AJAX_ERROR':12302,
		
	'methodName':null,
	'eventHandlers':null,
	'payload':null,
	'errorConfig':null,
	'sending':null,
	'completed':null,
	'simulate':null,
	'options':null,
	'logging':null,
	'customURL':null,
	'dataType':null,

	/**
	 * @constructor
	 * @param {String} methodName
	 */
	init:function(methodName)
	{
		this.methodName = methodName;
		this.eventHandlers = {};
		this.payload = {};
		this.sending = false;
		this.logging = true;
		this.completed = false;
		this.simulate = false;
		this.dataType = 'json';
		this.reportFailures = true;
		this.options = {
			'autoHideLoader':true,
			'async':true
		};
		this.errorConfig = {
			'message':t('Unhandled AJAX error'),
			'code':this.ERROR_UNHANDLED_AJAX_ERROR,
			'ifCodes':{}
		};
	},
	
	SetMethod:function(name)
	{
		this.methodName = name;
		return this;
	},
	
   /**
    * Changes the data type to HTML, so
    * the result will be HTML.
    * 
    * @return {Application_AJAX}
    */
	MakeHTML:function()
	{
		this.dataType = 'html';
		return this;
	},
	
   /**
    * Uses a custom AJAX URL instead of the regular
    * ajax repository.
    * 
    * @param {String} url
    * @return {Application_AJAX}
    */
	SetCustomURL:function(url)
	{
		if(!isEmpty(url)) {
			this.customURL = url;
			this.method = null;
		}
		
		return this;
	},
	
   /**
    * Sets an event handling function for the specified event.
    * 
    * @protected
    * @param {String} eventName
    * @param {Function} handler
    * @return {Application_AJAX}
    */
	SetEventHandler:function(eventName, handler)
	{
		if(!isEmpty(handler)) {
			this.eventHandlers[eventName] = handler;
		}
		
		return this;
	},
		
   /**
    * Sets the function that is called when the request
    * is sent successfully. The function gets one parameter:
    * the response data package.
    * 
    * @param {Function} handler
    * @return {Application_AJAX}
    */
	Success:function(handler)
	{
		return this.SetEventHandler('success', handler);
	},
	
   /**
    * Called when the user cancels the request, or dismisses
    * the error dialog when the request has failed. Otherwise
    * nothing is done at all.
    * 
    * @param {Function} handler
    * @return {Application_AJAX}
    */
	Cancel:function(handler)
	{
		return this.SetEventHandler('cancel', handler);
	},
	
   /**
    * Sets the function that is called when the request fails. 
    * 
    * The handler gets two parameters:
    * 
    * - The error text
    * - Any additional data pertaining to the error
    * 
    * <code>this</code> refers to the AJAX object instance.
    * 
    * NOTE: Use this if you wish to handle the error yourself.
    * Otherwise, use the {@link Error()} and {@link Retry()} methods
    * to have an error dialog be shown automatically.
    * 
    * @param {Function} handler
    * @return {Application_AJAX}
    * @see Error()
    */
	Failure:function(handler)
	{
		return this.SetEventHandler('failure', handler);
	},
	
   /**
    * Sets the data payload to send with the request. Must
    * be an object with variable name => value pairs.
    * 
    * @param {Object} data
    * @return {Application_AJAX}
    */
	SetPayload:function(data)
	{
		this.payload = data;
		return this;
	},
	
   /**
    * Sets the function that is called when the request completes,
    * regardless of success or failure. It is called right before
    * the success and failure handlers, so it is practical for cleanup.
    * 
    * @param {Function} handler
    * @return {Application_AJAX}
    */
	Always:function(handler)
	{
		return this.SetEventHandler('always', handler);
	},
	
   /**
    * Sets the function that is called when the request fails,
    * and the user wishes to retry sending the request. This is
    * only used in conjunction with setting the error details
    * using the {@link Application_Ajax.Error}.
    * 
    * NOTE: Even if set, this will be ignored if a failure handler is set.
    * 
    * If no retry handler is set, the builtin handler is used
    * which simply re-sends the same request.
    * 
    * @param {Function} handler
    * @return {Application_AJAX}
    */
	Retry:function(handler)
	{
		return this.SetEventHandler('retry', handler);
	},
	
   /**
    * Sets error details in case the request fails. This is used 
    * to automatically display an AJAX error dialog with the 
    * available error information.
    * 
    * A retry button can optionally be added to this dialog, using
    * the {@link Retry()} method.
    * 
    * NOTE: this is ignored if a failure handler is set.
    * 
    * @param {String} message Error message to display to the user
    * @param {Integer} code The error code to display
    * @param {String|null} ifErrorCode Only use this error message if the triggered error code equals this code.
    * @return {Application_AJAX}
    */
	Error:function(message, code, ifErrorCode)
	{
		if(isEmpty(message) || isEmpty(code)) {
			this.log('Empty error message or code specified.', 'error');
			return this;
		}
		
		// an error message for a specific error code
		if(!isEmpty(ifErrorCode)) 
		{
			this.errorConfig.ifCodes['E'+ifErrorCode] = {
				'message':message,
				'code':code
			};
		}
		else
		{
			this.errorConfig.message = message;
			this.errorConfig.code = code;
		}
		
		return this;
	},
	
   /**
    * Sets whether to only simulate the request: in this case,
    * no changes will be made to the database in any operations 
    * that are run serverside, and the debug output of the request
    * is opened in a new tab/window in the browser.
    * 
    * @param {Boolean} simulate
    * @return {Application_AJAX}
    */
	Simulate:function(simulate)
	{
		this.simulate = false;
		
		if(simulate == true) {
			this.simulate = true;
		}
		
		return this;
	},
	
   /**
    * @param {String} message
    * @param {String} category
    * @protected
    */
	log:function(message, category)
	{
		if(!this.logging) {
			return;
		}
		
		application.log('AJAX Method ['+this.methodName+']', message, category);
	},
	
   /**
    * Checks whether the request is still sending or receiving data.
    * @return {Boolean}
    */
	IsSending:function()
	{
		return this.sending;
	},
	
   /**
    * Sends the AJAX request as configured.
    * @return {Application_AJAX}
    */
	Send:function()
	{
		if(this.IsSending() || this.IsCompleted()) {
			return this;
		}
		
		this.sending = true;

		if(this.payload==null) {
			this.payload = {};
		}
		
    	// add a list of all client load keys that have been loaded
    	// so the target script can avoid loading them as required.
    	if(typeof(this.payload)=='string') {
    		this.payload += '&_loadkeys=' + application.getLoadKeyIDs().join(',');
    	} else {
        	this.payload['_loadkeys'] = application.getLoadKeyIDs().join(',');
    	}

    	this.log('Sending the request. Payload:', 'event');
    	if(this.logging) {console.log(this.payload);}
    	this.log('Simulation enabled: [' + bool2string(this.simulate, true).toUpperCase() + ']', 'ui');
    	
    	if(this.simulate) {
    		this.SendSimulation();
    		return this;
    	}

		this.payload['returnFormat'] = this.dataType;

		var ajax = this;
        $.ajax({
            'dataType': this.dataType,
            'type': 'POST',
            'async':this.options.async,
            'url': this.GetURL(),
            'data': this.payload,
            'success': function (data, textStatus, jqXHR) {
                ajax.Handle_SendSuccess(data, textStatus, jqXHR);
            },
            'error': function (jqXHR, textStatus, errorThrown) 
            {
                ajax.Handle_SendFailure(jqXHR, textStatus, errorThrown);
            }
        });
        
        return this;
	},
	
	GetMethodName:function()
	{
		return this.methodName;
	},
	
	GetPayload:function()
	{
		return this.payload;
	},
	
	GetDataType:function()
	{
		return this.dataType;
	},
	
   /**
    * Retrieves the full URL to the ajax script that will be called.
    * 
    * @return {String}
    */
	GetURL:function()
	{
		if(!isEmpty(this.customURL)) {
			return this.customURL;
		}
		
		return application.getAjaxURL(this.methodName);
	},

   /**
    * In simulation mode, we send the request as per normal but 
    * fetch the content as HTML and open it in a new window to
    * be able to review the output of the target script.
    * 
    * @protected
    */
	SendSimulation:function()
	{
		this.payload['simulate_only'] = 'yes';
		
		var ajax = this;
		application.fetchHTML(
			this.methodName, 
			this.payload, 
			function(data) {
				ajax.Handle_SendSimulationSuccess(data);
			}, 
			function(errorText, data) {
			    ajax.Handle_SendSimulationFailure(errorText, data);
			}
		);
	},
	
   /**
    * @protected
    * @param {Object} data
    */
	Handle_SendSimulationSuccess:function(data)
	{
		data = this.FilterData(data);
		
		this.Handle_Always();
		
		var w = window.open('about:blank', 'dummy-window-'+nextJSID());
		w.document.write('<p>Below is the output of the ajax request.</p>');
	    w.document.write('<pre>' + data + '</pre>');
	    w.document.close();
	    
	    this.GetEventHandler('success').call(this, data);
	},
	
   /**
    * Gets the event handler function that was set for the
    * specified event, or an empty function otherwise.
    * 
    * @param {String} eventName
    * @returns {Function}
    */
	GetEventHandler:function(eventName)
	{
		if(typeof(this.eventHandlers[eventName]) != 'undefined') {
			return this.eventHandlers[eventName];
		}
		
		// an empty function that does nothing, to allow
		// calling this method as if a handler existed.
		return function() {};
	},
	
   /**
    * Checks whether the request has completed (regardless of failure or success).
    * @returns {Boolean}
    */
	IsCompleted:function()
	{
		return this.completed;
	},
	
   /**
    * 
    * @protected
    * @param {String} errorText
    * @param {Object} data
    */
	Handle_SendSimulationFailure:function(errorText, data)
	{
		this.Handle_Always();
		
    	this.GetEventHandler('failure').call(this, errorText, data);
	},
	
   /**
    * Processes all tasks that have to be run every time the 
    * request is completed, regardless of response state.
    * @protected
    */
	Handle_Always:function()
	{
		this.sending = false;
		this.completed = true;
		
		if(this.options.autoHideLoader) {
			application.hideLoader();
		}
		
		this.GetEventHandler('always').call(this);
	},
	
   /**
    * @protected
    * @param {Object} data
    * @param {String} textStatus
    * @param {Object} jqXHR
    */
	Handle_SendSuccess:function(data, textStatus, jqXHR)
	{
		data = this.FilterData(data);
		
		this.Handle_Always();
		
		this.log('Request was sent successfully.', 'event');
		
		if(this.IsHTML()) {
			this.Handle_SendSuccess_HTML(data);
		} else {
			this.Handle_SendSuccess_JSON(data);
		}
	},
	
	Handle_SendSuccess_JSON:function(data)
	{
		if(typeof(data.state) == 'undefined') 
		{
			var error = this.TriggerError(
				t('Unexpected AJAX return format.'),
				this.ERROR_UNEXPECTED_RETURN_FORMAT,
				t('The expected %1$s data key was not present in the reponse.', 'state'),
				'',
				data
			);
			
			this.Handle_Error(error);
            return;
        }
        
        // handle json error status reply (the request succeeded,
        // but the target script returned an error). 
        if (data.state == 'error') 
        {
        	var details = null;
        	var trace = null;
        	var exData = null;
        	
        	// is an exception data array present?
        	if(typeof(data.data) != 'undefined' && data.data != null && typeof(data.data.isExceptionData) != 'undefined') 
        	{
        		exData = data.data;
        		details = exData.details;
        		trace = exData.trace;
        	}
        	
        	var error = this.TriggerError(
    			data.message,
    			data.code,
    			details,
    			trace,
    			exData
			);
        	
        	this.Handle_Error(error);
            return;
        }

        this.GetEventHandler('success').call(this, data.data);
	},
	
	Handle_SendSuccess_HTML:function(html)
	{
		this.GetEventHandler('success').call(this, html);
	},
	
	IsJSON:function()
	{
		return this.dataType == 'json';
	},
	
	IsHTML:function()
	{
		return this.dataType == 'html';
	},

	/**
	 * Disables or enables the reporting of failures to the server.
	 *
	 * @param {Boolean} state
	 * @return {Application_AJAX}
	 */
	SetReportFailure:function(state)
	{
		this.reportFailures = state;
		return this;
	},

   /**
    * @protected
    * @param {Object} jqXHR
    * @param {String} textStatus
    * @param {String} errorThrown
    */
	Handle_SendFailure:function(jqXHR, textStatus, errorThrown)
	{
		this.Handle_Always();
		
		this.log('The request could not be sent.', 'event');
		
		// the returned error can be an object or a simple string,
        // we use the method to find out which and get the error message.
        var message = application.getAJAXError(errorThrown);
        var code = application.ERROR_UNHANDLED_AJAX_ERROR;
        var details = t('An unhandled error occurred.');
        
    	if(jqXHR.status === 404) {
    		details = t('HTTP Status code %1$s - %2$s', 404, t('The target resource does not exist.'));
    		code = application.ERROR_AJAX_RESOURCE_DOES_NOT_EXIST;
    	}
        
    	// ensure that the data object has the same keys
    	// as the regular error response, so it is consistent.
        var error = this.TriggerError(
			message,
			code,
			details,
			'',
			null
		);

    	this.Handle_Error(error);
	},
	
	HasEventHandler:function(eventName)
	{
		return typeof (this.eventHandlers[eventName]) !== 'undefined';
	},
	
   /**
    * @protected
    * @param {Application_AJAX_Error} error
    */
	Handle_Error:function(error)
	{
		var ajax = this;
		
		this.log('Handling the error:', 'event');
		if(this.logging) {console.log(error);}
		
        if (this.HasEventHandler('failure'))  
        {
            this.log('A failure handler has been set, calling that.', 'event');

            this.GetEventHandler('failure').call(this, error.GetMessage(), error);
        } 
        else 
        {
        	this.log('No failure handler set, showing the error dialog.', 'ui');
        	
        	var message = error.GetMessage();
        	var code = error.GetCode();
        	
        	// to avoid issues with variable type casting when dealing
        	// with numbers or else, we force the usage of strings.
        	var ifCode = 'E'+error.GetCode();
        	
        	if(typeof(this.errorConfig.ifCodes[ifCode]) != 'undefined') {
        		message = this.errorConfig.ifCodes[ifCode].message;
        		code = this.errorConfig.ifCodes[ifCode].code;
        	}
        	
        	application.createDialogAJAXError(
    			null, 
    			error.GetData(), 
        		message, 
        		code, 
        		function() {
    				ajax.Handle_Retry();
    			}
        	)
        	.Hidden(this.GetEventHandler('cancel'))
        	.Show();
        } 
	},
	
   /**
    * Handles retrying the request when it has failed.
    * If a retry handler has been set specifically, it
    * is used, otherwise the same request is simply re-sent.
    * 
    * @protected
    */
	Handle_Retry:function()
	{
		if(this.HasEventHandler('retry')) {
			this.GetEventHandler('retry').call(this);
			return;
		}
		
		this.completed = false;
		this.Send();
	},
	
	DisableLogging:function()
	{
		this.logging = false;
		return this;
	},
	
	EnableLogging:function()
	{
		this.logging = true;
		return this;
	},
	
	SetAsync:function(state)
	{
		this.options['async'] = state;
		return this;
	},
	
	TriggerError:function(message, code, details, trace, data)
	{
		return new Application_AJAX_Error(
			this,
			message,
			code,
			details,
			trace,
			data,
			this.reportFailures
		);
	},
	
	FilterData:function(data)
	{
		if(this.dataType === 'html')
		{
			return this.ExtractScripts(data);
		}
		
		return data;
	},
	
   /**
    * When retrieving HTML code via AJAX, we look for any <script>
    * and <link> tags in the result. These then get extracted, and
    * appended to the body of the page.
    *
    * This guarantees that the scripts cannot be tampered with once
    * they have been loaded, as could happen for example if the HTML
    * code is subsequently removed from the DOM.
    *
    * @param {String} html
    * @returns {String}  
    */
	ExtractScripts:function(html)
	{
		var tags = [];
		
		tags = this.ExtractTagsByName('link', 'rel="stylesheet', html, tags);
		tags = this.ExtractTagsByName('script', 'src="', html, tags);
		
		this.log('Found '+tags.length+' scripts in the response HTML.');
		this.log('Appending them to the body.');
		
		$.each(tags, function(idx, tag) 
		{
			html = html.replace(tag, '');
			$('body').append(tag);
		});
		
		return html;
	},

   /**
    * Finds all tags of the specified name in the subject html,
    * and if the attributes contain the needle, adds the full
    * tag name to the tags list.
    *
    * Finds both self-closing and content tags.
    *
    * @param {String} tagName The name of the HTML tag to search for.
    * @param {String} matchNeedle The needle string to search for in the attributes.
    * @param {String} html The HTML code to search in
    * @param {String[]} tags List of tags to store any matches in
    * @returns {String[]} The updated tags list. 
    */	
	ExtractTagsByName:function(tagName, matchNeedle, html, tags)
	{
		var scriptregex = new RegExp("<"+tagName+"([^>]+)>.*</"+tagName+">|<"+tagName+"([^>]+)\/?>", "ig");
		var match = scriptregex.exec(html);
		
		while (match != null) 
		{
			var tag = match[0]; 
			var attribs = '';
			
			if(typeof(match[1]) != 'undefined')
			{
				attribs = match[1];
			}
			else if(typeof(match[2]) != 'undefined')
			{
				attribs = match[2];
			}

			if(attribs.indexOf(matchNeedle))
			{
				tags.push(tag);
			}
			
			match = scriptregex.exec(html);
		}
		
		return tags;
	}
};

Application_AJAX = Class.extend(Application_AJAX);
