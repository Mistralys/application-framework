var Application_CustomProperties_Dialog_List = 
{
	'collection':null,
	'grid':null,

	_init:function()
	{
		this.collection = this.dialog.GetCollection();
		
		this.CreateDataGrid();
	},
	
	_RenderAbstract:function()
	{
		return ''+
		t('Use this to attach any custom data that is also included in exports (provided the selected export supports custom properties).') + ' ' +
		t('Note:') + ' ' +
		t('Any changes you make here are applied immediately.')
	},

	_RenderBody:function()
	{
		return this.grid.Render();
	},
	
	_Handle_Buttons_Left:function()
	{
		var screen = this;
		
		this.AddButton(
			UI.Button(t('Add property...'))
			.SetIcon(UI.Icon().Add())
			.SetTooltip(t('Add a new property to the %1$s.', this.collection.GetTypeNameSingular()))
			.Click(function() {
				screen.DialogAddProperty();
			}),
			'addprop'
		);
		
		/*
		 * FIXME implement this
		this.AddButton(
			UI.Button(t('Add from preset'))
		);
		
		
		this.AddButton(
			UI.Button(t('Manage presets...'))
			.SetIcon(UI.Icon().Presets())
			.SetTooltip(t('Property presets for all %1$s.', this.collection.GetTypeNamePlural()))
			.Click(function() {
				screen.DialogPresets();
			}),
			'managepresets'
		);
		
		*/
	},
	
	_Handle_Buttons_Right:function()
	{
		var screen = this;
		
		this.AddButton(
			UI.Button(t('Close'))
			.SetIcon(UI.Icon().Close())
			.Click(function() {
				screen.HideDialog();
			})
		);
	},
	
	DialogAddProperty:function()
	{
		this.dialog.DialogAddProperty();
	},
	
	DialogPresets:function()
	{
		application.dialogNotImplemented();
	},
	
	CreateDataGrid:function()
	{
		var screen = this;
		
		var grid = UI.DataGrid(this.elementID('grid'));
		grid.SetPrimaryName('property_id');
		grid.EnableCompactMode();
		grid.EnableHover();
		grid.AddColumn('label', t('Label'));
		grid.AddColumn('name', t('Alias'));
		grid.AddColumn('value', t('Value'));
		grid.AddColumn('actions', '').RoleActions().MakeCompact();
		grid.RowClicked(function(entry, column) {
			screen.Handle_PropertyClicked(entry, column);
		});
		
		var props = this.collection.GetAll();
		var screen = this;
		
		$.each(props, function(idx, property) {
			grid.RegisterEntry({
				'property_id':property.GetID(),
				'name':property.GetNameForList(),
				'label':property.GetLabelForList(),
				'value':property.GetValueForList(),
				'actions':property.RenderListActions(screen)
			})
			.SetTag('property', property);
		});
		
		this.grid = grid;
	},
	
	Handle_PropertyClicked:function(entry, column)
	{
		var property = entry.GetTag('property');
		
		if(column.GetName() == 'value') {
			this.DialogEditValue(property);
		} else {
			this.DialogEditSettings(property);
		}
	},
	
	Handle_PropertyAdded:function(property)
	{
		this.grid.AppendEntry({
			'property_id':property.GetID(),
			'name':property.GetNameForList(),
			'label':property.GetLabelForList(),
			'value':property.GetValueForList(),
			'actions':property.RenderListActions(screen)
		})
		.SetTag('property', property);
	},
	
	DialogEditValue:function(property)
	{
		this.GetScreen('value').SetProperty(property);
		this.ShowScreen('value');
	},
	
	DialogEditSettings:function(property)
	{
		this.GetScreen('edit').SetProperty(property);
		this.ShowScreen('edit');
	},
	
	DialogDeleteProperty:function(property)
	{
		this.GetScreen('delete').SetProperty(property);
		this.ShowScreen('delete');
	},
	
	Handle_PropertyDeleted:function(property)
	{
		var entries = this.grid.GetEntries();
		var found = null;
		$.each(entries, function(idx, entry) {
			var prop = entry.GetTag('property');
			if(prop && prop.GetID() == property.GetID()) {
				found = entry; 
				return false;
			}
		});
		
		if(found) {
			this.grid.RemoveEntry(found);
		}
	}
};

Application_CustomProperties_Dialog_List = Dialog_Screened_Screen.extend(Application_CustomProperties_Dialog_List);