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
	'handlers':null,
	'defaultHandlers':null,
	'dialog':null,
	'jsID':null,
	'content':null,
	'ready':null,
	'rendering':null,
	'dangerous':null,
	'shown':null,
	'withInput':null,
	'preventClosing':null,
	'icon':null,
	'simulationb':null,

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
		this.preventClosing = false;
		this.icon = null;
		this.simulation = false;
		
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
			return;
		}
		
		this.content = content;		
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
		
		if(this.withInput) {
			content += ''+
			'<hr/>'+
			'<p>'+
				'<b class="text-warning">'+t('This is a critical operation, which will be logged.') + '</b> ' + 
				t('Please make sure that you reviewed the consequences.') +
			'</p>'+
			'<p>'+
				t('When ready, type the text "%1$s" into the field below (exactly as shown, without the quotes).', '<code>'+t('I_AM_SURE')+'</code>')+
			'</p>'+
			'<p>'+
				'<form id="'+this.elementID('inputform')+'">'+
					'<input type="text" id="'+this.elementID('inputmsg')+'" class="input-xxlarge" placeholder="'+t('I_AM_SURE')+'" autocomplete="off"/>'+
					FormHelper.renderDummySubmit()+
				'</form>'+
			'</p>'
		}
		
		return content;
	},

	HandleEvent:function(event)
	{
		this.log('Handling event ['+event+'].', 'event');
		
		this.handlers[event].call(this);
		this.defaultHandlers[event].call(this);
	},
	
	'confirmLabel':null,
	
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
		if(this.withInput) {
			var val = this.element('inputmsg').val().trim();
			if(val != t('I_AM_SURE')) {
				this.ShowAlertError('<b>'+t('The text you entered does not match.') + '</b> ' + t('Make sure to enter it exactly as shown, it is case sensitive.'));
				this.element('inputmsg').focus();
				return;
			}
		}
		
		this.HandleEvent('ok');
	},
	
	Handle_Shown:function()
	{
		// fix for clicking an element with a tooltip to open the
		// dialog, which then prevents the tooltip from being closed
		UI.CloseAllTooltips();
		
		if(this.withInput) {
			this.element('inputmsg').focus();
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
	
	element:function(part)
	{
		var id = this.elementID(part);
		return $('#'+id);
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
		
		if(simulate == true) {
			this.simulation = true;
		} 
		
		return this;
	}
};

Dialog_Confirmation = Class.extend(Dialog_Confirmation);