/**
 * File containing the MultiSelect class, which is used to handle
 * the bootstrap multiselect elements. They are handled server-side,
 * this simply manages the specialized clientside functions.
 *
 * @package Application
 * @subpackage Forms
 * @see HTML_QuickForm2_Element_Multiselect
 * @class MultiSelect
 * @static
 */
var MultiSelect =
{
   /**
    * Renders the label for the button of the multiselect element
    * according to the amount of elements that have been selected.
    *
    * @param options The option elements of the select
    * @param select The select element itself
    * @returns string
    */
	Render_Label:function(options, select)
	{
		var text;

		if(options.length==0) {
			text = t('Please select...');
		} else if(options.length==1) {
			text = $(options[0]).text();
		} else {
			text = t('%1$s selected', options.length);
		}

		return text;
	}
};