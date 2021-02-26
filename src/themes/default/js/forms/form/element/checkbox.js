/**
 * Handles a single checkbox in a form. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_Checkbox = 
{
	'onValue':null,
	'offValue':null,
	'inlineLabel':null,
	
	_init:function()
	{
		this.onValue = 'true';
		this.offValue = 'false';
		this.inlineLabel = this.label;
		this.label = null;
	},
		
	GetElementType:function()
	{
		return 'Checkbox';
	},
	
	_Render:function()
	{
		var atts = this.GetAttributes();
		atts['id'] = this.id + '-el';
		atts['type'] = 'checkbox';
		atts['value'] = this.GetValue();
		
		if(this.GetValue()==this.onValue) {
			atts['checked'] = 'checked';
		}
		
		delete atts.name;
		
		// to allow having an on and an off value, we store the actual value
		// in a hidden input element, and use the checkbox only for the UI.
		var html = ''+
		'<input type="hidden" name="'+this.name+'" value="'+this.GetValue()+'" id="'+this.id+'"/>'+
		'<label for="'+this.id+'-el" class="checkbox">'+
			'<input'+UI.CompileAttributes(atts)+'> '+
			this.inlineLabel+
		'</label>'+this.append;
		
		return html;
	},
	
	_GetLiveValue:function()
	{
		return $('#'+this.id).val();
	},
	
	_SetLiveValue:function(value)
	{
		$('#'+this.id).val(value);
		
		var state = false;
		if(value==this.onValue) {
			state = true;
		}
		
		$('#'+this.id+'-el').prop('checked', state);
	},
	
	_Reset:function()
	{
		this.SetChecked(false);
	},
	
	_PostRender:function()
	{
		var element = this;
		$('#'+this.id+'-el').on('change', function() {
			element.Handle_Change();
		});
	},
	
	Handle_Change:function()
	{
		var value = this.offValue;
		if($('#'+this.id+'-el').prop('checked')) {
			value = this.onValue;
		}
		
		// update the storage element with the new value
		$('#'+this.id).val(value);
		
		this._super();
	},
	
   /**
    * Sets the values that will be submitted for the checkbox's 
    * on or off states. By default, this is "true" and "false".
    * 
    * @param string onValue
    * @param string offValue
    * @returns {FormHelper_Form_Element_Checkbox}
    */
	SetOnOffValues:function(onValue, offValue)
	{
		var state = this.IsChecked();
		
		this.onValue = onValue;
		this.offValue = offValue;
		
		this.SetChecked(state);
		
		return this;
	},
	
   /**
    * Changes the element's ON and OFF values to "yes" and "no".
    * 
    * @returns {FormHelper_Form_Element_Checkbox}
    */
	MakeYesNo:function()
	{
		return this.SetOnOffValues('yes', 'no');
	},
	
   /**
    * Sets the element as checked. Automatically switches the
    * UI element as well as needed.
    * 
    * @param {Boolean} checked
    * @returns {FormHelper_Form_Element_Checkbox}
    */
	SetChecked:function(checked)
	{
		var value = this.offValue;
		if(checked==true) {
			value = this.onValue;
		}
		
		return this.SetValue(value);
	},
	
   /**
    * Checks whether the element is currently checked.
    * @returns {Boolean}
    */
	IsChecked:function()
	{
		if(this.GetValue()==this.onValue) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Sets the checkbox' boolean value. Can be a boolean,
    * or a valid boolean string representation like <code>yes</code>
    * or <code>true</code>.
    * 
    * @param {String|Boolean} value
    * @returns {FormHelper_Form_Element_Checkbox}
    */
	SetValue:function(value)
	{
		// convert the value to the internal values
		var internal = this.offValue;
		if(string2bool(value) == true) {
			internal = this.onValue;
		}

		return this._super(internal);
	}
};

FormHelper_Form_Element_Checkbox = FormHelper_Form_Element.extend(FormHelper_Form_Element_Checkbox);