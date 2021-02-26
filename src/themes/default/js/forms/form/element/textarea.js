/**
 * Textarea element. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_Textarea = 
{
	GetElementType:function()
	{
		return 'Textarea';
	},
	
	_Render:function()
	{
		var atts = this.GetAttributes();
		var value = this.GetValue();
		if(isEmpty(value)) {
			value = '';
		}
		
		return '<textarea'+UI.CompileAttributes(atts)+'>'+value+'</textarea>'+this.append;
	},
	
   /**
    * Sets the size of the textarea. Note that the columns attribute
    * may be disregarded depending on the classes and/or styles set
    * on the element.
    * 
    * @param {Int} rows
    * @param {Int} [cols]
    * @returns {FormHelper_Form_Element_Textarea}
    */
	SetSize:function(rows, cols)
	{
		this.SetAttribute('rows', rows);
		
		if(!isEmpty(cols)) {
			this.SetAttribute('cols', cols);
		}
		
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
	
	_PostRender:function()
	{
		var element = this;
		$('#'+this.id).on('change', function() {
			element.Handle_Change();
		});
	}
};

FormHelper_Form_Element_Textarea = FormHelper_Form_Element.extend(FormHelper_Form_Element_Textarea);