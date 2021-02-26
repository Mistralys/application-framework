/**
 * Utility class that handles the quick selection dropdown 
 * elements that are used in the application to switch between
 * active records.
 * 
 * @package UI
 * @subpackage Bootstrap
 * @class 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_QuickSelector = 
{
	'id':null,
	'items':null,
		
   /**
    * @constructs
    */
	init:function(id)
	{
		this.id = id;
		this.items = [];
	},
	
   /**
    * Adds an item from the list and the URL to redirect to
    * if the user selects it. The select element only contains
    * element IDs and labels, not the target URLs. This is set
    * automatically serverside.
    *
    * @param {String} id
    * @param {String} url
    */
	AddItem:function(id, url)
	{
		this.items.push({
			'id':id,
			'url':url
		});
	},
	
   /**
    * Initializes the UI element by disabling the navigation buttons that
    * cannot be used (if at the start or the end of the list). This is called
    * automatically serverside.
    *
    */
	Start:function()
	{
		if(!this.GetNext()) {
			this.element('btn_next').addClass('disabled');
		}
		
		if(!this.GetPrevious()) {
			this.element('btn_prev').addClass('disabled');
		}
	},
	
   /**
    * Redirects to the currently selected item in the list.
    *
    */
	Switch:function()
	{
		this.Redirect(this.GetSelected());
	},
	
   /**
    * Redirects to the next item in the list, if any.
    *
    */
	Next:function()
	{
		this.Redirect(this.GetNext());
	},
	
   /**
    * Redirects to the previous item in the list, if any.
    *
    */
	Previous:function()
	{
		this.Redirect(this.GetPrevious());
	},
	
   /**
    * Redirects to the specified list item, if it is a valid list
    * item with a target URL.
    *
    * @param {Object} item
    */
	Redirect:function(item)
	{
		if(item==null) {
			return;
		}

		// show the user that something's happening 
		this.element('label').html(application.renderSpinner(t('Loading...')));
		
		application.redirect(item.url);
	},
	
   /**
    * Retrieves the item as currently selected in the list.
    *
    * @return {Object}
    */
	GetSelected:function()
	{
		var id = this.element('select').val();
		for(var i=0; i<this.items.length; i++) {
			item = this.items[i];
			if(item.id==id) {
				return item;
			}
		}
		
		return null;
	},
	
   /**
    * Retrieves the next item in the list after the currently selected one, if any.
    *
    * @return {Object|NULL}
    */
	GetNext:function()
	{
		var selected = this.GetSelected();
		if(!selected) {
			return null;
		}
		
		for(var i=0; i<this.items.length; i++) {
			item = this.items[i];
			if(item.id == selected.id) {
				idx = i+1;
				if(typeof(this.items[idx]) != 'undefined') {
					return this.items[idx];
				}
			}
		}
		
		return null;
	},
	
   /**
    * Retrieves the next item in the list after the currently selected one, if any.
    *
    * @return {Object|NULL}
    */
	GetPrevious:function()
	{
		var selected = this.GetSelected();
		if(!selected) {
			return null;
		}
		
		for(var i=0; i<this.items.length; i++) {
			item = this.items[i];
			if(item.id == selected.id) {
				idx = i-1;
				if(typeof(this.items[idx]) != 'undefined') {
					return this.items[idx];
				}
			}
		}
		
		return null;
	},
	
	element:function(part)
	{
		return $('#'+this.elementID(part));
	},
	
	elementID:function(part)
	{
		var id = 'qs'+this.id;
		if(typeof(part)!='undefined') {
			id += '_'+part;
		}
		
		return id;
	}
};

UI_QuickSelector = Class.extend(UI_QuickSelector);