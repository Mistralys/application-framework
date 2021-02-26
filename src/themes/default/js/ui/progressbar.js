/**
 * Progress bar class: can be used to insert progress bars into
 * HTML block elements.
 *
 * Usage:
 *
 * <pre>
 * // create a new progress bar. You can create as many
 * // as you need in the same page.
 * var progressBar = new ProgressBar();
 *
 * // Have the progress bar render its markup:
 * // provide an empty block element that it can
 * // inject its markup into. It will take the full
 * // width of the element.
 * progressBar.Render($('#progress-bar-container'));
 *
 * // Call Update to have the bar advance.
 * // Specify the total percent of the operation.
 * progressBar.Update(40);
 * progressBar.Update(70);
 *
 * // Instead of calling Update(100) for the last
 * // step, you can use this to specify a custom text
 * progressBar.Completed('All done!');
 * </pre>
 *
 * @package UI
 * @subpackage Bootstrap
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var ProgressBar =
{
	'id':null,
	'indicator':null,
	'bar':null,
	'indicatorText':null,
	'spinner':null,
	'container':null,
	'wrapper':null,
	'statusbar':null,
	'processingText':null,
	'percent':null,
	'cancellable':null,
	'cancel':null,
	'cancelHandler':null,
	'canceller':null,
	
   /**
    * @constructs
    */
	init:function()
	{
		this.id = 'pbar'+nextJSID();
		this.indicator = null;
		this.bar = null;
		this.indicatorText = null;
		this.spinner = null;
		this.container = null;
		this.wrapper = null;
		this.statusbar = null;
		this.processingText = t('Processing...');
		this.percent = 0;
		this.cancellable = false;
		this.cancel = false;
		this.canceller = null;
	},
	
   /**
    * Updates the progress bar to the specified percent.
    * 
    * @param {Number} percent The percentual advancement
    * @param {String} [processingText=null] Optional text to show instead of the standard "Processing..."
    * @return ProgressBar
    * @private
    */
	RefreshIndicator:function(percent, processingText)
	{
		if(isEmpty(processingText)) {
			processingText = this.processingText;
		}
		
		this.spinner.show();
		this.indicatorText.html(processingText+' '+percent);
		return this;
	},
	
   /**
    * Makes the progressbar cancellable: if the user cancels
    * the operation, the cancel handler will be called. The 
    * handler gets one parameter:
    * 
    * - The progressbar instance.
    * 
    * @param {Function} cancelHandler
    * @param {String} [cancelText=null]
    * @returns {ProgressBar}
    */
	MakeCancellable:function(cancelHandler, cancelText)
	{
		if(isEmpty(cancelText)) { cancelText = null; }
		
		this.cancellable = true;
		this.cancel = false;
		this.cancelHandler = cancelHandler;
		this.cancelText = cancelText;
		
		return this;
	},
	
   /**
    * Sets the default processing text to show next to the percent advancement value.
    * 
    * @param {String} text
    * @return ProgressBar
    */
	SetProcessingText:function(text)
	{
		this.processingText = text;
		return this;
	},
	
   /**
    * Renders the markup required for the progressbar and injects
    * it into the specified container element.
    *
    * @param {DOMElement} container
    * @return ProgressBar
    */
	Render:function(container)
	{
		if(isEmpty(container)) {
			application.log('Progress bar', 'Empty container given.', 'error');
			return;
		}
		
		var bar = this;
		
		var html = 
		'<div id="progress-wrapper-'+this.id+'" class="progress-wrapper">'+
			'<div id="progress-indicator-'+this.id+'" style="display:none;" class="progress-indicator">'+
				UI.Icon().Spinner().SetID('progress-spinner-'+this.id).MakeHidden()+' '+
				'<span id="progress-indicator-text-'+this.id+'"></span>'+
			'</div>';
			if(this.cancellable) {
				html += ''+
				'<div id="progress-canceller-'+this.id+'" class="progress-canceller">'+
					application.renderLabelWarning(t('Cancel'))
					.SetIcon(UI.Icon().Cancel())
					.Click(function() {
						bar.Cancel();
					})+
				'</div>';
			}
		
			html += ''+
			'<div id="progress-container-'+this.id+'" class="progress-container">'+
				'<div id="progress-bar-'+this.id+'" style="visibility:hidden;" class="progress-bar"></div>'+
			'</div>'+
			'<p id="progress-statusbar-'+this.id+'" class="progress-statusbar" style="display:none;"></p>'+
		'</div>';
		
		$(container).html(html);
		
		this.indicator = $('#progress-indicator-'+this.id);
		this.bar = $('#progress-bar-'+this.id);
		this.indicatorText = $('#progress-indicator-text-'+this.id);
		this.spinner = $('#progress-spinner-'+this.id);
		this.container = $('#progress-container-'+this.id);
		this.wrapper = $('#progress-wrapper-'+this.id);
		this.statusbar = $('#progress-statusbar-'+this.id);
		this.canceller = $('#progress-canceller-'+this.id);
		
		this.RefreshIndicator('0%');
		this.indicator.show();
		
		return this;
	},
	
	Cancel:function()
	{
		if(!this.cancellable) {
			return this;
		}
		
		this.cancel = true;
		this.canceller.hide();
		this.wrapper.addClass('cancelled');
		this.Error(t('Cancelled'), '<b>' + t('Note:') + '</b> ' + t('You cancelled the process.') + ' ' + this.cancelText);
		this.cancelHandler.call(undefined, this);
	},
	
   /**
    * Updates the progress bar to the specified percent completion.
    *
    * @param {Number} percent
    * @param {String} [processingText=null] Optional text to show instead of the standard "Processing..."
    * @param {String} [statusText=null] Optional text to display in the statusbar
    * @return ProgressBar
    */
	Update:function(percent, processingText, statusText)
	{
		if(this.cancel) {
			return;
		}
		
		if(isEmpty(percent) || percent < 0) { percent = 0; }
		if(percent > 100) { percent = 100; }
		
		percent = Math.round(percent);
		
		this.bar.css('visibility', 'visible');
		this.bar.width(percent+'%');
		this.RefreshIndicator(percent+'%', processingText);
		this.wrapper.removeClass('progress-error');
		this.percent = percent;
		
		this.ShowStatus(statusText);
		return this;
	},
	
	Error:function(errorMessage, errorDetails)
	{
		this.RefreshIndicator(this.percent+'%', errorMessage);
		this.wrapper.addClass('progress-error');
		this.spinner.hide();
		
		this.ShowStatus(errorDetails);
	},
	
	ShowStatus:function(text)
	{
		if(!isEmpty(text)) {
			this.statusbar.show();
			this.statusbar.html(text);
			return;
		} 

		this.statusbar.hide();
	},
	
   /**
    * Sets the progress bar as completed: Updates it to 100%, and
    * displays the specified text within the bar.
    *
    * @param {String} completedText
    * @return ProgessBar
    */
	Completed:function(completedText)
	{
		if(this.cancel) {
			return;
		}
		
		this.Update(100);
		this.spinner.hide();
		this.canceller.hide();
		
		if(!isEmpty(completedText)) {
			this.indicatorText.html(completedText);
		}
		
		return this;
	},
	
   /**
    * Renders the progressbar in a generic loader dialog.
    * @returns {ProgressBar}
    */
	RenderLoader:function()
	{
		var id = 'progressbar-container-' + this.id;
		application.showCustomLoader('<div id="' + id + '"></div>');
		this.Render($('#'+id));
		this.wrapper.addClass('nomargin');
		return this;
	},
	
	HideLoader:function()
	{
		application.hideLoader();
	},
	
   /**
    * Hides the progress bar by fading out its container element.
    */
	FadeOut:function()
	{
		var container = this.container;
		var indicator = this.indicator;
		container.fadeOut(
			400, 
			function() {
				indicator.hide();
			}
		);
	}
};

ProgressBar = Class.extend(ProgressBar);

/**
 * Helper class used to display a progress bar to process
 * several items in batches.  
 * 
 * Usage:
 * 
 * <pre>
 * var proc = ProgressBar.CreateBatchProcessor('AjaxMethodName')
 * .SetBatchSize(1) // 1 is default
 * .SetTitle(t('Some title'))
 * .SetIcon(UI.Icon().OK())
 * .SetPayloadKey('param_name', 'value')
 * .Success(function() {
 *     // do something
 * });
 * 
 * proc.AddItem({data});
 * proc.AddItem({data});
 * 
 * proc.Start();
 * </pre>
 * 
 * The AJAX method gets parameters with the following structure:
 * 
 * <pre>
 * {
 *     'custom_param_name':'value', // any custom parameters set with SetPayloadKey()
 *     'items':[
 *         {
 *             'item_property_1':'value', // item data as specified with AddItem()
 *             ...
 *         },
 *         ...
 *     ]
 * }
 * </pre>
 * 
 * @package UI
 * @subpackage Bootstrap
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var ProgressBar_BatchProcessor = 
{
	'ERROR_CANNOT_PROCESS_ITEM':90001,
		
	'jsID':null,
	'method':null,
	'items':null,
	'dialog':null,
	'processing':null,
	'position':null,
	'batchSize':null, // how many items to process per batch
	'title':null,
	'abstractText':null,
	'eventHandlers':null,
	'payload':null,
	'icon':null,
	'simulate':null,
	'ajaxURL':null,
	'layout':null,
	'itemsPayloadKey':null,
	'batch':null,
	'cancellable':null,
	'cancelText':null,
	
	init:function(method)
	{
		this.jsID = nextJSID();
		this.method = method;
		this.items = [];
		this.dialog = null;
		this.processing = false;
		this.icon = null;
		this.position = 0;
		this.batchSize = 1;
		this.eventHandlers = {};
		this.title = t('Batch process items');
		this.abstractText = t('This processes the selected items in batches.');
		this.payload = {};
		this.simulate = false;
		this.layout = 'dialog';
		this.itemsPayloadKey = 'items';
		this.batch = 0;
		this.cancellable = false;
		this.cancelText = null;
	},
	
	SetTitle:function(title)
	{
		this.title = title;
		return this;
	},
	
	SetAbstract:function(abstractText)
	{
		this.abstractText = abstractText;
		return this;
	},
	
	SetAJAXURL:function(url)
	{
		this.ajaxURL = url;
		this.method = null;
		return this;
	},
	
	SetIcon:function(icon)
	{
		this.icon = icon;
		return this;
	},
	
	SetBatchSize:function(itemsPerBatch)
	{
		this.batchSize = itemsPerBatch;
		return this;
	},
	
   /**
    * Sets the name of the key in which the items for each
    * batch are transmitted to the target AJAX script. This
    * defaults to <code>items</code>.
    * 
    * @param {String} keyName
    * @return {ProgressBar_BatchProcessor}
    */
	SetItemsPayloadKey:function(keyName)
	{
		this.itemsPayloadKey = keyName;
		return this;
	},
	
   /**
    * Uses a regular progress bar container instead of the
    * default progress bar dialog.
    * 
    * @param {jQuery} containerEl
    * @returns {ProgressBar_BatchProcessor}
    */
	MakeProgressBar:function(containerEl)
	{
		this.container = containerEl;
		this.layout = 'regular';
		return this;
	},
	
	Start:function()
	{
		if(this.processing) {
			return;
		}
		
		if(this.items.length == 0) {
			this.log('No items have been added to the batch.', 'error');
			return;
		}
		
		this.processing = true;
		
		var proc = this;
		
		if(this.IsDialog()) 
		{
			this.dialog = new Dialog_ProgressBar()
			.SetTitle(this.title)
			.SetAbstract(this.abstractText)
			.SetIcon(this.icon)
			.Shown(function() {
				proc.NextBatch();
			});
			
			if(this.cancellable) {
				this.dialog.MakeCancellable(
					function() {
						proc.Cancel();
					}, 
					this.cancelText
				);
			}
			
			this.dialog.Show();
		} 
		else 
		{
			this.progressBar = new ProgressBar();
			
			if(this.cancellable) {
				this.progressBar.MakeCancellable(
					function() {
						proc.Cancel();
					},
					this.cancelText
				);
			}
			
			this.progressBar.Render(this.container);
			this.NextBatch();
		}
	},
	
	Cancel:function()
	{
		if(!this.cancellable) {
			return;
		}
		
		this.cancel = true;
	},
	
	MakeCancellable:function(cancelText)
	{
		this.cancellable = true;
		this.cancelText = cancelText;
		return this;
	},
	
	IsDialog:function()
	{
		if(this.layout == 'dialog') {
			return true;
		}
		
		return false;
	},
		
   /**
    * Adds an item to be batch processed. Should be an associative array
    * with key => value pairs of data to send to the AJAX script that
    * will process the items in a batch.
    * 
    * @param {Object} data
    * @returns {ProgressBar_BatchProcessor}
    */
	AddItem:function(data)
	{
		this.items.push(data);
		return this;
	},
	
	AddItems:function(items)
	{
		var proc = this;
		$.each(items, function(idx, item) {
			proc.AddItem(item);
		});
		
		return this;
	},
	
   /**
    * Sets a parameter which will be sent with the request for
    * each batch. Use this to specify global data that needs to
    * be present for all items.
    * 
    * @param {String} name
    * @param {String} value
    * @returns {ProgressBar_BatchProcessor}
    */
	SetPayloadKey:function(name, value)
	{
		this.payload[name] = value;
		return this;
	},
	
   /**
    * Sets all payload keys contained in the specified associative array.
    * 
    * @param {Object} data
    * @returns {ProgressBar_BatchProcessor}
    */
	SetPayload:function(data)
	{
		var proc = this;
		$.each(data, function(key, value) {
			proc.SetPayloadKey(key, value);
		});
		
		return this;
	},
	
   /**
    * @protected
    */
	NextBatch:function()
	{
		if(this.cancel) {
			this.Handle_Cancel();
			return;
		}
		
		this.batch++;
		
		var payload = this.payload;
		payload['is_last_batch'] = 'no';
		payload[this.itemsPayloadKey] = [];
		
		var total = this.items.length;
		if(this.position+1 > total) {
			this.Handle_CheckComplete();
			return;
		}
		
		// is this the last batch we're processing?
		if(this.position + this.batchSize + 1 > total) {
			payload['is_last_batch'] = 'yes';
		}
		
		for(var i=this.position; i<this.position + this.batchSize; i++) {
			if((i+1) > total) {
				break;
			}
			
			var item = this.items[i];
			payload[this.itemsPayloadKey].push(item);
		}
		
		var proc = this;
		
		application.createAJAX(this.method)
		.SetCustomURL(this.ajaxURL)
		.Simulate(this.simulate)
		.SetPayload(payload)
		.Error(t('Could not process an item in the batch.'), this.ERROR_CANNOT_PROCESS_ITEM)
		.Success(function(data) {
			proc.Handle_BatchSuccess(data);
		})
		.Cancel(function() {
			proc.Handle_Cancel();
		})
		.Retry(function() {
			proc.Handle_Retry();
		})
		.Send();
	},
	
	Handle_Cancel:function()
	{
		if(this.IsDialog()) 
		{
			this.dialog.AllowClosing();
		} 
	},
	
	Handle_Retry:function()
	{
		if(this.IsDialog()) {
			this.dialog.Show();
		}
		
		this.NextBatch();
	},
	
   /**
    * @param {Object} data
    * @protected
    */
	Handle_BatchSuccess:function(data)
	{
		var processed = this.position;
		this.position = this.position + this.batchSize;
		var percent = this.position * 100 / this.items.length;
		
		if(this.IsDialog()) {
			this.dialog.ProgressUpdate(percent);
		} else {
			this.progressBar.Update(percent, this.GetProcessingText(), this.GetStatusText(this.batch, percent, processed));
		}
		
		this.NextBatch();
	},
	
	GetTotalItems:function()
	{
		return this.items.length;
	},
	
	GetTotalBatches:function()
	{
		return Math.ceil(this.GetTotalItems() / this.batchSize);
	},
	
	GetProcessingText:function()
	{
		return this.processingText;
	},
	
   /**
    * Adds an event handler for when the status text in the 
    * progress bar is updated. The handler gets the following
    * parameters:
    * 
    * - The batch processor instance
    * - The current batch number
    * - The current percent
    * - The amount of items processed so far
    * 
    * @param {Function} handler
    * @returns {ProgressBar_BatchProcessor}
    */
	StatusTextUpdated:function(handler)
	{
		this.statusTextHandler = handler;
		return this;
	},
	
	GetStatusText:function(position, percent, processed)
	{
		if(this.statusTextHandler != null) {
			return this.statusTextHandler.call(undefined, this, position, percent, processed);
		}
		
		return null;
	},
	
   /**
    * @protected
    */
	Handle_CheckComplete:function()
	{
		if(this.IsDialog()) 
		{
			this.dialog.Close();
			this.dialog.ProgressCompleted();
		} 
		else 
		{
			this.progressBar.Completed();
		}
		
		this.TriggerEvent('Success');
	},
	
   /**
    * Adds an event handler which is called when all batches 
    * have been processed successfully.
    * 
    * @return {ProgressBar_BatchProcessor}
    */
	Success:function(handler)
	{
		return this.AddEventHandler('Success', handler);
	},
	
   /**
    * Adds an event handler for the specified event. All event
    * handlers get the renderable object instance as first argument,
    * additional parameters depend on the event.
    * 
    * @param {String} eventName
    * @param {Function} handler
    * @returns {ProgressBar_BatchProcessor}
    * @protected
    */
	AddEventHandler:function(eventName, handler)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			this.eventHandlers[eventName] = [];
		}
		
		this.eventHandlers[eventName].push(handler);
		return this;
	},
	
   /**
    * Triggers the specified event. 
    * 
    * The handler function gets the renderable instance
    * as first parameter. <code>this</code> is undefined.
    * Any additional arguments are passed on to the event 
    * handling functions.
    *  
    * @param {String} eventName
    * @protected
    */
	TriggerEvent:function(eventName)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined' || this.eventHandlers[eventName].length == 0) {
			return;
		}
		
		var args = [];
		args.push(this);
		
		for(var i=1; i < arguments.length; i++) {
			args.push(arguments[i]);
		}
		
		$.each(this.eventHandlers[eventName], function(idx, handler) {
			handler.apply(undefined, args);
		});
	},

   /**
    * Checks if at least one event handler has been added for the specified event.
    * @param {String} eventName
    * @returns {Boolean}
    */
	HasEventHandler:function(eventName)
	{
		if(typeof(this.eventHandlers[eventName]) != 'undefined' && this.eventHandlers[eventName].length > 0 ) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Whether to simulate the requests.
    * @param {Boolean} simulate
    * @returns {ProgressBar_BatchProcessor}
    */
	SetSimulation:function(simulate)
	{
		this.simulate = false;
		
		if(simulate == true) {
			this.simulate = true;
		}
		
		return this;
	},
	
	log:function(message, category)
	{
		application.log(
			'ProgressBar batch processor ['+this.jsID+']',
			message,
			category
		);
	}
};

ProgressBar_BatchProcessor = Class.extend(ProgressBar_BatchProcessor);