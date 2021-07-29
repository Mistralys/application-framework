/**
 * Class handling a confirmation dialog. Offers an easy to use
 * API to customize the dialog and tap into its events.
 * 
 * @package Application
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 */
var Dialog_Confirmation = 
{
	/**
	 * @type {Array.<string,function[]>}
	 */
	'handlers':null,

	/**
	 * @type {Array.<string,function[]>}
	 */
	'defaultHandlers':null,

	'dialog':null,
	'jsID':null,
	'content':null,
	'ready':null,
	'rendering':null,
	'dangerous':null,
	'shown':null,
	'withInput':false,
	'withComments':false,
	'commentsDescription':'',
	'commentsRequestVar':'',
	'preventClosing':null,
	'icon':null,
	'simulation':false,

	/**
	 * @type {string}
	 */
	'confirmLabel':'',

	/**
	 * @constructor
	 */
	init:function()
	{
		this.jsID = nextJSID();
		this.dialog = null;
		this.content = '';
		this.title = t('Please confirm');
		this.ready = false;
		this.rendering = false;
		this.dangerous = false;
		this.shown = false;
		this.withInput = false;
		this.withComments = false;
		this.commentsDescription = '';
		this.commentsRequestVar = 'confirm_comments';
		this.preventClosing = false;
		this.icon = null;
		this.simulation = false;
		this.confirmLabel = '';

		var dialog = this;
		
		this.defaultHandlers = {
			'ok':function() {dialog.Hide();},
			'cancel':function() {dialog.Hide();},
			'shown':function() {},
			'hidden':function() {}
		};

		this.handlers = {
			'ok':function() {dialog.Hide();},
			'cancel':function() {dialog.Hide();},
			'shown':function() {},
			'hidden':function() {}
		};
		
		application.registerDialog(this);
	},
	
   /**
    * Attaches a handler to the "ok" event, when the user clicks on the "Confirm" button.
    *
	* The handler function gets the following parameters:
	*
	* 1. The user-specified comments text, or an empty string if comments are disabled.
	*
    * @param {Function} handler
    * @returns {Dialog_Confirmation}
    */
	OK:function(handler)
	{
		return this.SetHandler('ok', handler);
	},
	
   /**
    * Attaches a handler to the "cancel" event, when the user clicks on the "Cancel" button.
    * 
    * @param {Function} handler
    * @returns {Dialog_Confirmation}
    */
	Cancel:function(handler)
	{
		return this.SetHandler('cancel', handler);
	},
	
   /**
    * Attaches a handler to the "shown" event, when the dialog is opened.
    * 
    * @param {Function} handler
    * @returns {Dialog_Confirmation}
    */
	Shown:function(handler)
	{
		return this.SetHandler('shown', handler);
	},
	
   /**
    * Attaches a handler to the "hidden" event, when the dialog is closed.
    *  
    * Note: When the user clicks the cancel button, both events are triggered
    * in turn: First the "cancel" event, then the "hidden" event as the dialog
    * is closed.
    *  
    * @param {Function} handler
    * @returns {Dialog_Confirmation}
    */
	Hidden:function(handler)
	{
		return this.SetHandler('hidden', handler);
	},
	
	SetHandler:function(event, handler)
	{
		if(typeof(handler)=='undefined' || handler == null) {
			return this;
		}
		
		this.handlers[event] = handler;
		return this;
	},
	
   /**
    * Opens/shows the dialog. If it has not been shown previously, its markup
    * is created, and then it is shown.
    *
    * @returns {Dialog_Confirmation}
    */
	Show:function()
	{
		// avoid double-clicks
		if(this.rendering) {
			return this;
		}
		
		if(!this.IsReady()) {
			this.Render();
			return this;
		}
		
		this.dialog.modal('show');
		this.shown = true;

		application.disallowAutoRefresh('dialogs');

		return this;
	},
	
   /**
    * Hides the dialog. Has no effect if it was not open.
    * 
    * @returns {Dialog_Confirmation}
    */
	Hide:function()
	{
		if(this.IsReady()) {
			this.dialog.modal('hide');
		}
		
		this.shown = false;

		application.allowAutoRefresh('dialogs');

		return this;
	},
	
   /**
    * Sets the content to display in the dialog. Can be used
    * to set the content prior to showing the dialog, as well
    * as afterwards.
    * 
    * @param {String} content
    * @returns {Dialog_Confirmation}
    */
	SetContent:function(content)
	{
		if(this.IsReady()) {
			this.element('content').html(content);
			return this;
		}
		
		this.content = content;		
		return this;
	},

	/**
	 * Sets the name of the GET request parameter that is used
	 * to add the comments text to the URL when using the
	 * `makeLinked()` method.
	 *
	 * @param {string} name
	 * @returns {Dialog_Confirmation}
	 * @constructor
	 */
	SetCommentsRequestVar:function(name)
	{
		this.commentsRequestVar = name;
		return this;
	},
	
   /**
    * Sets the title of the dialog. Can be used to set the title 
    * prior to showing the dialog, as well as afterwards.
    * 
    * @param {String} title
    * @returns {Dialog_Confirmation}
    */
	SetTitle:function(title)
	{
		this.title = title;

		if(this.IsReady()) {
			this.element('title').html(this.RenderTitle());
			return;
		}
		
		return this;
	},
	
	RenderTitle:function()
	{
		var title = this.title;
		
		if(this.icon != null) {
			title = this.icon.Render() + ' ' + title;
		}
		
		if(this.simulation) {
			title = '<div style="float:right;margin-right:20px;">' + application.renderLabelWarning(t('Simulation')) + '</div>' + title;
		}
		
		return title;
	},
	
   /**
    * Shows an alert message within the dialog, styled for informational messages.
    * 
    * @param {String} message
    * @returns {Dialog_Confirmation}
    */
	ShowAlertInfo:function(message)
	{
		return this.ShowAlert('info', message);
	},
	
   /**
    * Shows an alert message within the dialog, styled for error messages.
    * 
    * @param {String} message
    * @returns {Dialog_Confirmation}
    */
	ShowAlertError:function(message)
	{
		return this.ShowAlert('error', message);
	},
	
   /**
    * Shows an alert message within the dialog, styled for success messages.
    * 
    * @param {String} message
    * @returns {Dialog_Confirmation}
    */
	ShowAlertSuccess:function(message)
	{
		return this.ShowAlert('success', message);
	},

   /**
    * Shows an alert of the specified type.
    * 
    * @param {String} type A valid alert type, as supported by the application.renderAlert method, e.g. "success", "error".
    * @param {String} message
    * @returns {Dialog_Confirmation}
    */
	ShowAlert:function(type, message)
	{
		this.HideAlerts();
		this.element('messages').append(application.renderAlert(type, message, true));
		return this;
	},
	
   /**
    * Hides all alert messages currently shown in the dialog, if any.
    * 
    * @returns {Dialog_Confirmation}
    */
	HideAlerts:function()
	{
		$('#'+this.elementID('messages')+' .alert').hide('fast');
		return this;
	},
	
   /**
    * Checks whether the dialog is ready, i.e. wether rendering is
    * done and it can be modified further.
    * 
    * @returns {Boolean}
    */
	IsReady:function()
	{
		return this.ready;
	},
	
	PostRender:function()
	{
		this.ready = true;
		
		this.SetTitle(this.title);
		this.SetContent(this.RenderContent());
		
		if(this.dangerous) {
			this.MakeDangerous();
		}
		
		var dialog = this;
		UI.RefreshTimeout(function() {
			dialog.rendering = false;
			dialog.Show();
		});
		
		if(this.withInput) {
			this.element('inputform').submit(function(e) {
				e.preventDefault();
				dialog.Handle_Confirm();
			});
		}
	},
	
	RenderContent:function()
	{
		var content = this.content;
		
		content += this.RenderInput();
		content += this.RenderComments();
		
		return content;
	},

	RenderInput:function()
	{
		if(!this.withInput) {
			return '';
		}

		return ''+
		'<hr/>'+
		'<p>'+
			'<b class="text-warning">'+t('This is a critical operation, which will be logged.') + '</b> ' +
			t('Please make sure that you reviewed the consequences.') +
		'</p>'+
		'<p>'+
			t('When ready, type the text "%1$s" into the field below (exactly as shown, without the quotes).', '<code>'+t('I_AM_SURE')+'</code>')+
		'</p>'+
		'<form id="'+this.elementID('inputform')+'">'+
			'<input type="text" id="'+this.elementID('inputmsg')+'" class="input-xxlarge" placeholder="'+t('I_AM_SURE')+'" autocomplete="off"/>'+
				FormHelper.renderDummySubmit()+
		'</form>';
	},

	RenderComments:function()
	{
		if(!this.withComments) {
			return '';
		}

		var descrLine = '';
		if(this.commentsDescription !== '') {
			descrLine = '<p>'+this.commentsDescription+'</p>';
		}

		return ''+
		'<hr/>'+
		'<p>'+
			'<b>'+t('Comments')+' <span class="muted">('+t('Optional')+')</span></b>'+
		'</p>'+
		descrLine+
		'<form id="'+this.elementID('commentform')+'" class="input-xxlarge">'+
			'<textarea rows="3" id="'+this.elementID('fieldComments')+'" class="input-xxlarge"></textarea>'+
		'</form>';
	},

	/**
	 * @param {String} event
	 * @param {Array} args
	 */
	HandleEvent:function(event, args)
	{
		this.log('Handling event ['+event+']. Arguments:', 'event');
		this.logData(args);
		this.log(sprintf(
			'Found [%s] listeners, and [%s] default handlers.',
			this.handlers[event].length,
			this.defaultHandlers[event].length
		));

		this.handlers[event].apply(this, args);
		this.defaultHandlers[event].apply(this, args);
	},
	
	SetConfirmLabel:function(label)
	{
		this.confirmLabel = label;
		return this;
	},
	
   /**
    * Changes the layout of the dialog to signify that the action being
    * confirmed is a potentially dangerous operation and needs to be
    * reviewed carefully.
    * 
    * @return {Dialog_Confirmation}
    */
	MakeDangerous:function()
	{
		var label = this.confirmLabel;
		if(isEmpty(label)) {
			label = t('Confirm');
		}
		
		this.dangerous = true;
		
		if(this.dialog != null) {
			this.dialog.addClass('modal-danger');
			
			this.element('btn_confirm')
				.removeClass('btn-primary')
				.addClass('btn-danger')
				.html(UI.Icon().Warning()+' '+label);
		} 
		
		return this;
	},
	
   /**
    * Adds an input field that the user has to type a string into
    * to confirm the operation, to make sure they understand the
    * consequences.
    * 
    * @return {Dialog_Confirmation}
    */
	MakeWithInput:function()
	{
		this.withInput = true;
		return this;
	},

	MakeLinked:function(url, loaderText)
	{
		var dialog = this;

		this.MakeClickable(function(comments)
		{
			if(typeof url == "object") {
				url[dialog.commentsRequestVar] = comments;
			} else {
				url += '&'+dialog.commentsRequestVar+'=' + encodeURIComponent(comments);
			}

			application.redirect(url, loaderText);
		});

		return this;
	},

	/**
	 * Sets a callback as target for the confirm button.
	 *
	 * The callback gets the following parameters:
	 *
	 * 1. User-entered comments string, or empty string if not enabled.
	 *
	 * @param {Function} callback
	 * @returns {Dialog_Confirmation}
	 */
	MakeClickable:function(callback)
	{
		return this.OK(callback);
	},

	/**
	 * Adds a comments field to enter comments regarding the
	 * operation.
	 *
	 * @param {String} description
	 * @returns {Dialog_Confirmation}
	 */
	MakeWithComments:function(description)
	{
		this.withComments = true;
		this.commentsDescription = description;
		return this;
	},
	
	Lock:function()
	{
		if(!this.IsReady()) {
			return this;
		}
		
		this.element('btn_confirm').
			button('loading');
		
		this.element('btn_cancel').hide();
		
		// hide the small close button
		$('#'+this.elementID('dialog')+' .close').hide();
		
		return this;
	},
	
	Unlock:function()
	{
		if(!this.IsReady()) {
			return this;
		}
		
		this.element('btn_confirm').
			button('reset');
		
		this.element('btn_cancel').show();
		$('#'+this.elementID('dialog')+' .close').show();
		
		return this;
	},

	Render:function()
	{
		if(this.dialog != null) {
			return;
		}
		
		this.rendering = true;
		var dialog = this;
		var label = this.confirmLabel;
		if(isEmpty(label)) {
			label = t('Confirm');
		}
		
		var footer =
		UI.Button(t('Cancel'))
			.SetID(this.elementID('btn_cancel'))
			.Click(function() {
				dialog.HandleEvent('cancel');
			})+' '+
		UI.Button(label)
			.SetID(this.elementID('btn_confirm'))
			.MakeSuccess()
			.SetLoadingText(t('Please wait...'))
			.SetIcon(UI.Icon().OK())
			.Click(function() {
				dialog.Handle_Confirm();
			});

		this.dialog = DialogHelper.createDialog(
			'<span id="'+this.elementID('title')+'" class="confirm-title"></span>',
			'<div id="'+this.elementID('messages')+'" class="confirm-messages"></div>'+
			'<div id="'+this.elementID('content')+'" class="confirm-body"></div>',
			footer,
			{
				'id':this.elementID('dialog')
			}
		);
		
		var dialog = this;
		
		this.dialog.on('shown', function(){
			dialog.Handle_Shown();
		});
		
		this.dialog.on('hidden', function() {
			dialog.HandleEvent('hidden');
		});
		
		this.dialog.on('hide', function(e) {
			dialog.Handle_BeforeClose(e);
		});
		
		UI.RefreshTimeout(function() {
			dialog.PostRender();
		});
	},
	
	Handle_Confirm:function()
	{
		this.logEvent('The dialog has been confirmed.');

		if(this.withInput)
		{
			var val = this.element('inputmsg').val().trim();
			var match = t('I_AM_SURE');

			this.log(sprintf('Input message is [%s].', val));

			if(val !== match)
			{
				this.log(sprintf('Invalid message, does not match [%s].', match));

				this.ShowAlertError('<b>'+t('The text you entered does not match.') + '</b> ' + t('Make sure to enter it exactly as shown, it is case sensitive.'));
				this.element('inputmsg').focus();
				return;
			}

			this.log('Message is a match.');
		}

		var args = [];

		var comments = '';
		if(this.withComments)
		{
			comments = this.element('fieldComments').val().trim();
			this.log(sprintf('Comments are enabled. Given [%s].', comments));
		}

		args.push(comments);

		this.HandleEvent('ok', args);
	},
	
	Handle_Shown:function()
	{
		// fix for clicking an element with a tooltip to open the
		// dialog, which then prevents the tooltip from being closed
		UI.CloseAllTooltips();
		
		if(this.withInput) {
			this.element('inputmsg').focus();
		} else if(this.withComments) {
			this.element('fieldComments').focus();
		}
		
		this.HandleEvent('shown');
	},
	
	HideContent:function()
	{
		this.element('content').hide();
		return this;
	},
	
	ShowContent:function()
	{
		this.element('content').show();
		return this;
	},

	elementID:function(part)
	{
		if(typeof(part)=='undefined') {
			return this.jsID;
		}
		
		return this.jsID+'_'+part;
	},
	
	SetIcon:function(icon)
	{
		this.icon = icon;
		return this;
	},

	/**
	 * @param {string} part
	 * @returns {jQuery}
	 */
	element:function(part)
	{
		return $('#'+this.elementID(part));
	},

	logEvent:function(message)
	{
		this.log(message, 'event');
	},

	logData:function(data)
	{
		this.log(data, 'data');
	},

	log:function(message, category)
	{
		application.log(
			'Confirmation Dialog [#'+this.jsID+']',
			message,
			category
		);
	},
	
	IsShown:function()
	{
		return this.shown;
	},

   /**
    * Prevents the dialog from being closed by any of the 
    * buttons and/or shortcut keys.
    * 
    * @return {Dialog_Basic}
    */
	PreventClosing:function()
	{
		this.preventClosing = true;
		return this;
	},
	
   /**
    * Allows the dialog to be closed after having been 
    * set to prevent closing.
    * 
    * @return {Dialog_Basic}
    */
	AllowClosing:function()
	{
		this.preventClosing = false;
		return this;
	},
	
   /**
    * Called when the dialog is being closed. Prevents
    * the dialog from closing if closing is disabled.
    * 
    * @private
    * @param {Event} event
    * @see Dialog_Basic.PreventClosing
    */
	Handle_BeforeClose:function(event)
	{
		if(this.preventClosing) {
			event.preventDefault();
			event.stopPropagation();
		}
	},
	
	SetSimulation:function(simulate)
	{
		if(!User.isDeveloper()) {
			return this;
		}
		
		this.simulation = false;
		
		if(simulate === true) {
			this.simulation = true;
		} 
		
		return this;
	}
};

Dialog_Confirmation = Class.extend(Dialog_Confirmation);