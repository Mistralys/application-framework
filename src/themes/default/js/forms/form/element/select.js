/**
 * Handles a select element with all its options. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_Select = 
{
	'options':null,
		
	_init:function()
	{
		this.options = [];
	},
		
	GetElementType:function()
	{
		return 'Select';
	},
	
	_Render:function()
	{
		var atts = this.GetAttributes();
		atts['type'] = this.type;
		atts['value'] = this.GetValue();
		
		var html = ''+
		'<select'+UI.CompileAttributes(atts)+'>';
			for(var i=0; i<this.options.length; i++) {
				html += this.options[i].Render();
			}
			html += ''+
		'</select>'+this.append;
			
		return html;
	},
	
   /**
    * Adds an option.
    * 
    * @param {String} label
    * @param {String} value
    * @returns {FormHelper_Form_Element_Select_Option}
    */
	AddOption:function(label, value)
	{
		var option = new FormHelper_Form_Element_Select_Option(this, label, value);
		this.options.push(option);
		
		if(this.rendered) {
			$('#'+this.id).append(option.Render());
		}
		
		return option;
	},
	
   /**
    * Adds the standard "Please select" option.
    * @returns {FormHelper_Form_Element_Select_Option}
    */
	AddPleaseSelect:function()
	{
		return this.AddOption(t('Please choose...'), '');
	},
	
   /**
    * Adds an option group.
    * @param {String} label
    * @returns {FormHelper_Form_Element_Select_OptionGroup}
    */
	AddGroup:function(label)
	{
		var group = new FormHelper_Form_Element_Select_OptionGroup(this, label);
		this.options.push(group);
		
		if(this.rendered) {
			$('#'+this.id).append(group.Render());
		}
		
		return group;
	},
	
   /**
    * Removes all options and option groups from the element.
    * @return {FormHelper_Form_Element_Select}
    */
	ClearOptions:function()
	{
		this.options = [];
		$('#' + this.id + ' optgroup').remove();
		$('#' + this.id + ' option').remove();
		
		return this;
	},
	
	_GetLiveValue:function()
	{
		return $('#'+this.id).val();
	},
	
	_SetLiveValue:function(value)
	{
		return $('#'+this.id).val(value);
	},
	
	_Reset:function()
	{
		FormHelper.resetSelectField($('#'+this.id));
	},
	
	_PostRender:function()
	{
		var element = this;
		$('#'+this.id).on('change', function() {
			element.Handle_Change();
		});
	},
	
	IsSelected:function(option)
	{
		if(option.GetValue() == this.GetValue()) {
			return true;
		}
		
		return false;
	}
};

FormHelper_Form_Element_Select = FormHelper_Form_Element.extend(FormHelper_Form_Element_Select);

/**
 * A single option in a form select element. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see FormHelper_Form_Element_Select.AddOption
 */
var FormHelper_Form_Element_Select_Option = 
{
	'label':null,
	'value':null,
	'selectElement':null,
	'attributes':null,
	
   /**
    * Constructor.
    * 
    * @param {FormHelper_Form_Element_Select} selectElement
    * @param {String} label
    * @param {String} value
    */
	init:function(selectElement, label, value)
	{
		this.selectElement = selectElement;
		this.label = label;
		this.value = value;
		this.attributes = {};
	},

   /**
    * Renders the option to HTML.
    * @return {String}
    */
	Render:function()
	{
		this.attributes['value'] = this.value;
		
		if(this.selectElement.IsSelected(this)) {
			this.attributes['selected'] = 'selected';
		}
		
		return '<option'+UI.CompileAttributes(this.attributes)+'>'+this.label+'</option>';
	},
	
   /**
    * Sets an attribute of the option tag.
    * @param {String} name
    * @param {String} value
    * @returns {FormHelper_Form_Element_Select_Option}
    */
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	},
	
   /**
    * Retrieves the value of the option.
    * @returns {String}
    */
	GetValue:function()
	{
		return this.value;
	}
};

FormHelper_Form_Element_Select_Option = Class.extend(FormHelper_Form_Element_Select_Option);

/**
 * And option group for select elements. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see FormHelper_Form_Element_Select.AddGroup
 */
var FormHelper_Form_Element_Select_OptionGroup = 
{
	'label':null,
	'options':null,
	'selectElement':null,
	'attributes':null,
	'classes':[],
	
   /**
    * Constructor.
    * 
    * @param {FormHelper_Form_Element_Select} selectElement
    * @param {String} label
    */
	init:function(selectElement, label)
	{
		this.selectElement = selectElement;
		this.label = label;
		this.options = [];
		this.classes = [];
		this.attributes = {};
	},
	
   /**
    * Adds an option to the group.
    * 
    * @param {String} label
    * @param {String} value
    * @returns {FormHelper_Form_Element_Select_Option}
    */	
	AddOption:function(label, value)
	{
		var option = new FormHelper_Form_Element_Select_Option(this.selectElement, label, value);
		this.options.push(option);
		
		if(this.selectElement.rendered) {
			$('#'+this.attributes['id']).append(option.Render());
		}
		
		return option;
	},
	
   /**
    * Adds a class name to the group tag.
    * @param {String} name
    * @returns {FormHelper_Form_Element_Select_OptionGroup}
    */
	AddClass:function(name)
	{
		if(!in_array(name, this.classes)) {
			this.classes.push(name);
		}
		
		return this;
	},
	
   /**
    * Renders the option group to HTML.
    * @returns {String}
    */
	Render:function()
	{
		this.attributes['label'] = this.label;
		
		if(this.classes.length > 0) {
			this.attributes['class'] = this.classes.join(' ');
		}
		
		if(typeof(this.attributes['id']) == 'undefined') {
			this.attributes['id'] = nextJSID();
		}
		
		var html = ''+
		'<optgroup'+UI.CompileAttributes(this.attributes)+'>';
			for(var i=0; i<this.options.length; i++) {
				html += this.options[i].Render();
			}
			html += ''+
		'</optgroup>';
			
		return html;
	},
	
   /**
    * Sets an attribute of the group tag.
    * 
    * @param {String} name
    * @param {String} value
    * @returns {FormHelper_Form_Element_Select_OptionGroup}
    */
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	}
};

FormHelper_Form_Element_Select_OptionGroup = Class.extend(FormHelper_Form_Element_Select_OptionGroup);