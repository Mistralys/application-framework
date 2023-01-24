/**
 * Handles simple dialogs: base class for implementing custom
 * dialogs based on this base skeleton. Offers methods and events
 * to make it easy to build simple dialogs.
 * 
 * @package Application
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 */
var Dialog_Basic = 
{
	'rendering':null,
	'rendered':null,
	'dialog':null,
	'footerDisabled':null,
	'dangerous':null,
	'large':null,
	'simulate':null,
	'abstractText':null,
	'icon':null,
	'buttons':null,
	'isShown':null,
	'eventHandlers':null,
	'preventClosing':null,
	'data':null,
	'classes':null,
	
	'ERROR_METHOD_NOT_IMPLEMENTED':3684233001,
	
	init:function()
	{
		this.rendered = false;
		this.rendering = false;
		this.simulate = false;
		this.dialog = null;
		this.footerDisabled = false;
		this.jsID = nextJSID();
		this.dangerous = false;
		this.large = false;
		this.abstractText = '';
		this.icon = '';
		this.classes = [];
		this.data = {};
		this.buttons = {};
		this.buttons[this.BUTTON_POSITION_LEFT] = [];
		this.buttons[this.BUTTON_POSITION_RIGHT] = [];
		this.isShown = false;
		this.preventClosing = false;
		this.eventHandlers = {
			'shown':[],
			'closed':[]
		};
		
		application.registerDialog(this);
		
		this._init();
	},
	
   /**
    * @protected
    */
	_init:function()
	{
		// allows dialog classes to run initialization routines
		// without having to overload the init method.
	},
	
   /**
    * Shows the modal dialog, and renders the content as necessary.
    * 
    * @public
    * @return {Dialog_Basic}
    */
	Show:function()
	{
		if(!this.rendered) {
			var dialog = this;
			this.Render();
			UI.RefreshTimeout(function() {
				dialog.PostRender();
				dialog.Show();
			});
			return this;
		}
		
		this.dialog.modal('show');
		this.HideAlerts();
		this.Handle_Shown();

		application.disallowAutoRefresh('dialogs');

		return this;
	},
	
   /**
    * Hides the modal. Has no effect if it is not shown.
    * 
    * @public
    * @return {Dialog_Basic}
    */
	Hide:function()
	{
		this.isShown = false;
		
		if(this.dialog != null) {
			this.dialog.modal('hide');
		}

		application.allowAutoRefresh('dialogs');
		
		return this;
	},
	
   /**
    * Renders the dialog's markup.
    * @public
    */
	Render:function()
	{
		if(this.rendering) {
			return;
		}
		
		this.rendering = true;
		
		var footer = this.RenderFooter();
		if(this.footerDisabled==true) {
			footer = null;
		}
		
		this.dialog = DialogHelper.createDialog(
			this.RenderTitle(), 
			this.RenderBody(), 
			footer,
			{
				'id':this.jsID
			}
		);
		
		if(this.dangerous) {
			this.AddClass('modal-danger');
		}
		
		if(this.large) {
			this.AddClass('modal-large');
		}

		var dialog = this.dialog;

		$.each(this.classes, function(idx, className) {
			dialog.addClass(className);
		});
	},
	
   /**
    * @private
    * @returns {String}
    */
	RenderTitle:function()
	{
		var html = ''+
		'<span id="'+this.elementID('simulate')+'" class="dialog-simulation-badge">' + this.RenderSimulationBadge() + '</span>'+
		'<span id="' + this.elementID('icon') + '">' +
			this.icon + 
		'</span> ' +
		'<span id="' + this.elementID('title') + '">' + 
			this.GetTitle() +
		'</span>';
		
		return html;
	},
	
   /**
    * Sets the title of the dialog. Can be used to set the title 
    * prior to showing the dialog, as well as afterwards.
    * 
    * @param {String} title
    * @returns {Dialog_Basic}
    */
	SetTitle:function(title)
	{
		if(this.IsReady()) {
			this.element('title').html(title);
			return;
		}
		
		this.title = title;
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
		return this.rendered;
	},
	
   /**
    * Turns off the footer of the dialog.
    * 
    * @public
    * @return {Dialog_Basic}
    */
	DisableFooter:function()
	{
		this.footerDisabled = true;
		return this;
	},
	
   /**
    * Retrieves the title of the dialog. If the extending class
    * does not implement the {@link _GetTitle()} method, the 
    * application's name is used instead.
    * 
    * @return {String}
    * @see _GetTitle
    * @private
    */
	GetTitle:function()
	{
		if(this.title != null) {
			return this.title;
		}
		
		return this._GetTitle();
	},
	
   /**
    * @return {String}
    * @protected
    */
	_GetTitle:function()
	{
		return application.appName;
	},
	
   /**
    * Renders the abstract for the dialog. This is optional,
    * and is only used if the extending class implements the
    * {@link _RenderAbstract} method and returns a valid string.
    * 
    * @return {String}
    * @see _RenderAbstract
    * @private
    */
	RenderAbstract:function()
	{
		var html = this._RenderAbstract();
		if(typeof(html) != 'undefined' && typeof(html.length) != 'undefined' && html.length) {
			html = DialogHelper.renderAbstract(html);
		} else {
			html = '';
			if(this.abstractText != '') {
				html = DialogHelper.renderAbstract(this.abstractText);
			}
		}
		
		return '<div id="' + this.elementID('abstract') + '">' + html + '</div>';
	},
	
   /**
    * @protected
    */
	_RenderAbstract:function()
	{
		// OPTIONAL. Extend this in your subclass
	},
	
   /**
    * Sets the abstract text to display. Can be used after the
    * dialog has been rendered to replace the existing abstract,
    * add add one if there was none.
    * 
    * @param {String} text
    * @return {Dialog_Basic}
    */
	SetAbstract:function(text)
	{
		if(this.IsReady()) {
			if(isEmpty(text)) {
				this.element('abstract').hide();
			} else {
				this.element('abstract')
				.show()
				.html(DialogHelper.renderAbstract(text));
			}
		}
		
		this.abstractText = text;
		return this;
	},
	
	SetIcon:function(icon)
	{
		if(this.IsReady()) {
			this.element('icon').html(icon.Render());
		}
		
		this.icon = icon;
		return this;
	},
	
	GetIcon:function()
	{
		return this.icon;
	},
	
   /**
    * Renders the body markup for the dialog. The extending class
    * needs to implement the {@link _RenderBody} method to build
    * the markup.
    * 
    * @return {String}
    * @see _RenderBody
    * @private
    */
	RenderBody:function()
	{
		var body = this._RenderBody();
		if(typeof(body) == 'undefined' || typeof(body.length) == 'undefined' || !body.length) {
			body = '';
		}
		
		var html = ''+
		this.RenderAbstract()+
		'<div id="'+this.elementID('messages')+'"></div>'+
		'<div id="'+this.elementID('body')+'">'+body+'</div>';
		
		return html;
	},
	
   /**
    * @protected
    */
	_RenderBody:function()
	{
		
	},
	
   /**
    * Renders the dialog's footer, i.e. the buttons to show in the
    * footer bar. Returns an HTML markup string. If the class extending
    * this class does not implement the _RenderFooter method, a standard
    * "Close" button will be shown.
    * 
    * @private
    * @return string
    */
	RenderFooter:function()
	{
		var html = '' +
		'<div id="'+this.elementID('footer-left')+'" class="modal-footer-left">' + 
			this.RenderFooterLeft() + 
		'</div>';
		
		var custom = this._RenderFooter();
		if(!isEmpty(custom)) {
			html += custom;
			return html;
		}
		
		if(this.buttons[this.BUTTON_POSITION_RIGHT].length == 0) { 
			this.AddButtonClose();
		}
		
		html += this.RenderButtons(this.BUTTON_POSITION_RIGHT);
		
		return html;
	},
	
   /**
    * Renders the markup for all buttons available in the specified position.
    * 
    * @private
    * @param {String} position
    * @returns {String}
    */
	RenderButtons:function(position)
	{
		if(typeof(this.buttons[position]) == 'undefined' || this.buttons[position].length < 1) {
			this.log('No buttons to render on the ['+position+'] side.');
			return '';
		}
		
		this.log(this.buttons[position].length + ' buttons to render on the ['+position+'] side.');
		
		var html = '';
		$.each(this.buttons[position], function(idx, button){
			html += button.Render() + ' ';
		});
		
		return html;
	},
	
   /**
    * Renders the left footer content: either the dialog returns its
    * own static html code via the _RenderFooterLeft() method, or any
    * buttons that have been added to the left side are used.
    * 
    * @private
    * @returns {String}
    */
	RenderFooterLeft:function()
	{
		var custom = this._RenderFooterLeft();
		if(!isEmpty(custom)) {
			return custom;
		}
		
		return this.RenderButtons(this.BUTTON_POSITION_LEFT);
	},
	
   /**
    * @protected 
    * @returns {String}
    */
	_RenderFooterLeft:function()
	{
		return '';
	},
	
   /**
    * Creates and adds a button styled as a primary button.
    * Inherits the dialog's icon if it has been set.
    * 
    * @param {String} label
    * @param {Function} clickHandler The click handler to use for the button. Has the dialog instance as <code>this</code>.
    * @param {String} position
    * @return {UI_Button}
    */
	AddButtonPrimary:function(label, clickHandler, position)
	{
		if(isEmpty(label)) {
			label = t('OK');
		}
		
		var button = UI.Button(label)
		.MakePrimary();
		
		if(!isEmpty(this.icon)) {
			let type = this.icon.GetType();
			if(type !== null) {
				button.SetIcon(UI.Icon().SetType(type, this.icon.GetPrefix()));
			}
		}
		
		var dialog = this;
		if(!isEmpty(clickHandler)) {
			button.Click(function() {
				clickHandler.call(dialog);
			});
		}
		
		return this.AddButton(button, '__primary', position);
	},
	
   /**
    * If present, will return the button instance for the 
    * dialog's primary button (if added via the 
    * {@link AddButtonPrimary} method).
    * 
    * @return {UI_Button}|NULL
    */
	GetPrimaryButton:function()
	{
		return this.GetButton('__primary');
	},
	
   /**
    * Creates and adds a regular "close" button to the dialog, and returns the button instance.
    * @param {String} label
    * @param {String} name 
    * @return {Dialog_Basic}
    */
	AddButtonClose:function(label, name)
	{
		if(isEmpty(label)) {
			label = t('Close');
		}
		
		if(isEmpty(name)) {
			name = 'close';
		}
		
		var dialog = this;
		
		return this.AddButtonRight(
			UI.Button(label)
			.SetIcon(UI.Icon().Close())
			.Click(function() {
				dialog.Hide();
			}), 
			name
		);
	},
	
   /**
    * Creates and adds a regular "cancel" button to the dialog, and returns the button instance.
    * @param {String} [name] Optional name which can be used to retrieve the button later 
    * @returns {Dialog_Basic}
    */
	AddButtonCancel:function(name)
	{
		if(isEmpty(name)) {
			name = 'cancel';
		}
		
		return this.AddButtonClose(t('Cancel'), name);
	},
	
	'BUTTON_POSITION_LEFT':'left',
	'BUTTON_POSITION_RIGHT':'right',
	
   /**
    * Adds a new button to the footer toolbar.
    * @param {UI_Button} button
    * @param {string} name Optional name which can be used to retrieve the button later 
    * @param {String} position On which side the button should be shown.
    * @returns {Dialog_Basic}
    */
	AddButton:function(button, name, position)
	{
		if(position != this.BUTTON_POSITION_LEFT) {
			position = this.BUTTON_POSITION_RIGHT;
		}
		
		this.log('Adding button ['+button.GetLabel()+'] on the ['+position+'] side.', 'ui');
		
		if(!isEmpty(name)) {
			button.SetID(this.elementID('btn-'+name));
		}
		
		this.buttons[position].push(button);
		return this;
	},
	
	AddButtonRight:function(button, name)
	{
		return this.AddButton(button, name, this.BUTTON_POSITION_RIGHT);
	},
	
   /**
    * Retrieves the ID of the dialog, which is unique for 
    * each dialog and used in all element IDs to keep them
    * separated from all other elements.
    * 
    * @returns {String}
    */
	GetJSID:function()
	{
		return this.jsID;
	},
	
   /**
    * Retrieves a named dialog button instance (if you specified
    * the name parameter when adding the button).
    * 
    * @param {String} name
    * @returns {UI_Button}|NULL
    */
	GetButton:function(name)
	{
		return UI.GetButton(this.elementID('btn-'+name));
	},
	
   /**
    * Adds a button on the left hand side of the dialog's 
    * bottom toolbar, instead of the default right side.
    * 
    * @param {UI_Button} button
    * @param {String} name Optional name which can be used to retrieve the button later 
    * @returns {Dialog_Basic}
    */
	AddButtonLeft:function(button, name)
	{
		return this.AddButton(button, name, this.BUTTON_POSITION_LEFT);
	},
	
   /**
    * @protected
    */
	_RenderFooter:function()
	{
		// extend this in your subclass
		// if you don't, a standard "Close" button will be shown
	},
	
   /**
    * Executes any routines like attaching event handlers once
    * the markup has been rendered and injected into the DOM.
    * 
    * @private
    */
	PostRender:function()
	{
		this._PostRender();
		this.rendered = true;
		this.rendering = false;
		
		var dialog = this;
		
		this.dialog.on('hide', function(e) {
			dialog.Handle_BeforeClose(e);
		});
		
		this.dialog.on('hidden', function(e) {
			dialog.Handle_Closed();
		});
		
		this._Start();
	},
	
   /**
    * This is called when the dialog has finished rendering and
    * post-rendering. Use this to add any routines the dialog needs
    * to do once everything is ready.
    * 
    * @abstract
    * @protected
    */
	_Start:function()
	{
		// OPTIONAL - extend this in your subclass
	},
	
   /**
    * Called when the dialog's markup has been rendered
    * and is accessible in the DOM.
    * 
    * @abstract
    * @protected
    */
	_PostRender:function()
	{
		// OPTIONAL - extend this in your subclass
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
	
   /**
    * @private
    */
	Handle_Closed:function()
	{
		this._Handle_Closed();
		
		if(this.eventHandlers.closed.length > 0) {
			for(var i=0; i<this.eventHandlers.closed.length; i++) {
				this.eventHandlers.closed[i].call(undefined, this);
			}
		}
	},
	
   /**
    * Called when the dialog has been closed.
    * 
    * @abstract
    * @protected
    */
	_Handle_Closed:function()
	{
		
	},
	
   /**
    * Shows an alert message within the dialog, styled for informational messages.
    * 
    * @param {String} message
    * @returns {Dialog_Basic}
    * @public
    */
	ShowAlertInfo:function(message)
	{
		return this.ShowAlert('info', message);
	},
	
   /**
    * Shows an alert message within the dialog, styled for error messages.
    * 
    * @param {String} message
    * @returns {Dialog_Basic}
    * @public
    */
	ShowAlertError:function(message)
	{
		return this.ShowAlert('error', message);
	},
	
   /**
    * Shows an alert message within the dialog, styled for success messages.
    * 
    * @param {String} message
    * @returns {Dialog_Basic}
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
    * @returns {Dialog_Basic}
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
    * @returns {Dialog_Basic}
    */
	HideAlerts:function()
	{
		$('#'+this.elementID('messages')+' .alert').hide('fast');
		return this;
	},
	
   /**
    * @private
    */
	Handle_Shown:function()
	{
		this.isShown = true;

		// fix for clicking an element with a tooltip to open the
		// dialog, which then prevents the tooltip from being closed
		UI.CloseAllTooltips();

		this._Handle_Shown();
		
		if(this.eventHandlers.shown.length > 0) {
			for(var i=0; i<this.eventHandlers.shown.length; i++) {
				this.eventHandlers.shown[i].call(undefined, this);
			}
		}
	},
	
   /**
    * @protected
    */
	_Handle_Shown:function()
	{
		// implement this in your dialog class as needed
	},
	
   /**
    * Changes the layout of the dialog to signify that the action being
    * confirmed is a potentially dangerous operation and needs to be
    * reviewed carefully.
    * 
    * @return {Dialog_Basic}
    */
	MakeDangerous:function()
	{
		this.dangerous = true;
		
		if(this.IsReady()) {
			this.dialog.addClass('modal-danger');
		} 
		
		return this;
	},

	AddClass:function(className)
	{
		if(!in_array(className, this.classes, true)) {
			this.classes.push(className);
		}

		if(this.IsReady()) {
			this.dialog.AddClass(className);
		}

		return this;
	},
	
   /**
    * Changes the layout of the dialog to be larger to allow
    * more content inside.
    * 
    * @returns {Dialog_Basic}
    */
	MakeLarge:function()
	{
		this.large = true;
		
		if(this.IsReady()) {
			this.dialog.addClass('modal-large');
		}
		
		return this;
	},
	
   /**
    * Replaces the current body of the dialog with the specified html markup.
    * @param html
    * @return {Dialog_Basic}
    */
	ChangeBody:function(html)
	{
		if(!this.IsReady()) {
			return this;
		}
		
		this.element('body').html(html);
		
		var dialog = this;
		UI.RefreshTimeout(function() {
			dialog.PostChangeBody();
		});
		
		return this;
	},
	
   /**
    * @private
    */
	PostChangeBody:function()
	{
		this._PostChangeBody();
	},
	
   /**
    * @abstract
    * @protected
    */
	_PostChangeBody:function()
	{
		
	},
	
   /**
    * @protected
    */
	element:function(name)
	{
		return $('#'+this.elementID(name));
	},

   /**
    * @protected 
    */
	elementID:function(name)
	{
		var id = this.jsID;
		if(typeof(name)!='undefined') {
			id += '_'+name;
		}

		return id;
	},
	
   /**
    * @protected
    */
	log:function(message, category)
	{
		application.log('Dialog [' + this.jsID + ']', message, category);
	},
	
   /**
    * Adds an event handler to the "shown" event of the dialog.
    * This gets the dialog instance as sole parameter.
    * 
    * @param {Function} handler
    * @return {Dialog_Basic}
    */
	Shown:function(handler)
	{
		return this.SetEventHandler('shown', handler);
	},
	
   /**
    * Sets an event handling function for the specified event.
    * 
    * @protected
    * @param {String} eventName
    * @param {Function} handler
    * @returns {Dialog_Basic}
    */
	SetEventHandler:function(eventName, handler)
	{
		if(!isEmpty(handler)) {
			this.eventHandlers[eventName].push(handler);
		}
		
		return this;
	},
	
   /**
    * Adds an event handler that gets called when the dialog
    * is hidden/closed.
    * 
    * @param {Function} handler
    * @returns {Dialog_Basic}
    */
	Hidden:function(handler)
	{
		return this.SetEventHandler('closed', handler);
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
    * Destroys the dialog: removes it from the DOM.
    * 
    * Warning: using the dialog instance after this is
    * still possible, but will cause errors.
    * 
    * @return {Dialog_Basic}
    */
	Destroy:function()
	{
		$('#'+this.jsID).remove();
		return this;
	},
	
   /**
    * Sets whether the simulation mode should be activated.
    * Note that this does not do anything on its own: the dialog
    * has to use this functionality for it to have any effect.
    * 
    * @param {Boolean} simulate
    * @returns {Dialog_Basic}
    * @see Dialog_Basic.IsSimulationActive
    */
	SetSimulation:function(simulate)
	{
		if(this.simulate == simulate) {
			return;
		}
		
		if(simulate==true) {
			this.simulate = true;
		} else {
			this.simulate = false;
		}

		this.log('Set simulation mode to ['+bool2string(this.simulate)+']');
		
		if(this.IsReady()) {
			this.element('simulate').html(this.RenderSimulationBadge());
		}
		
		return this;
	},
	
	RenderSimulationBadge:function()
	{
		if(this.IsSimulationActive()) {
			return application.renderBadgeWarning(t('Simulation'), t('Simulation mode is enabled.'));
		}
		
		return '';
	},
	
   /**
    * Checks whether the dialog's simulation mode is active.
    * @returns {Boolean}
    * @see Dialog_Basic.SetSimulation
    */
	IsSimulationActive:function()
	{
		return this.simulate;
	},
	
   /**
    * Set a custom data value: this simply allows to store
    * custom data in the dialog. It has no other functionality.
    * 
    * @param {String} key
    * @param {Mixed} value
    * @return {Dialog_Basic}
    */
	SetData:function(key, value)
	{
		this.data[key] = value;
		return this;
	},
	
   /**
    * Retrieves the value of a previously set data value, or
    * the default value if it does not exist.
    * @param {String} key
    * @param {Mixed} [defaultValue=null]
    * @returns {Mixed}
    */
	GetData:function(key, defaultValue)
	{
		if(typeof(this.data[key]) != 'undefined') {
			return this.data[key];
		}

		if(typeof(defaultValue=='undefined')) {
			defaultValue = null;
		}

		return defaultValue;
	},
	
   /**
    * Whether the dialog is currently shown.
    * @returns {Boolean}
    */
	IsShown:function()
	{
		return this.isShown;
	}
};

Dialog_Basic = Class.extend(Dialog_Basic);