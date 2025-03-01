/**
 * Utility class for working with modal dialogs based on bootstrap.
 * 
 * @package Application
 * @subpackage Dialog
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class DialogHelper
 * @static
 */
var DialogHelper =
{
   /**
    * Creates a dialog with the specified markup, and returns the 
    * jQuery DOM element for it.
    * 
    * @param {String} title
    * @param {String} body
    * @param {String} [footer]
    * @param {Object} [options]
    * @param {Array} [options.modalClasses]
    * @param {Array} [options.bodyClasses]
    * @param {Integer|String} [options.id] The ID for the dialog div.
    * @returns {jQuery}
    */
	createDialog:function(title, body, footer, options)
	{
		if(typeof(options)=='undefined') {
			options = {};
		}
		
		if(typeof(options.id)=='undefined') {
			options.id = 'dialog_'+nextJSID();
		}
		
		if(typeof(options.modalClasses)=='undefined') {
			options.modalClasses = [];
		}

		if(typeof(options.bodyClasses)=='undefined') {
			options.bodyClasses = [];
		}
		
		const dialogID = options.id;
		let html =
		'<div class="modal hide '+application.dialogAnimation+' '+options.modalClasses.join(' ')+'" id="'+dialogID+'">';
			if(title!=null) {
				html +=
				'<div class="modal-header">'+
					'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'+
					'<h3 class="modal-title">'+title+'</h3>'+
				'</div>';
			}
			html +=
			'<div class="modal-body '+options.bodyClasses.join(' ')+'">'+body+'</div>';
			if(typeof(footer)!='undefined' && footer!=null) {
				html +=
				'<div class="modal-footer">'+footer+'</div>';
			}
			html +=
		'</div>';

		$('body').append(html);
		
		return $('#'+dialogID);
	},
	
   /**
    * Like the createDialog method, but creates a dialog that is wider and higher
    * to allow for more content.
    * 
    * @param {String} title
    * @param {String} body
    * @param {String} [footer]
    * @param {Object} [options]
    * @param {Array} [options.modalClasses]
    * @param {Array} [options.bodyClasses]
    * @returns {jQuery}
    */
	createLargeDialog:function(title, body, footer, options)
	{
		if(typeof(options)=='undefined') {
			options = {};
		}
		
		if(typeof(options.modalClasses)=='undefined') {
			options.modalClasses = [];
		}

		if(typeof(options.bodyClasses)=='undefined') {
			options.bodyClasses = [];
		}

		options.bodyClasses.push('large');
		options.modalClasses.push('large');
		
		return this.createDialog(title, body, footer, options);
	},
	
   /**
    * Renders a button that closes the dialog when clicked.
    * Returns the required markup.
    *

    * @param {String} label
    * @param {String} [id]
    * @returns {UI_Button}
    */
	renderButton_close:function(label, id)
	{
		if(typeof(label)=='undefined') {
			label = t('Close');
		}
		
		var btn = UI.Button(label)
			.SetAttribute('data-dismiss', 'modal')
			.SetAttribute('aria-hidden', 'true');
		
		if(typeof(id)!='undefined') {
			btn.SetID(id);
		}
		
		return btn;
	},
	
   /**
    * Renders a primary dialog button that executes the specified
    * javascript statement when clicked.
    * 
    * @param {String} label
    * @param {String} [statement]
    * @param {String} [loadingText]
    * @param {String} [id]
    * @returns {UI_Button}
    */
	renderButton_primary:function(label, statement, loadingText, id)
	{
		var btn = UI.Button(label)
			.MakePrimary();
		
		if(typeof(id)!='undefined') {
			btn.SetID(id);
		}

		if(typeof(loadingText)!='undefined') {
			btn.SetAttribute('data-loading-text', loadingText);
		}
		
		if(typeof(statement) != 'undefined' && statement != null && statement.length > 0) {
			btn.SetOnclick(statement);
		}
		
		return btn;
	},
	
   /**
    * Renders an introductory text that can be used in the body of the dialog
    * (typically as first element in the body).
    * 
    * @param {String} text
    * @returns {String}
    */
	renderAbstract:function(text)
	{
		return ''+
		'<div class="modal-abstract">'+
			text+
		'</div>';
	},
	
	createButton:function(label)
	{
		return UI.Button(label);
	}
};