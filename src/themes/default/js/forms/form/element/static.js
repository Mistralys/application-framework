/**
 * Static form element for displaying static text. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_Static = 
{
	'content':null,
		
	_init:function()
	{
		this.content = '';
	},
		
	GetElementType:function()
	{
		return 'Static';
	},

   /**
    * Replaces the existing content with the specified text/html.
    * @param {String} content
    * @returns {FormHelper_Form_Element_Static}
    */
	SetContent:function(content)
	{
		this.content = content;
		
		if(this.rendered) {
			$('#'+this.id+'-content').html(content);
		}
		
		return this;
	},
	
	_Render:function()
	{
		return '<div id="'+this.id+'-content">' + this.content + '</div>';
	},

	_GetLiveValue:function()
	{
		return null;
	},
	
	_SetLiveValue:function(value)
	{
		
	}
};

FormHelper_Form_Element_Static = FormHelper_Form_Element.extend(FormHelper_Form_Element_Static);