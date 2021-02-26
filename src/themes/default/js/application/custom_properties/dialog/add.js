var Application_CustomProperties_Dialog_Add = 
{
	'ERROR_CANNOT_SEND_DATA':16801,
		
	'collection':null,
	'edit':null,
	'valueOnly':null,
	'submitting':null,
	'property':null,
	
	_init:function()
	{
		this.collection = this.dialog.GetCollection();
		this.edit = false;
		this.valueOnly = false;
		this.submitting = false;
		this.property = null;
	},
	
	_RenderAbstract:function()
	{
		return t('Here you can add a new property to the %1$s.', this.collection.GetTypeNameSingular()) + ' ' +
		t('It will be active as soon as you confirm the form.');
	},
	
	_RenderBody:function()
	{
		this.CreateForm();
		
		return this.form.Render();
	},
	
	CreateForm:function()
	{
		var screen = this;
		var form = FormHelper.createForm(this.elementID('form'));
		
		if(!this.valueOnly) {
			var label = form.AddLabel();
			label.MakeRequired();
			
			var name = form.AddAlias('name')
			.MakeRequired()
			.AppendGenerateAliasButton(label);
	
			if(this.collection.IsPublishable()) {
				var struct = form.AddSwitch('is_structural', t('Is structural?'));
				struct.SetHelpText(t('Whether changing the value of the property affects the publishing of the %1$s.', this.collection.GetTypeNameSingular()));
			}
		}
		
		if(!this.edit || this.valueOnly) {
			var value = form.AddTextarea('value', t('Value'))
			.SetSize(5)
			.MakeWidthXXL()
			.SetHelpText(
				t('The value of the property in conjunction with this %1$s.', this.collection.GetTypeNameSingular()) + ' ' +
				t('Note:') + ' ' +
				t('The value is entirely freeform.') + ' ' +
				t('HTML code is allowed, but must be entered manually and is not validated.')
			);
		}
		
		form.Submit(function() {
			screen.Handle_Submit();
		});
		
		this.form = form;
		
		if(this.property != null) {
			this.form.SetValues(this.property.Serialize());
		}
	},
	
	_Handle_Shown:function()	
	{
		var elName = 'label';
		if(this.valueOnly) {
			elName = 'value';
		}
		
		this.form.FocusElement(elName);
	},
	
	_Handle_Buttons_Right:function()
	{
		var screen = this;
		var label = null;
		var icon = null;
		
		if(this.edit) {
			label = t('Save now');
			icon = UI.Icon().Add();
		} else {
			label = t('Add now');
			icon = UI.Icon().Save();
		}
		
		this.AddButton(
			UI.Button(label)
			.SetIcon(icon)
			.MakePrimary()
			.Click(function() {
				screen.form.Submit();
			}),
			'submit'
		);
		
		this.AddButton(
			UI.Button(t('Cancel'))
			.SetIcon(UI.Icon().Cancel())
			.Click(function() {
				screen.ShowScreen('list');
			}),
			'cancel'
		);
	},
	
	Handle_Submit:function()
	{
		if(this.submitting) {
			return;
		}
		
		this.submitting = true;

		this.dialog.Hide();
		
		application.showLoader(t('Please wait, processing...'));
		
		var screen = this;
		var values = this.form.GetValues();
		var method = 'CustomPropertyAdd';
		var payload = {
			'owner_type':this.collection.GetOwnerType(),
			'owner_key':this.collection.GetOwnerKey()
		};
		
		if(!this.valueOnly) {
			payload['label'] = values.label;
			payload['name'] = values.name;
			payload['is_structural'] = false;
			
			if(this.collection.IsPublishable()) {
				payload.is_structural = string2bool(values.is_structural);
			}
		};
		
		if(!this.edit || this.valueOnly) {
			payload['value'] = values.value;
		}
		
		if(this.edit) {
			method = 'CustomPropertySave';
			payload['property_id'] = this.property.GetID();
			payload['value_only'] = this.valueOnly;
		} 
		
		application.createAJAX(method)
		.SetPayload(payload)
		.Error(t('Could not process the property data.'), this.ERROR_CANNOT_SEND_DATA)
		.Retry(function() {
			screen.dialog.Show();
		})
		.Always(function() {
			screen.submitting = false;
			application.hideLoader();
		})
		.Success(function(data) {
			screen.Handle_SubmitSuccess(data);
		})
		.Send();
	},
	
	Handle_SubmitSuccess:function(data)
	{
		this.form.Reset();
		
		this.dialog.Show();
		this.ShowScreen('list');
		
		if(this.edit) {
			return this.Handle_EditProperty(data);
		} 

		return this.Handle_AddProperty(data);
	},
	
	Handle_EditProperty:function(data)
	{
		this.collection.UpdateFromRequest(data);

		this.property.UpdateFromRequest(data.edited);
	},
	
	Handle_AddProperty:function(data)
	{
		this.collection.UpdateFromRequest(data);
		
		var property = this.collection.RegisterProperty(
			data.added.property_id, 
			data.added.label, 
			data.added.name, 
			data.added.value, 
			string2bool(data.added.is_structural), 
			data.added.default_value, 
			data.added.preset_id
		);
			
		this.GetScreen('list').Handle_PropertyAdded(property);
	},
	
	SetProperty:function(property)
	{
		this.property = property;
		
		if(this.form != null) {
			this.form.SetValues(property.Serialize());
		}
		
		return this;
	}
};

Application_CustomProperties_Dialog_Add = Dialog_Screened_Screen.extend(Application_CustomProperties_Dialog_Add);