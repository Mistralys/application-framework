/**
 * Handler class for a single radio form element.
 * 
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 * @see FormHelper.createRadio
 */
var FormHelper_Radio =
{
	'attributes':null,
	'classes':null,
	'value':null,
	'name':null,
	'label':null,
	'id':null,
	'append':null,
	'prepend':null,
	'checked':null,
	'group':null,
	
   /**
    * Constructor.
    * 
    * @param {String} name
    * @param {String} value
    * @param {String} label
    */
	init:function(name, value, label)
	{
		this.name = name;
		this.label = label;
		this.id = 'rd'+nextJSID();
		this.classes = [];
		this.attributes = {};
		this.value = value;
		this.append = '';
		this.prepend = '';
		this.checked = null;
		this.group = null;
		this.tooltip = null;
	},
	
   /**
    * Sets the radio group instance handling this radio button
    * if any. This is used by the group, it should not be used manually.
    *
    * @param {FormHelper_RadioGroup} group
    * @returns {FormHelper_RadioClass}
    */
	SetGroup:function(group)
	{
		this.group = group;
		return this;
	},
	
   /**
    * Appends some custom markup after the element.
    *
    * @param {String} markup
    * @return {FormHelper_Radio}
    */
	Append:function(markup)
	{
		this.append = markup;
		return this;
	},
	
   /**
    * Prepends some custom markup before the element.
    *
    * @param {String} markup
    * @return {FormHelper_Radio}
    */
	Prepend:function(markup)
	{
		this.prepend = markup;
		return this;
	},
	
   /**
    * Sets the ID for the element. An ID is given automatically, 
    * this can be used to overwrite that.
    *
    * @param {String} id
    * @returns {FormHelper_RadioClass}
    */
	SetID:function(id)
	{
		this.id = id;
		return this;
	},
	
   /**
    * Retrieves the element's id.

    * @returns {String}
    */
	GetID:function()
	{
		return this.id;
	},
	
   /**
    * Retrieves the value of the radio's value attribute.
    *
    * @returns {String}
    */
	GetValue:function()
	{
		return this.value;
	},
	
   /**
    * Checks whether the element has the specified class.
    *
    * @param {String} name
    * @returns {Boolean}
    */
	HasClass:function(name)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==name) {
				return true;
			}
		}
		
		return false;
	},
	
   /**
    * Adds a class to the element. Each class is only added once.
    * 
    * @param {String} name
    * @returns {FormHelper_Radio}
    */
	AddClass:function(name)
	{
		if(!this.HasClass(name)) {
			this.classes.push(name);
		}
		
		return this;
	},
	
   /**
    * Sets an attribute of the element, overwriting any existing value.
    *
    * @param {String} name
    * @param {String} value
    * @returns {FormHelper_Radio}
    */
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	},
	
   /**
    * Sets an icon to place before the radio's label.
    *
    * @param {UI_Icon} icon
    * @returns {FormHelper_RadioClass}
    */
	SetIcon:function(icon)
	{
		this.icon = icon;
		return this;
	},
	
   /**
    * Checks whether an icon has been set.
    *
    * @returns {Boolean}
    */
	HasIcon:function()
	{
		if(this.icon instanceof UI_Icon) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Renders the required markup for the element. Note that
    * it implements the toString method, so it can also be used
    * as a string.
    *
    * @returns {String}
    */
	Render:function()
	{
		this.SetAttribute('name', this.name);
		this.SetAttribute('id', this.id);
		this.SetAttribute('value', this.value);
		
		if(this.checked) {
			this.SetAttribute('checked', 'checked');
		}
		
		var label = this.label;
		if(this.icon) {
			label = this.icon.Render()+' '+label;
		}
		
		var atts = {
			'class':'radio',
			'id':this.id+'_label'
		};
		
		if(this.tooltip != null) {
			atts['title'] = this.tooltip;
		} 
		
		var html =
		this.prepend+
		'<label'+UI.CompileAttributes(atts)+'>'+
			'<input type="radio"'+UI.CompileAttributes(this.attributes)+'/> '+
			label+
		'</label>'+
		'<div class="clear"></div>'+
		this.append;
		
		return html;
	},
	
	PostRender:function()
	{
		if(this.tooltip != null) {
			UI.MakeTooltip('#'+this.id+'_label', false);
		}
		
		var radio = this;
		this.GetRadioElement().change(function() {
			radio.Handle_Change();
		});
	},
	
	Handle_Change:function()
	{
		if(this.GetRadioElement().is(':checked') && this.group!=null) {
			this.group.Handle_Change(this);
		}
	},
	
   /**
    * Sets the radio as checked before it is rendered (has
    * no effect afterwards).
    *
    * @return {FormHelper_Radio}
    */
	Check:function()
	{
		this.checked = true;
		return this;
	},
	
	Handle_Checked:function()
	{
		this.GetLabelElement().addClass('active');
		this.checked = true;
	},
	
	Handle_Unchecked:function()
	{
		this.GetLabelElement().removeClass('active');
		this.checked = false;
	},
	
   /**
    * Retrieves the jQuery extended DOMElement for the radio button itself
    *
    * @returns {DOMNode}
    */
	GetRadioElement:function()
	{
		return $('#'+this.id);
	},
	
   /**
    * Retrieves the jQuery extended DOMElement for the radio button's label
    *
    * @returns {DOMNode}
    */
	GetLabelElement:function()
	{
		return $('#'+this.id+'_label');
	},
	
   /**
    * Sets the text to use for the tooltip to show when hovering
    * over the radio button.
    * 
    * @param {String} tooltip
    * @returns {FormHelper_Radio}
    */
	SetTooltip:function(tooltip)
	{
		this.tooltip = tooltip;
		return this;
	},
	
	toString:function()
	{
		return this.Render();
	}
};

FormHelper_Radio = Class.extend(FormHelper_Radio);