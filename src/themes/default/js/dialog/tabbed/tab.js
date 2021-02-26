/**
 * Base class for a tab in a tabbed dialog instance. Must be
 * extended by the actual implementation class. The extended
 * class must implement the following methods:
 *
 * <ul>
 * <li>Render</li>
 * <li>RenderFooter</li>
 * <li>DoActivate</li>
 * <li>PostRender</li>
 * <li>Handle_Submit</li>
 * </ul>
 *
 * @package Application
 * @subpackage Dialogs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 */
var TabbedDialogTab = 
{ 
	'dialog':null,
	'jsID':null,
	'rendered':null,
	'formID':null,
	
   /**
    * Whether to only apply changes without closing the dialog.
    * @property apply
    * @type {Boolean}
    */
	'apply':null,
	
	'elForm':null, // available starting with PostRender.
			
   /**
    * @param {TabbedDialog} dialog
    */
	init:function(dialog)
	{
		this.dialog = dialog;
		this.jsID = nextJSID();
		this.formID = null;
		this.apply = false;
		this.rendered = false;
	},
	
   /**
    * Retrieves the title of the tab.
    *
    * @returns {String}
    */
	GetTitle:function()
	{
		return 'Unknown';
	},

   /**
    * Retrieves the tab's alias, used to easily identify tabs, for example
    * when switching to a specific tab in the dialog.
    *
    * @returns {String}
    */
	GetAlias:function()
	{
		throw new ApplicationException(
			t('Invalid dialog configuration'),
			'The method [GetAlias] must be implemented in the extending class.'
		);
	},
	
   /**
    * Retrieves the tab's ID. Every tab gets a unique ID within a request,
    * but which is very likely to change between requests. To identify tabs,
    * consider using the alias instead.
    *
    * @returns {String}
    */
	GetID:function()
	{
		return this.jsID;
	},
	
   /**
    * Retrieves the tab's parent dialog instance.
    *
    * @return {TabbedDialogTab}
    */
	GetDialog:function()
	{
		return this.dialog;
	},
	
   /**
    * Whether the dialog should be wrapped in its own form
    * tag from the get go. This is added automatically.
    * 
    * @returns {Boolean}
    */
	HasForm:function()
	{
		return true;
	},
	
	RenderBody:function(formID)
	{
		// store this for later, since the ID is specific to
		// the dialog, not the tab itself.
		this.formID = formID;
		
		var content = this.Render();
		
		if(this.HasForm()) {
			content = ''+
			'<form id="'+formID+'" class="form-horizontal">'+
				content+
				FormHelper.renderDummySubmit()+
			'</form>';
		}
		
		return content;
	},
	
   /**
    * Renders the HTML for the tab's content. This has to be overwritten
    * by the class that implements the tab. By default this returns an 
    * alert telling the user that the tab has not been implemented yet.
    *  
    * @return {String}
    */
	Render:function()
	{
		return application.renderAlertInfo(t('Not implemented yet.'));
	},
	
   /**
    * Renders the HTML for the tab's footer (where the buttons are).
    * This has to be overwritten by the class implementing the tab,
    * by default this returns an empty string.
    *
    * @return {String}
    */
	RenderFooter:function()
	{
		return '';
	},
	
   /**
    * Processes any tasks that need to be done when the user
    * activates the tab. This has to be implemented by the class
    * implementing the tab, otherwise it does nothing.
    *
    */
	DoActivate:function()
	{
	},
	
   /**
    * Does the required post rendering (attaching events, etc) once the
    * tab's HTML contents have been successfully inserted into the DOM.
    *
    */
	PostRender:function()
	{
		this._PostRender();
		this.rendered = true;
	},
	
	_PostRender:function()
	{
		
	},
	
   /**
    * Checks whether the tab's contents have been rendered and the 
    * post rendering routine has finished as well.
    *
    * @return {Boolean}
    */
	IsRendered:function()
	{
		return this.rendered;
	},
	
	Handle_Submit:function()
	{
		this.log('Tab does not implement the Handle_Submit method.', 'error');
		return false;
	},
	
	elementID:function(part)
	{
		if(typeof(part)=='undefined') {
			return this.jsID;
		}
		
		return this.jsID+'_'+part;
	},
	
	element:function(part)
	{
		var id = this.elementID(part);
		return $('#'+id);
	},
	
	log:function(message, category)
	{
		application.log(
			'Tab ['+this.jsID+'] ['+this.GetTitle()+']',
			message,
			category
		);
	},

   /**
    * Sets this tab as having modifications, which shows 
    * in the tab title.
    *
    */
	SetModified:function()
	{
		this.log('Setting as modified.', 'event');
		this.dialog.SetTabModified(this.GetID());
	},
	
   /**
    * Sets this tab as not having any modifications.
    *
    */
	SetNotModified:function()
	{
		this.log('Setting as not modified.', 'event');
		this.dialog.SetTabNotModified(this.GetID());
	},
	
   /**
    * Checks whether the tab has any modifications. This has to be 
    * overridden by the class implementing the tab to add its own
    * mechanics.
    *
    * @return {Boolean}
    */
	IsModified:function()
	{
		this.log('Tab does not implement the IsModified method.', 'error');
		return false;
	},
	
   /**
    * Checks if the tab's contents have been modified, and displays the
    * modified state in the tab's title accordingly.
    *
    */
	CheckModified:function()
	{
		this.log('Checking if the tab has been modified.', 'event');
		
		// the reason for this is that most tabs rely on form element values
		// to check if the user made any modifications. That cannot work when
		// the tab hasn't been rendered yet, since the HTML elements do not 
		// exist yet. 
		if(!this.IsRendered()) {
			this.log('Tab is not rendered yet, so it is considered not modified.', 'data');
			this.SetNotModified();
			return;
		}
		
		if(this.IsModified()) {
			this.SetModified();
		} else {
			this.SetNotModified();
		}
	},
	
	Handle_Modified:function()
	{
		
	},
	
	Handle_NotModified:function()
	{
		
	},
	
	Handle_ButtonOKClick:function()
	{
		this.log('Clicked the OK button.', 'event');
		
		if(!this.IsModified()) {
			this.log('Tab has not been modified, ignoring.', 'event');
			return;
		}
			 
		this.apply = false;
		this.dialog.Handle_SubmitTab(this.GetID());
	},
	
	Handle_ButtonApplyClick:function()
	{
		this.log('Clicked the apply button.', 'event');
		
		if(!this.IsModified()) {
			this.log('Tab has not been modified, ignoring.', 'event');
			return;
		}
			 
		this.apply = true;
		this.dialog.Handle_SubmitTab(this.GetID());
	},
	
   /**
    * Checks if the tab is in apply mode, as compared to direct 
    * submit mode. The dialog will stay open if this is true.
    *
    * @return {Boolean}
    */
	IsApplyMode:function()
	{
		return this.apply;
	},
	
   /**
    * Submits the tab's form, triggering validation and updating the field
    * with the data if it's valid. Note: this actually ends up calling the
    * Handle_Submit() event handling method.
    *
    */
	SubmitForm:function()
	{
		this.elForm.submit();
	}
};

TabbedDialogTab = Class.extend(TabbedDialogTab);
