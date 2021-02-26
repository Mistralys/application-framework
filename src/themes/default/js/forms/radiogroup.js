/**
 * Form helper class that can be used to dynamically create groups
 * of radio buttons and manipulate their values.
 *
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class 
 * @see FormHelper.createRadioGroup
 */
var FormHelper_RadioGroup = 
{
	'id':null,
	'elements':null,
	'name':null,
	'defaultValue':null,
	'rendered':null,
	'postRenderAttempts':null,
	'maxPostRenderAttempts':3,
	'activeElement':null,
	'classes':null,
	
   /**
    * @constructs
    * @param {String} name
    * @param {String} [value=null] The default radio button to preselect. If none is set, the first in the list is selected automatically.
    */
	init:function(name, value)
	{
		if(typeof(value)=='undefined') {
			value = null;
		}
		
		this.id = nextJSID();
		this.defaultValue = value;
		this.name = name;
		this.elements = [];
		this.rendered = false;
		this.postRenderAttempts = 0;
		this.maxPostRenderAttempts = 3;
		this.activeElement = null;
		this.withIcons = false;
		this.classes = [];
		this.eventHandlers = {
			'change':[]
		};
	},

   /**
    * Adds a radio element to the group, and returns the instance
    * of the radio helper.
    *
    * @param {String} value
    * @param {String} label
    * @return {FormHelper_Radio}
    */
	Add:function(value, label)
	{
		var element = FormHelper.createRadio(
			this.name,
			value,
			label
		)
		.AddClass('rd'+this.id)
		.SetGroup(this);
		
		this.elements.push(element);
		return element;
	},
	
   /**
    * Adds an event listener for the change event, which is
    * called whenever the user selects another radio element
    * in the group.
    *
    * @param {Function} handler
    * @return {FormHelper_RadioGroup}
    */
	Change:function(handler)
	{
		return this.AddListener('change', handler);
	},
	
   /**
    * Adds an event listener for the specified event. Note that
    * no error is triggered if the event is unknown. Always prefer
    * the direct methods like Change() to add event handlers to avoid
    * this issue.
    *
    * @param {String} eventName
    * @param {Function} handler
    * @returns {FormHelper_RadioGroup}
    */
	AddListener:function(eventName, handler)
	{
		if(typeof(this.eventHandlers[eventName])!='undefined') {		
			this.eventHandlers[eventName].push(handler);
		}
		
		return this;
	},

   /**
    * Sets that all radios should have an icon instead of the standard
    * radio button. Use the radio item's SetIcon method to specify an
    * icon to use, otherwise the default ItemActive icon will be used.
    *
    * @return {FormHelper_RadioGroup}
    */
	MakeIconList:function()
	{
		this.withIcons = true;
		return this;
	},
	
	Render:function()
	{
		this.rendered = true;
		
		if(this.elements.length==0) {
			return '';
		}
		
		// if no default value is set, use that of the first element in the list.
		if(this.defaultValue==null) {
			this.defaultValue = this.elements[0].GetValue();
		}
		
		this.AddClass('radio-group');
		
		if(this.withIcons) {
			this.AddClass('with-icons');
		}
		
		var html = ''+
		'<div class="'+this.classes.join(' ')+'" id="rg'+this.id+'">';
			for(var i=0; i<this.elements.length; i++) {
				var element = this.elements[i];
				if(element.GetValue()==this.defaultValue) {
					element.Check();
				}
				
				if(this.withIcons && !element.HasIcon()) {
					element.SetIcon(UI.Icon().ItemActive());
				}
				
				html += element.Render() + ' ';
			}
			html += ''+
		'</div>';
			
		this.SchedulePostRender();
			
		return html;
	},
	
   /**
    * Adds a class to the wrapper element around the radio elements.
    *
    * @param {String} className
    * @return {FormHelper_RadioGroup}
    */
	AddClass:function(className)
	{
		if(!this.HasClass(className)) {
			this.classes.push(className);
		}
		
		return this;
	},
	
   /**
    * Checks whether the wrapper element has the specified class.
    *
    * @param {String} className
    * @returns {Boolean}
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
    * This attempts to do post-rendering stuff. If the required
    * elements are not available in the DOM yet, it sets a timeout
    * to try again after a short delay, repeating this until the 
    * maximum set amount of tries.
    */
	SchedulePostRender:function()
	{
		// if the element is present in the DOM, we can go ahead
		var el = $('#rg'+this.id);
		if(el.length > 0) {
			this.PostRender();
			return;
		}
		
		// avoid doing this indefinitely
		this.postRenderAttempts++;
		if(this.postRenderAttempts >= this.maxPostRenderAttempts) {
			return;
		}
		
		// set a timeout to try again
		var group = this;
		UI.RefreshTimeout(function() {
			group.SchedulePostRender();
		});
	},
	
	PostRender:function()
	{
		for(var i=0; i<this.elements.length; i++) {
			this.elements[i].PostRender();
		}
		
		// tell the initially selected radio button that it's selected
		var checked = $('#rg'+this.id+' input[type=radio]:checked').first();
		
		if(checked.length > 0) {
			var element = this.GetElementByRadio(checked);
			this.Handle_Change(element);
		}
	},
	
   /**
    * Retrieves the radio element class instance by a radio element instance.
    *
    * @param DOMNode radioElement
    * @returns {FormHelper_Radio|NULL}
    */
	GetElementByRadio:function(radioElement)
	{
		var id = radioElement.attr('id');
		for(var i=0; i<this.elements.length; i++) {
			var element = this.elements[i];
			if(element.id==id) {
				return element;
			}
		}
		
		return null;
	},
	
	Handle_Change:function(activeElement)
	{
		this.activeElement = activeElement;
		
		activeElement.Handle_Checked();
		
		for(var i=0; i<this.elements.length; i++) {
			var element = this.elements[i];
			if(element==activeElement) {
				continue;
			}
			
			element.Handle_Unchecked();
		}
		
		this.Trigger('change');
	},
	
	Trigger:function(eventName)
	{
		if(typeof(this.eventHandlers[eventName])=='undefined') {
			return;
		}
		
		for(var i=0; i<this.eventHandlers[eventName].length; i++) {
			this.eventHandlers[eventName][i].call(this);
		}
	},
	
   /**
    * Retrieves the value of the currently selected radio element in the group.
    *
    * @returns {String}
    */
	GetValue:function()
	{
		if(this.activeElement!=null) {
			return this.activeElement.GetValue();
		}
		
		return this.defaultValue;
	},
	
	toString:function()
	{
		return this.Render();
	}
};

FormHelper_RadioGroup = Class.extend(FormHelper_RadioGroup);