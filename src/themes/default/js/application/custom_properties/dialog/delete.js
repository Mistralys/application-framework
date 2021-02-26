var Application_CustomProperties_Dialog_Delete = 
{
	'ERROR_CANNOT_DELETE_PROPERTY':17001,
		
	'collection':null,
	'property':null,

	_init:function()
	{
		this.collection = this.dialog.GetCollection();
	},
	
	_RenderAbstract:function()
	{
		return '';
	},

	_RenderBody:function()
	{
		return '';
	},
	
	SetProperty:function(property)
	{
		this.property = property;
	},
	
	_Handle_Shown:function()
	{
		this.SetAbstract(
			t(
				'This will delete the %1$s custom property %2$s.', 
				this.collection.GetTypeNameSingular(), 
				'<b>' + this.property.GetLabel() + '</b>'
			) +
			' ' +
			t('The change will be applied immediately.')
		);
		
		this.ChangeBody(
			application.renderAlertWarning(
				UI.Icon().Warning() + ' ' +
				'<b>' + t('This cannot be undone, are you sure?') + '</b>'
			)
		);
	},
	
	_Handle_Buttons_Right:function()
	{
		var screen = this;
		
		this.AddButton(
			UI.Button(t('Delete now'))
			.MakeDangerous()
			.SetIcon(UI.Icon().Delete())
			.Click(function() {
				screen.ConfirmDelete();
			}),
			'delete'
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
	
	ConfirmDelete:function()
	{
		this.dialog.Hide();
		var screen = this;
		var property = this.property;
		
		application.showLoader(t('Please wait, deleting...'));
		
		var payload = {
			'owner_key':this.collection.GetOwnerKey(),
			'owner_type':this.collection.GetOwnerType(),
			'property_id':this.property.GetID()
		};
		
		application.createAJAX('CustomPropertyDelete')
		.SetPayload(payload)
		.Error(t('Cannot delete the custom property.'), this.ERROR_CANNOT_DELETE_PROPERTY)
		.Success(function(data) {
			screen.Handle_DeleteSuccess(data);
		})
		.Always(function() {
			application.hideLoader();
		})
		.Retry(function() {
			screen.dialog.Show();
		})
		.Send();
	},
	
	Handle_DeleteSuccess:function(data)
	{
		this.collection.UpdateFromRequest(data);
		this.collection.RemoveProperty(this.property);
		
		this.dialog.Show();
		
		this.GetScreen('list').Handle_PropertyDeleted(this.property);
		
		this.ShowScreen('list');
		
		this.dialog.ShowAlertSuccess(
			UI.Icon().Information() + ' ' +
			t(
				'The custom property %1$s has been deleted successfully at %2$s.',
				this.property.GetLabel(),
				application.getCurrentTime()
			)
		);
	}
};

Application_CustomProperties_Dialog_Delete = Dialog_Screened_Screen.extend(Application_CustomProperties_Dialog_Delete);