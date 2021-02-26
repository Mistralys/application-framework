var Application_CustomProperties_Collection = 
{
	'ownerType':null,
	'ownerKey':null,
	'properties':null,
	'isPublishable':null,
	'dialog':null,

	init:function(ownerType, ownerKey, typeNameSingular, typeNamePlural, isPublishable)
	{
		this.ownerType = ownerType;
		this.ownerKey = ownerKey;
		this.typeNameSingular = typeNameSingular;
		this.typeNamePlural = typeNamePlural;
		this.isPublishable = isPublishable;
		this.properties = [];
		this.dialog = null;
	},
	
	GetOwnerType:function()
	{
		return this.ownerType;
	},
	
	GetOwnerKey:function()
	{
		return this.ownerKey;
	},
	
	DialogManage:function()
	{
		if(this.dialog == null) {
			this.dialog = new Application_CustomProperties_Dialog(this);
		}
		
		this.dialog.Show();
	},
	
   /**
    * Registers an available property: creates the instance,
    * adds it to the collection and returns it.
    * 
    * @return {Application_CustomProperties_Property}
    */
	RegisterProperty:function(property_id, label, name, value, is_structural, default_value, preset_id)
	{
		var property = new Application_CustomProperties_Property(
			this,
			property_id,
			label,
			name,
			value,
			is_structural,
			default_value,
			preset_id
		);
		
		this.properties.push(property);
		return property;
	},
	
   /**
    * Retrieves all registered properties.
    * 
    * @return {Application_CustomProperties_Property}
    */
	GetAll:function()
	{
		return this.properties;
	},
	
	GetTypeNameSingular:function()
	{
		return this.typeNameSingular;
	},
	
	GetTypeNamePlural:function()
	{
		return this.typeNamePlural;
	},
	
	IsPublishable:function()
	{
		return this.isPublishable;
	},
	
	UpdateFromRequest:function(requestData)
	{
		this.log('Updating from request data.', 'data');
		
		if(this.ownerKey != requestData.owner_key) {
			this.log('The owner key has changed from ['+this.ownerKey+'] to ['+requestData.owner_key+'], updating.', 'data');
			this.ownerKey = requestData.owner_key;
		}
	},
	
	GetByID:function(property_id)	
	{
		var found = null;
		$.each(this.properties, function(idx, property) {
			if(property.GetID() == property_id) {
				found = property;
				return false;
			}
		});
		
		return found;
	},
	
	RemoveProperty:function(property)
	{
		var keep = [];
		$.each(this.properties, function(idx, prop) {
			if(prop.GetID() != property.GetID()) {
				keep.push(prop);
			}
		});
		
		this.properties = keep;
	},
	
	log:function(message, category)
	{
		application.log(
			'CustomProperties ['+this.ownerType+'] | ' + this.ownerKey,
			message, 
			category
		);
	}
};

Application_CustomProperties_Collection = Class.extend(Application_CustomProperties_Collection);