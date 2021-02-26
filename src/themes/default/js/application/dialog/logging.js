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
var Application_Dialog_Logging = 
{
	'form':null,
	
	_init:function()
	{
		this.form = null;

		var dialog = this;
		
		this.AddButtonLeft(
			UI.Button(t('Select all'))
			.SetIcon(UI.Icon().SelectAll())
			.Click(function() {
				dialog.Handle_SelectAll();
			})
		);

		this.AddButtonLeft(
			UI.Button(t('Deselect all'))
			.SetIcon(UI.Icon().DeselectAll())
			.Click(function() {
				dialog.Handle_DeselectAll();
			})	
		);
	},
		
	_GetTitle:function()
	{
		return t('Clientside logging settings');
	},
	
	_RenderBody:function()
	{
		var categories = array_values(application.loggingCategories);
		categories.sort(function(a, b) {
			return naturalSort(a.label, b.label);
		});
		
		var dialog = this;
		var form = FormHelper.createForm('logging_settings');
		
		for(var i=0; i<categories.length; i++) {
			category = categories[i];
			form.AddSwitch(category.name, '<code>' + category.name + '</code>')
			.SetHelpText(category.label);
		}
		
		this.form = form;
		this.RefreshValues();
		
		return form.Render();
	},
	
	Handle_SelectAll:function()
	{
		var els = this.form.GetElements();
		$.each(els, function(idx, element) {
			if(element.GetElementType() == 'Switch') {
				element.TurnOn();
			}
		});
	},
	
	Handle_DeselectAll:function()
	{
		var els = this.form.GetElements();
		$.each(els, function(idx, element) {
			if(element.GetElementType() == 'Switch') {
				element.TurnOff();
			}
		});
	},
	
	_RenderAbstract:function()
	{
		return t('Choose which types of logging messages to display in the browser\'s console.')+' '+
		t('Note:')+' '+t('These settings are persisted using cookies.');
	},
	
	_RenderFooter:function()
	{
		var dialog = this;
		
		return UI.Button(t('Save settings'))
			.SetIcon(UI.Icon().Save())
			.MakePrimary()
			.Click(function() {
				dialog.Handle_Submit();
			})+
		DialogHelper.renderButton_close();
	},
	
   /**
    * Updates the current settings every time the dialog is shown.
    */
	_Handle_Shown:function()
	{
		this.RefreshValues();
	},
	
	RefreshValues:function()
	{
		if(this.form != null) {
			var form = this.form;
			$.each(application.loggingCategories, function(name, def) {
				var el = form.GetElementByName(name);
				el.SetValue(application.isLoggingActive(name));
			});
		}
	},
	
	Handle_Submit:function()
	{
		var form = this.form;
		
		$.each(application.loggingCategories, function(name, def) {
			var el = form.GetElementByName(name);
			application.setLoggingFor(name, el.IsChecked());
		});
		
		this.Hide();
	}
};

Application_Dialog_Logging = Dialog_Basic.extend(Application_Dialog_Logging);