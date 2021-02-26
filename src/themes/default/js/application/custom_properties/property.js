var Application_CustomProperties_Property = 
{
	'collection':null,
	'data':null,
	'texts':null,

	init:function(collection, property_id, label, name, value, is_structural, default_value, preset_id)
	{
		this.collection = collection;
		
		this.texts = {
			'label':null,
			'value':null,
			'name':null
		};
		
		this.data = {
			'property_id':property_id,
			'label':label,
			'name':name,
			'value':value,
			'is_structural':is_structural,
			'default_value':default_value,
			'preset_id':preset_id
		};
	},
	
	GetID:function()
	{
		return this.GetDataKey('property_id');
	},
	
	GetLabel:function()
	{
		return this.GetDataKey('label');
	},
	
	GetLabelForList:function()
	{
		if(this.texts.label == null) {
			this.texts.label = UI.Text(this.RenderListLabel()).MakeLink();
		}
		
		return this.texts.label;
	},
	
	RenderListLabel:function()
	{
		return this.GetLabel();
	},

	GetName:function()
	{
		return this.GetDataKey('name');
	},
	
	GetNameForList:function()
	{
		if(this.texts.name == null) {
			this.texts.name = UI.Text(this.RenderListName());
		}
		
		return this.texts.name;
	},
	
	RenderListName:function()
	{
		return this.GetName();
	},
	
	GetValue:function()
	{
		return this.GetDataKey('value');
	},
	
	IsStructural:function()
	{
		return this.GetDataKey('is_structural');
	},
	
   /**
    * @protected
    * @return mixed
    */
	GetDataKey:function(name)
	{
		if(typeof(this.data[name]) != 'undefined') {
			return this.data[name];
		}
		
		return null;
	},
	
	GetValueForList:function()
	{
		if(this.texts.value == null) {
			this.texts.value = UI.Text(this.RenderListValue()).MakeLink();
		}
		
		return this.texts.value;
	},
	
	RenderListValue:function()
	{
		var value = this.GetValue();
		if(isEmpty(value)) 
		{
			value = '<i class="muted">('+t('empty value')+')</i>';
		} 
		else 
		{
			value = strip_tags(value);
			var maxlen = 16;
			
			if(value.length > maxlen) {
				value = value.substring(0, maxlen) + ' <span class="muted">[...]</span>';
			}
		}
		
		return value;
	},
	
	RenderListActions:function(listScreen)
	{
		var property = this;
		
		var menu = UI.DropMenu('')
		.MakeMini()
		.NoCaret()
		.MakeRightAligned()
		.MakeDropup()
		.SetIcon(UI.Icon().Settings());
		
		menu.AddItem(t('Edit value'))
		.SetIcon(UI.Icon().Edit())
		.Click(function() {
			listScreen.DialogEditValue(property);
		});
		
		menu.AddItem(t('Edit settings'))
		.SetIcon(UI.Icon().Settings())
		.Click(function() {
			listScreen.DialogEditSettings(property);
		});
		
		menu.AddSeparator();
		
		menu.AddItem(t('Delete...'))
		.SetIcon(UI.Icon().DeleteSign())
		.MakeDangerous()
		.Click(function() {
			listScreen.DialogDeleteProperty(property);
		});
		
		return menu.Render();
	},
	
	Serialize:function()
	{
		return this.data;
	},
	
	UpdateFromRequest:function(data)
	{
		this.log('Updating from request.', 'data');
		
		this.data = data;
		this.data.is_structural = string2bool(data.is_structural);
		
		this.texts.label.SetText(this.RenderListLabel());
		this.texts.value.SetText(this.RenderListValue());
		this.texts.name.SetText(this.RenderListName());
	},
	
	log:function(message, category)
	{
		application.log(
			'CustomProperties ['+this.collection.GetOwnerType()+'] | ' + this.collection.GetOwnerKey() + ' | Property [' + this.GetID() + ']',
			message,
			category
		);
	}
};

Application_CustomProperties_Property = Class.extend(Application_CustomProperties_Property);