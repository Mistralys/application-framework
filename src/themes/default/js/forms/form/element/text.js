/**
 * Text input element. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_Text = 
{
	'ERROR_INVALID_ELEMENT_TYPE':528001,
		
	'type':null,
	'validTypes':['search', 'email', 'url', 'tel', 'number', 'range', 'date', 'month', 'week', 'time', 'datetime', 'datetime-local', 'color'],
	'maxLength':null,
	'minLength':null,
	
	_init:function()
	{
		this.type = 'text';
		this.maxLength = null;
		this.minLength = null;
	},
		
	GetElementType:function()
	{
		return 'Text';
	},
	
	SetType:function(type)
	{
		if(!in_array(type, this.validTypes)) {
			throw new ApplicationException(
				'Invalid element type',
				'The element type ['+type+'] is not valid. Valid types are: ['+this.validTypes.join(', ')+'].',
				this.ERROR_INVALID_ELEMENT_TYPE
			);
		}
		
		this.type = type;
		return this;
	},
	
	_Render:function()
	{
		var atts = this.GetAttributes();
		atts['type'] = this.type;
		atts['value'] = this.GetValue();
		
		return '<input'+UI.CompileAttributes(atts)+'/>'+this.append;
	},
	
	_GetLiveValue:function()
	{
		return $('#'+this.id).val();
	},
	
	_SetLiveValue:function(value)
	{
		return $('#'+this.id).val(value);
	},
	
	_PostRender:function()
	{
		var element = this;
		$('#'+this.id).on('change', function() {
			element.Handle_Change();
		});
	},
	
	_Handle_AutoHelpTexts:function()
	{
		if(this.maxLength != null && this.minLength != null) {
			this.RegisterAutoHelpText(t(
				'%1$s to %2$s characters.', 
				this.minLength, 
				this.maxLength
			));
		} else {
			if(this.maxLength != null) {
				this.RegisterAutoHelpText(t(
					'Max. %1$s characters.',
					this.maxLength
				));
			}
			if(this.minLength != null) {
				this.RegisterAutoHelpText(t(
					'Min. %1$s characters.',
					this.minLength
				));
			}
		}
	},
	
	SetMaxLength:function(length)
	{
		this.maxLength = length;
		
		this.AddRule(
			function(value) {
				// this case is handled by the required rule, if any
				if(isEmpty(value)) {
					return true;
				}
				
				if(value.length <= length) {
					return true;
				}
				
				return false;
			},
			t('May not be longer than %1$s characters.', length)
		);
	},
	
	SetMinLength:function(length)
	{
		this.minLength = length;
		
		this.AddRule(
			function(value) {
				// this case is handled by the required rule, if any
				if(isEmpty(value)) {
					return true;
				}
				
				if(value.length >= length) {
					return true;
				}
				
				return false;
			},
			t('May not be shorter than %1$s characters.', length)
		);
	},
	
	SetAllowedLength:function(minLength, maxLength)
	{
		this.SetMinLength(minLength);
		this.SetMaxLength(maxLength);
		return this;
	},
	
	SetPlaceholder:function(text)
	{
		return this.SetAttribute('placeholder', text);
	}
};

FormHelper_Form_Element_Text = FormHelper_Form_Element.extend(FormHelper_Form_Element_Text);