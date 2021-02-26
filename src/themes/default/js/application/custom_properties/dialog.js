var Application_CustomProperties_Dialog = 
{
	'collection':null,
	
	init:function(collection)
	{
		this.collection = collection;
		this._super();

		this.SetIcon(UI.Icon().Properties());
	},
	
	_init:function()
	{
		this.AddScreen(new Application_CustomProperties_Dialog_List(this, 'list'));
		this.AddScreen(new Application_CustomProperties_Dialog_Add(this, 'add'));
		this.AddScreen(new Application_CustomProperties_Dialog_Edit(this, 'edit'));
		this.AddScreen(new Application_CustomProperties_Dialog_Value(this, 'value'));
		this.AddScreen(new Application_CustomProperties_Dialog_Delete(this, 'delete'));
	},
	
	_GetTitle:function()
	{
		return t('Manage %1$s custom properties', this.collection.GetTypeNameSingular());
	},
	
   /**
    * @return {Application_CustomProperties_Collection}
    */
	GetCollection:function()
	{
		return this.collection;
	},
	
	DialogAddProperty:function()
	{
		this.ShowScreen('add');
	}
};

Application_CustomProperties_Dialog = Dialog_Screened.extend(Application_CustomProperties_Dialog);