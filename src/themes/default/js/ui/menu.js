/**
 * Utility class that offers an API to easily create Bootstrap
 * menus, like dropdown menus.
 *
 * @package UI
 * @subpackage Bootstrap
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_Menu = 
{
	'id':null,
	'type':null,
	'items':null,
	'classes':null,
	'attributes':null,
	'rendered':null,
	'element':null,
		
   /**
    * Constructor.
    */
	init:function()
	{
		this.id = 'menu'+nextJSID();
		this.type = 'dropdown';
		this.items = [];
		this.classes = [];
		this.attributes = {};
		this.rendered = false;
		this.element = null;

		this.AddClass('dropdown-menu');
		this.SetAttribute('role', 'menu');
		this.SetAttribute('aria-labelledby', this.id);
	},

   /**
    * Retrieves the menu's ID attribute (for the wrapper element, usually the UL).
    * 
    * @returns {Number}
    */
	GetID:function()
	{
		return this.id;
	},

   /**
    * Sets the ID attribute. The menu always gets an automatic ID,
    * this can be used to override this and use your own ID.
    * 
    * @param {String} id
    * @returns {UI_Menu}
    */
	SetID:function(id)
	{
		this.id = id;
		this.SetAttribute('aria-labelledby', this.id);
		return this;
	},
	
   /**
    * Adds a separator item which renders as a divider line
    * between items. This will be ignored if it is the first
    * item in the menu, or if the previously added item was
    * already a separator.
    * 
    * Note: since separator items have no API or further 
    * configuration items, the item is not returned.
    * 
    * @return {UI_Menu}
    */
	AddSeparator:function()
	{
		// won't add this as first item in a menu
		if(this.items.length==0) {
			return this;
		}
		
		// check if the previous item isn't already a separator
		if(this.items.length >= 2) {
			var previous = this.items[this.items.length-1];
			if(previous.GetType() === 'separator') {
				return this;
			}
		}
		
		var item = new UI_Menu_Separator(this, null, null);
		
		this.items.push(item);
		return this;
	},
	
   /**
    * Adds a new regular menu item and returns the created 
    * object instance to configure it further.
    * 
    * @param {String} label
    * @param {String} name Name to retrieve the item again later
    * @return {UI_Menu_Item}
    */
	AddItem:function(label, name)
	{
		var item = new UI_Menu_Item(this, label, name);
		this.items.push(item);
		return item;
	},
	
   /**
    * Adds a submenu.
    * 
    * @param {String} label
    * @returns {UI_Menu_Submenu}
    */
	AddSubmenu:function(label)
	{
		var menu = new UI_Menu_Submenu(this, label);
		this.items.push(menu);
		return menu;
	},
	
   /**
    * Adds a class to the menu's container tag.
    * 
    * @param {String} className
    * @returns {UI_Menu}
    */
	AddClass:function(className)
	{
		if(!this.HasClass(className)) {
			this.classes.push(className);
		}
		
		if(this.rendered) {
			this.element.addClass(className);
		}
		
		return this;
	},
	
   /**
    * Removes a class from the menu's container tag.
    * Has no effect if it does not have the class name.
    * 
    * @param {String} className
    * @return {UI_Menu}
    */
	RemoveClass:function(className)
	{
		var keep = [];
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i] != className) {
				keep.push(this.classes[i]);
			}
		}
		
		if(this.rendered) {
			this.element.removeClass(className);
		}
		
		this.classes = keep;
		return this;
	},
		
   /**
    * Checks if the menu has the specified class.
    * 
    * @return {Boolean}
    */
	HasClass:function(className)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==className) {
				return true;
			}
		}
		
		return false;
	},

   /**
    * Makes the menu right-aligned, so that the dropdown menu
    * opens to the left of the button.
    * 
    * @return {UI_Menu}
    */
	MakeRightAligned:function()
	{
		return this.AddClass('pull-right');
	},
	
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	},
	
	RemoveAttribute:function(name)
	{
		var keep = {};
		$.each(this.attributes, function(aName, aValue){
			if(aName != name) {
				keep[aName] = aValue;
			}
		});
		
		this.attributes = keep;
		return this;
	},
	
	Render:function()
	{
		this.AddClass('dropdown-menu');
		
		var atts = {
			'class':this.classes.join(' '),
			'role':'menu',
			'id':this.id,
			'aria-labelledby':this.id
		};
		
		var html = ''+
		'<ul '+UI.CompileAttributes(atts)+'>'+
			this._RenderItems()+
		'</ul>';
		
		var menu = this;
		UI.RefreshTimeout(function() {
			menu._PostRender();
		});
		
		return html;
	},
	
	_PostRender:function()
	{
		this.rendered = true;
		this.element = $('#'+this.id);
	},
	
	_RenderItems:function()
	{
		var html = '';
		for(var i=0; i<this.items.length; i++) {
			html += this.items[i].Render();
		}
		
		return html;
	},
	
	toString:function()
	{
		return this.Render();
	},
	
   /**
    * Retrieves a menu item by its name.
    * @param {String} name
    * @returns {NULL|UI_Menu_Item}
    */
	GetItem:function(name)
	{
		for(var i=0; i<this.items.length; i++) {
			var item = this.items[i];
			if(item.GetName() == name) {
				return item;
			}
		}
		
		return null;
	},
	
	SetActive:function(item, active)
	{
		if(active != false) {
			active = true;
		}
		
		for(var i=0; i<this.items.length; i++) {
			this.items[i].SetActive(!active);
		}

		item.SetActive(active);
	}
};

UI_Menu = Class.extend(UI_Menu);