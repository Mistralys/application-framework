var Application_ListFilters_Dialog_Save = 
{
	'filters':null,
		
	init:function(filters)
	{
		this._super();
		
		this.filters = filters;
	},
		
	_RenderAbstract:function()
	{
		return ''+ 
		t('This will save the current product list filter in your user settings.')+' '+
		t('You will be able to load it again anytime you like.')+' '+
		t('Note:')+' '+t('To replace an existing filter, simply give it the same name.');
	},
	
	_RenderBody:function()
	{
		var html = ''+
		'<form class="form-horizontal" id="'+this.elementID('form')+'">'+
			FormHelper.renderItem(
				t('Label'),
				this.elementID('label'),
				'<input type="text" id="'+this.elementID('label')+'" value="" class="input-large"/>',
				true,
				t('A short label to recognize the filter by.')+' '+
				t('%1$s to %2$s characters.', 2, 60)+' '+
				FormHelper.getValidationHint_label()
			)+
			FormHelper.renderDummySubmit()+
		'</form>';
		
		return html;
	},
	
	_PostRender:function()
	{
		var dialog = this;
		this.element('form').submit(function() {
			dialog.Handle_Save();
			return false;
		});
	},
	
	_RenderFooter:function()
	{
		var dialog = this;
		
		this.AddButtonRight(
			UI.Button(t('Save now'))
			.SetIcon(UI.Icon().Save())
			.MakePrimary()
			.SetLoadingText(t('Saving...'))
			.Click(function() {
				dialog.Handle_Save();
			}),
			'save'
		);
		
		this.AddButtonCancel();
	},
	
	GetTitle:function()
	{
		return UI.Icon().Filter() + ' ' + t('Save the current filter');
	},
	
	_Handle_Shown:function()
	{
		this.element('label').focus();
	},
	
	Lock:function()
	{
		this.GetButton('save').Loading();
	},
	
	Unlock:function()
	{
		this.GetButton('save').Reset();
	},
	
	Handle_Save:function()
	{
		FormHelper.resetErrorStati(this.elementID('label'));
		
		var label = trim(this.element('label').val());
		if(!FormHelper.validate_label(label)) {
			FormHelper.makeError(this.elementID('label'), t('Must be a valid alias.'));
			return;
		}
		
		this.Lock();
		
		var settings = {};
		var defs = this.filters.GetDefinitions();
		var dialog = this;
		$.each(defs, function(setting, def) {
			var value = FormHelper.getElementValue(def.elementID);
			settings[setting] = value;
		});
		
		var payload = {
			'label':label,
			'settings_id':this.filters.GetID(),
			'settings':settings
		};
		
		application.AJAX(
			'SaveListFilter', 
			payload, 
			function(data) {
				dialog.Handle_SaveSuccess(data);
			}, 
			function(errorText, data) {
				dialog.Handle_SaveFailure(errorText, data);
			}
		);
	},
	
	Handle_SaveSuccess:function(data)
	{
		this.element('label').val('');
		
		this.Unlock();
		this.Hide();
		
		if(data.was_new) {
			message = t('The filter %1$s has been added successfully at %2$s.', data.label, application.getCurrentTime());
		} else {
			message = t('The filter %1$s has been overwritten successfully at %2$s.', data.label, application.getCurrentTime());
		}
		
		application.displaySuccessMessage(message, true);
		
		this.filters.Handle_PresetAdded(data.id, data.label, data.settings);
	},
	
	Handle_SaveFailure:function(errorText, data)
	{
		this.Unlock();
		
		this.ShowAlertError(
			UI.Icon().Warning() + ' ' +
			t('Could not save the filter.') + ' ' +
			t('Reason given:') + ' ' +
			errorText
		);
		
		this.element('label').focus();
	}
};

Application_ListFilters_Dialog_Save = Dialog_Basic.extend(Application_ListFilters_Dialog_Save);