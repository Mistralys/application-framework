/**
 * Handles the dialog for developer settings regarding
 * clientside logging: lets the use choose which types
 * of logging messages should be shown in the browser's
 * console window.
 * 
 * @package Application
 * @subpackage Dialogs
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var Application_Dialog_SaveComments = 
{
	_GetTitle:function()
	{
		return t('Confirm changes');
	},
	
	_RenderBody:function()
	{
		var html = ''+
		'<form>'+
			FormHelper.renderItem(
				null,
				this.elementID('comments'),
				'<textarea rows="3" cols="53" class="input-block" id="'+this.elementID('comments')+'"></textarea>',
				true,
				'<span id="'+this.elementID('statusbar')+'"></span>'
			)+
		'</form>';
			
		return html;
	},
	
	_PostRender:function()
	{
		var dialog = this;
		
		this.element('comments').on('keyup', function() {
			dialog.Handle_CommentsChanged();
		});
		
		this.element('comments').on('change', function() {
			dialog.Handle_CommentsChanged();
		});
		
		this.Validate();
	},
	
	Handle_CommentsChanged:function()
	{
		this.Validate();
	},
	
	_RenderAbstract:function()
	{
		return this.message;
	},
	
	_RenderFooter:function()
	{
		var dialog = this;
		
		return UI.Button(t('Confirm'))
			.SetID(this.elementID('btn_confirm'))
			.SetIcon(UI.Icon().Save())
			.MakePrimary()
			.Click(function() {
				dialog.Handle_Submit();
			})+
		DialogHelper.renderButton_close(t('Cancel'));
	},
	
   /**
    * Updates the current settings every time the dialog is shown.
    */
	_Handle_Shown:function()
	{
		FormHelper.focusField(this.element('comments'));
	},
	
	_init:function()
	{
		this.message = null;
		this.confirmHandler = null;
	},
	
	Handle_Submit:function()
	{
		var comments = this.Validate();
		if(comments) {
			this.log('Comments are valid: [' + comments + '].', 'data');
			this.log('Calling the confirm handler.', 'event');
			this.Hide();
			this.confirmHandler.call(undefined, comments);
			return;
		}
		
		this.log('Comments are not valid, ignoring submit.', 'data');
	},
	
	'validationMessage':null,
	
	Validate:function()
	{
		var comments = trim(this.element('comments').val());
		var min = 6;
		if(comments.length < min) {
			this.element('btn_confirm').addClass('disabled');
			this.element('statusbar').html(
				UI.Icon().Warning().MakeDangerous() + ' ' +
				'<span class="text-error">' + t('%1$s characters minimum.', min) + '</span>'
			);
			return false;
		}
		
		this.element('btn_confirm').removeClass('disabled');
		this.element('statusbar').html(
			UI.Icon().OK().MakeSuccess().Render() + ' ' +
			t('%1$s characters total.', comments.length)
		);
		
		return comments;
	},
	
	'message':null, 
	
	SetMessage:function(message)
	{
		this.message = message;
		return this;
	},
	
	'confirmHandler':null,
	
	SetConfirmHandler:function(handler)
	{
		this.confirmHandler = handler;
		return this;
	}
};

Application_Dialog_SaveComments = Dialog_Basic.extend(Application_Dialog_SaveComments);