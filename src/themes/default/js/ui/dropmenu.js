/**
 * Utility class that offers an API to easily dynamically create
 * Bootstrap button dropdowns.
 *
 * @package UI
 * @subpackage Bootstrap
 * @class 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_DropMenu = 
{
	'label':null,
	'icon':null,
	'size':null,
	'dropup':null,
	'classes':null,
	'split':null,
	'clickHandler':null,
	'menu':null,
	'caret':null,
	'layout':null,
	'additionalButtons':null,
	'rendered':null,
	'styles':null,
	'container':null,
	
	
   /**
    * @constructs
    * @param {String} label The label for the dropdown button.
    */
	init:function(label)
	{
		if(typeof(label)=='undefined') {
			label = '';
		}
		
		this.id = 'dropmenu'+nextJSID();
		this.label = label;
		this.size = null;
		this.icon = null;
		this.dropup = false;
		this.classes = [];
		this.split = false;
		this.clickHandler = null;
		this.menu = UI.Menu();
		this.caret = true;
		this.layout = 'default';
		this.additionalButtons = [];
		this.rendered = false;
		this.styles = {};
		this.container = true;
	},
	
   /**
    * Removes the button's caret. Handy for example if you
    * want to make an icon-only dropdown button. Note: has 
    * not effect with split buttons.
    * 
    */
	NoCaret:function()
	{
		this.caret = false;
		return this;
	},
		
   /**
    * Makes the dropdown button into a small button.
    * 
    * @returns {UI_DropMenu}
    */
	MakeSmall:function()
	{
		return this.MakeSize('small');
	},
	
   /**
    * Makes the dropdown button into a large button.
    * 
    * @returns {UI_DropMenu}
    */
	MakeLarge:function()
	{
		return this.MakeSize('large');
	},
	
   /**
    * Makes the dropdown button into a miniature button.
    * 
    * @returns {UI_DropMenu}
    */
	MakeMini:function()
	{
		return this.MakeSize('mini');
	},
	
	'sizes':{
		'2':{
			'large':'large',
			'small':'small',
			'mini':'mini'
		},
		'3':{
			'large':'lg',
			'small':'sm',
			'mini':'xs'
		}
	},
	
	MakeSize:function(size)
	{
		this.size = this.sizes[UI.BOOTSTRAP_VERSION][size];
		return this;
	},
		
   /**
    * Makes the menu appear above the button instead of below it.
    * 
    * @return {UI_DropMenu}
    */
	MakeDropup:function()
	{
		this.dropup = true;
		return this;
	},
	
   /**
    * Adds a class to the dropdown's button. Note that each
    * class will only be added once, even if you add it multiple
    * times.
    * 
    * @param {String} className
    * @return {UI_DropMenu}
    */
	AddClass:function(className)
	{
		if(!this.HasClass(className)) {
			this.classes.push(className);
		}
		
		return this;
	},
	
   /**
    * Checks whether the dropdown's button has the specified class.
    * 
    * @return {Boolean}
    */
	HasClass:function(className)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i] == className) {
				return true;
			}
		}
		
		return false;
	},
	
   /**
    * Splits the dropdown button into a separately clickable
    * button and the button with a caret to show the menu. 
    * 
    * @param {Function} clickHandler The function that will handle the click event on the button.
    * @return {UI_DropMenu}
    */
	MakeSplit:function(clickHandler)
	{
		this.split = true;
		this.clickHandler = clickHandler;
		return this;
	},
	
   /**
    * Adds a separator item to the dropdown menu.
    */
	AddSeparator:function()
	{
		this.menu.AddSeparator();
		return this;
	},
	
   /**
    * Adds a submenu.
    * @return {UI_Menu_Submenu}
    */
	AddSubmenu:function(label)
	{
		return this.menu.AddSubmenu(label);
	},
	
   /**
    * Adds a menu item to the dropdown menu.
    * 
    * @param {String} label
    * @param {String} name
    * @return {UI_Menu_Item}
    */
	AddItem:function(label, name)
	{
		return this.menu.AddItem(label, name);
	},
	
   /**
    * Retrieves a menu item by its name.
    * 
    * @param {String} name
    * @return {UI_Menu_Item}
    */
	GetItem:function(name)
	{
		return this.menu.GetItem(name);
	},
	
   /**
    * Retrieves the dropdown's menu object, which is used
    * to generate the dropdown menu itself. Use this to add
    * the elements you wish to see to the menu.
    * 
    * @return {UI_Menu}
    */
	GetMenu:function()
	{
		return this.menu;
	},
	
   /**
    * Sets the icon to use for the menu button.
    * 
    * @param {UI_Icon} icon
    * @returns {UI_Button}
    */
	SetIcon:function(icon)
	{
		this.icon = icon;
		return this;
	},

   /**
    * Styles the button as a primary button.
    * 
    * @returns {UI_DropMenu}
    */
	MakePrimary:function()
	{
		return this.MakeType('primary');
	},
	
   /**
    * Styles the button as a button for a dangerous operation, like deleting records.
    * 
    * @returns {UI_DropMenu}
    */
	MakeDangerous:function()
	{
		return this.MakeType('danger');
	},
	
	MakeDeveloper:function()
	{
		return this.MakeType('developer');
	},
	
   /**
    * Styles the button as an informational button.
    * 
    * @returns {UI_DropMenu}
    */
	MakeInformational:function()
	{
		return this.MakeType('info');
	},
	
   /**
    * Styles the button as a success button.
    * 
    * @returns {UI_DropMenu}
    */
	MakeSuccess:function()
	{
		return this.MakeType('success');
	},
	
   /**
    * Styles the button as a warning button for potentially dangerous operations.
    * 
    * @returns {UI_DropMenu}
    */
	MakeWarning:function()
	{
		return this.MakeType('warning');
	},
	
   /**
    * Styles the button as an inverted button.
    * 
    * @returns {UI_DropMenu}
    */
	MakeInverse:function()
	{
		return this.MakeType('inverse');
	},
	
   /**
    * Makes the menu right-aligned, so that the dropdown menu
    * opens to the left of the button.
    * 
    * @return {UI_DropMenu}
    */
	MakeRightAligned:function()
	{
		this.menu.MakeRightAligned();
		return this;
	},
	
	MakeType:function(type)
	{
		this.layout = type;
		return this;
	},
	
	AddMenuClass:function(className)
	{
		this.menu.AddClass(className);
		return this;
	},
	
	RemoveMenuClass:function(className)
	{
		this.menu.RemoveClass(className);
		return this;
	},

	Render:function()
	{
		var groupClasses = ['btn-group'];
		if(this.dropup) {
			groupClasses.push('dropup');
		}
		
		this.AddClass('btn');
		this.AddClass('btn-'+this.layout);
		
		if(this.size!=null) {
			this.AddClass('btn-'+this.size);
		}
		
		var styles = [];
		$.each(this.styles, function(part, value) {
			styles.push(part + ':' + value);
		});
		
		var atts = {
			'id':this.id,
			'class':groupClasses.join(' '),
			'style':styles.join(';')
		};
		
		var html = ''+
		this._RenderAdditionalButtons()+
		this._RenderButton()+
		this.menu.Render();
		
		if(this.container) {
			html = ''+
			'<div'+UI.CompileAttributes(atts)+'>' +
				html +
			'</div>';
		}
		
		this.rendered = true;
		return html;
	},
	
	AdditionalButton:function(button)
	{
		this.additionalButtons.push(button);
	},
	
	_RenderAdditionalButtons:function()
	{
		if(this.additionalButtons.length == 0) {
			return '';
		}
		
		var html = '';
		$.each(this.additionalButtons, function(idx, button) {
			html += button.Render();
		});
		
		return html;
	},
	
	_RenderButton:function()
	{
		// render the split button
		if(this.split) {
			return this._RenderSplitButton();
		}
		
		var label = this.label;
		if(this.icon != null) {
			label = this.icon.Render()+' '+label;
		}

		var html = ''+
		'<a class="'+this.classes.join(' ')+' dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">'+
			label+' '+
			this._RenderCaret()+
		'</a>';
		
		return html;
	},
	
	_RenderSplitButton:function()
	{
		var label = this.label;
		if(this.icon != null) {
			label = this.icon.Render()+' '+label;
		}	
		
		var html = ''+
		'<button class="'+this.classes.join(' ')+'">'+label+'</button>'+
		'<button class="'+this.classes.join(' ')+' dropdown-toggle" data-toggle="dropdown">'+
			'<span class="caret"></span>'+
		'</button>';
		
		return html;
	},
	
	_RenderCaret:function()
	{
		if(!this.caret) {
			return '';
		}
		
		return '<span class="caret"></span>';
	},
	
	AddStyle:function(part, value)
	{
		this.styles[part] = value;
	},
	
   /**
    * Hides the menu.
    * @return {UI_DropMenu}
    */
	Hide:function()
	{
		if(this.rendered) {
			$('#'+this.id).hide();
		} else {
			this.AddStyle('display', 'none');
		}
		
		return this;
	},
	
   /**
    * Shows the menu in case it was hidden before.
    * @return {UI_DropMenu}
    */
	Show:function()
	{
		if(this.rendered) {
			$('#'+this.id).show();
		}
		
		return this;
	},
	
	NoContainer:function()
	{
		this.container = false;
	},
	
	toString:function()
	{
		return this.Render();
	}
};

UI_DropMenu = Class.extend(UI_DropMenu);