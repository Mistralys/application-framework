/**
 * Handles a single hidden form element.
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_Hidden = 
{
	_init:function()
	{
		// the value is set in the label on instantiation
		this.value = this.label;
	},
	
	GetElementType:function()
	{
		return 'Hidden';
	},
	
	Render:function()
	{
		var atts = this.GetAttributes();
		atts['type'] = 'hidden';
		atts['value'] = this.GetValue();
		
		return '<input'+UI.CompileAttributes(atts)+'/>';
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
	}
};

FormHelper_Form_Element_Hidden = FormHelper_Form_Element.extend(FormHelper_Form_Element_Hidden);