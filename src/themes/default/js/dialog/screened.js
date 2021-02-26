/**
 * Dialog which allows switching between different screens.
 * Like tabbed dialogs, but with manual changing between
 * screens. Useful for example for subforms, and allows 
 * switching the dialog's abstract accordingly. 
 * 
 * Usage:
 * 
 * Create the dialog as you would a basic dialog. In the 
 * <code>_init()</code> method, set up the available screens:
 * 
 * <pre>
 * this.AddScreen(new DialogClass_Screen_Screen1Name(this, 'screen1Alias'));
 * this.AddScreen(new DialogClass_Screen_Screen2Name(this, 'screen2Alias'););
 * </pre>
 * 
 * Each screen must have its own class, extending the
 * <code>Dialog_Screened_Screen</code> class.
 * 
 * To switch between screens, use the <code>ShowScreen('screenAlias')</code> method.
 * 
 * @package Application
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 * @extends Dialog_Basic
 * @see Dialog_Screened_Screen
 */
var Dialog_Screened = 
{
	'screens':null,
	'activeScreen':null,
	'isProcessing':null,
	
	init:function()
	{
		this.isProcessing = false;
		this.screens =  {};
		this.activeScreen = null;
		this._super();
	},
	
	/**
	 * Creates and adds a new screen, and returns the instance.
	 * @param {Dialog_Screened_Screen} screen
	 * @returns {Dialog_Screened}
	 */
	AddScreen:function(screen)
	{
		var name = screen.GetName();
		
		if(typeof(this.screens[name]) != 'undefined') {
			application.log('Screened dialog', 'The screen ['+name+'] has already been added.', 'error');
			return this;
		}
		
		this.screens[screen.GetName()] = screen;
		return this;
	},
	
   /**
    * Shows the specified screen. Automatically hides all
    * other screens so only this is shown.
    * 
    * @param {String} name
    * @returns {Dialog_Screened}
    */
	ShowScreen:function(name)
	{
		if(this.isProcessing) {
			return;
		}
		
		this.isProcessing = true;
		
		var dialog = this;
		var found = false;
		var names = [];
		
		$.each(this.screens, function(idx, screen) 
		{
			var screenName = screen.GetName();
			names.push(screenName);
			
			if(screenName == name) {
				screen.Show();
				dialog.activeScreen = screen;
				found = true;
			} else {
				screen.Hide();
			}
		});
		
		if(!found) {
			this.log('Screen ['+name+'] not found. Available screens are ['+names.join(', ')+'].', 'error');
		}
		
		this.isProcessing = false;
	},
	
	_RenderAbstract:function()
	{
		return '&#160;';
	},
	
	_RenderBody:function()
	{
		var html = '';
		$.each(this.screens, function(idx, screen) {
			html += screen.RenderContainer();
		});
		
		return html;
	},
	
	_PostRender:function()
	{
		if(this.activeScreen == null) {
			this.activeScreen = this.GetDefaultScreen();
		}
	},
	
	_Handle_Shown:function()
	{
		this.ShowScreen(this.activeScreen.GetName());
	},
	
	GetDefaultScreen:function()
	{
		var first = null;
		var found = null;
		$.each(this.screens, function(idx, screen) {
			if(first == null) {
				first = screen;
			}		
			
			if(screen.IsDefault()) {
				found = screen;
				return false;
			}
		});
		
		if(found != null) {
			return found;
		}
		
		return first;
	},
	
   /**
    * Retrieves a screen instance by its name.
    * @param {String} name
    * @returns {Dialog_Screened_Screen}
    */
	GetScreen:function(name)
	{
		if(typeof(this.screens[name]) != 'undefined') {
			return this.screens[name];
		}
		
		return null;
	},
	
	_RenderFooter:function()
	{
		$.each(this.screens, function(idx, screen) {
			screen.RenderButtonsRight();
		});
	},
	
	_RenderFooterLeft:function()
	{
		$.each(this.screens, function(idx, screen) {
			screen.RenderButtonsLeft();
		});
	}
};

Dialog_Screened = Dialog_Basic.extend(Dialog_Screened);

/**
 * Represents a single screen in the screened dialog.
 * This is where the actual content is rendered. 
 * 
 * @package Application
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 * @extends Dialog_Basic
 * @see Dialog_Screened
 */
var Dialog_Screened_Screen = 
{
	'dialog':null,
	'name':null,
	'jsID':null,
	'rendered':null,
	'isDefault':null,
	'buttons':null,
	'buttonsPosition':null,
	'options':null,
	'rendering':null,
	'isShown':null,
	
	init:function(dialog, name)
	{
		this.dialog = dialog;
		this.name = name;
		this.jsID = nextJSID();
		this.isDefault = false;
		this.rendered = false;
		this.buttons = [];
		this.buttonsPosition = null;
		this.options = {};
		this.rendering = false;
		this.isShown = false;
		
		this._init();
	},
	
   /**
    * Sets a screen option, insofar the screen supports it:
    * this depends entirely on the screen implementation.
    * 
    * @param {String} name
    * @param {Mixed} value
    * @returns {Dialog_Screened_Screen}
    */
	SetOption:function(name, value)
	{
		this.options[name] = value;
		return this;
	},
	
   /**
    * Retrieves a screen option.
    * 
    * @param {String} name
    * @param {Mixed} [defaultValue]
    * @returns {Dialog_Screened_Screen}
    */
	GetOption:function(name, defaultValue)
	{
		if(typeof(this.options[name]) != 'undefined') {
			return this.options[name];
		}
		
		if(isEmpty(defaultValue)) {
			defaultValue = null;
		}
		
		return defaultValue;
	},
	
	GetID:function()
	{
		return this.jsID;
	},
	
	GetName:function()
	{
		return this.name;
	},
	
	SetAbstract:function(abstract)
	{
		this.dialog.SetAbstract(abstract);
		return this;
	},
	
	_init:function()
	{
		
	},
	
	MakeDefault:function()
	{
		this.isDefault = true;
		return this;
	},
	
	RenderContainer:function()
	{
		return '<div id="'+this.elementID('container')+'" style="display:none;"></div>';
	},
	
	Show:function()
	{
		if(this.isShown) {
			return;
		}
		
		// prevent double-clicks
		if(this.rendering) {
			return;
		}
		
		if(!this.rendered) {
			this.rendering = true;
			this.log('Not rendered yet, rendering body.');
			this.element('container').html(this._RenderBody());

			var screen = this;
			UI.RefreshTimeout(function() {
				screen.PostRender();
				screen.Show();
			});
			return this;
		}
		
		this.log('Showing the screen.');
		
		this.element('container').show();
		
		this.dialog.SetAbstract(this._RenderAbstract());
		
		$.each(this.buttons, function(idx, button) {
			button.Show();
		});
		
		this._Handle_Shown();
		
		this.isShown = true;
		return this;
	},
	
	Hide:function()
	{
		if(!this.isShown) {
			return;
		}
		
		this.element('container').hide();
		
		if(!isEmpty(this.buttons)) {
			$.each(this.buttons, function(idx, button) {
				button.Hide();
			});
		}
		
		this.isShown = false;
		return this;
	},
	
	HideDialog:function()
	{
		this.GetDialog().Hide();
	},
	
	GetDialog:function()
	{
		return this.dialog;
	},
	
	RenderBody:function()
	{
		var html = this._Render();
		return html;
	},
	
	PostRender:function()
	{
		this._PostRender();
		this.rendered = true;
		this.rendering = false;
		
		this.log('Post render done.');
	},
	
	_RenderBody:function()
	{
		throw new ApplicationException('_RenderBody method not implemented');
	},
	
	_PostRender:function()
	{
		
	},
	
	_RenderAbstract:function()
	{
		return '';
	},
	
	_Handle_Shown:function()
	{
		
	},
	
	GetID:function()
	{
		return this.jsID;
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
    * Switches to another screen.
    * @param {String} name
    * @returns {Dialog_Screened_Screen}
    */
	ShowScreen:function(name)
	{
		this.dialog.ShowScreen(name);
		return this;
	},
	
   /**
    * Retrieves the specified screen by its name.
    * @return {Dialog_Screened_Screen|NULL}
    */
	GetScreen:function(name)
	{
		return this.dialog.GetScreen(name);
	},
	
	IsDefault:function()
	{
		return this.isDefault;
	},
	
	log:function(message, category)
	{
		this.dialog.log('Screen ['+this.GetName()+'] | '+ message, category);
	},
	
	AddButton:function(button, name)
	{
		name = this.jsID+name;

		button.Hide(); // initially hidden, shown as needed
		
		this.dialog.AddButton(button, name, this.buttonsPosition);
		this.buttons.push(button);
		return this;
	},
	
	GetButton:function(name)
	{
		name = this.jsID+name;
		
		return this.dialog.GetButton(name);
	},
	
	HideButton:function(name)
	{
		var btn = this.GetButton(name);
		if(btn) {
			btn.Hide();
		}
		
		return this;
	},
	
	ShowButton:function(name)
	{
		var btn = this.GetButton(name);
		if(btn) {
			btn.Show();
		}
		
		return this;
	},
	
	AddButtonSeparator:function(name)
	{
		if(isEmpty(name)) {
			name = nextJSID();
		}
		
		// simulate a real button
		var separator = 
		{
			'id':nextJSID(),
			'display':'inline-block',
			'rendered':false,
			
			SetID:function(id)
			{
				this.id = id;
				return this;
			},
			
			GetLabel:function()
			{
				return '(Separator)';
			},
			
			Hide:function() 
			{
				if(this.rendered) {
					$('#'+this.id).hide();
				}
				
				this.display = 'none';
				return this;
			},
			
			Show:function()
			{
				if(this.rendered) {
					$('#'+this.id).css('display', 'inline-block');
				}
				
				this.display = 'inline-block';
				return this;
			},
			
			MakeDisabled:function() {},
			MakeEnabled:function() {},
			
			Render:function()
			{
				this.rendered = true;
				return '<button type="button" id="'+this.id+'" style="display:'+this.display+'" class="btn btn-default button-separator">&nbsp;</button>';
			},
			
			toString:function()
			{
				return this.Render();
			}
		};
		
		return this.AddButton(separator, name);
	},
	
	RenderButtonsLeft:function()
	{
		this.buttonsPosition = this.dialog.BUTTON_POSITION_LEFT;
		this._Handle_Buttons_Left();
	},
	
	RenderButtonsRight:function()
	{
		this.buttonsPosition = this.dialog.BUTTON_POSITION_RIGHT;
		this._Handle_Buttons_Right();
	},
	
	_Handle_Buttons_Right:function()
	{
		
	},
	
	_Handle_Buttons_Left:function()
	{
		
	},
	
	ChangeBody:function(html)
	{
		this.element('container').html(html);
		
		var screen = this;
		UI.RefreshTimeout(function() {
			screen.Handle_PostChangeBody();
		});
		return this;
	},
	
	Handle_PostChangeBody:function()
	{
		this._Handle_PostChangeBody();
	},
	
	_Handle_PostChangeBody:function()
	{
		
	}
};

Dialog_Screened_Screen = Class.extend(Dialog_Screened_Screen);