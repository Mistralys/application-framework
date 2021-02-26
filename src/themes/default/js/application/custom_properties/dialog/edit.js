var Application_CustomProperties_Dialog_Edit = 
{
	_init:function()
	{
		this._super();
		this.edit = true;
	},

	_Handle_Shown:function(property)
	{
		this._super();
		this.SetAbstract(
			t(
				'Here you can edit the settings of the %1$s property %2$s.', 
				this.collection.GetTypeNameSingular(),
				this.property.GetLabel()
			) + ' ' +
			t('The changes will be active as soon as you confirm the form.')
		);
	}
};

Application_CustomProperties_Dialog_Edit = Application_CustomProperties_Dialog_Add.extend(Application_CustomProperties_Dialog_Edit);

var Application_CustomProperties_Dialog_Value = 
{
	_init:function()
	{
		this._super();
		this.edit = true;
		this.valueOnly = true;
	},

	_Handle_Shown:function(property)
	{
		this._super();
		this.SetAbstract(
			t(
				'Here you can edit the value of the %1$s property %2$s.', 
				this.collection.GetTypeNameSingular(),
				this.property.GetLabel()
			) + ' ' +
			t('The changes will be active as soon as you confirm the form.')
		);
	}
};

Application_CustomProperties_Dialog_Value = Application_CustomProperties_Dialog_Add.extend(Application_CustomProperties_Dialog_Value);