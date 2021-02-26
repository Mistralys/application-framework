/**
 * Handles arbitrary HTML content to be added to the form. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends FormHelper_Form_Element
 */
var FormHelper_Form_Element_HTML = 
{
	'html':null,
		
	_init:function()
	{
		this.html = this.label;
	},
		
	GetElementType:function()
	{
		return 'HTML';
	},

   /**
    * Replaces the existing HTML with the specified code.
    * @param {String} html
    * @returns {FormHelper_Form_Element_HTML}
    */
	SetHTML:function(html)
	{
		this.html = html;
		
		if(this.rendered) {
			$('#'+this.id+'-html').html(html);
		}
		
		return this;
	},
	
	Render:function()
	{
		return ''+
		'<div id="' + this.id + '-container">' +
			'<div id="' + this.id + '-html">' + this.html + '</div>'+
		'</div>';
	},
	
	_GetLiveValue:function()
	{
		return '';
	},
	
	_SetLiveValue:function(value)
	{
		
	}
};

FormHelper_Form_Element_HTML = FormHelper_Form_Element.extend(FormHelper_Form_Element_HTML);