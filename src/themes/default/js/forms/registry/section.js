var FormHelper_Registry_Section = 
{
	'registry':null,
	'id':null,
	'label':null,
	
	init:function(registry, id, label)
	{
		this.registry = registry;
		this.id = id;
		this.label = label;
	},
	
	GetSection:function()
	{
		return UI.GetSection(this.id);
	},
	
	Expand:function()
	{
		this.GetSection().Expand();
	},
	
	Collapse:function()
	{
		this.GetSection().Collapse();
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	GetLabel:function()
	{
		return this.label;
	},
	
   /**
    * Retrieves all elements that are contained within this form section.
    * @returns {FormHelper_Registry_Element[]}
    */
	GetElements:function()
	{
		var els = this.registry.GetElements();
		var found = [];
		var sectionID = this.id;
		$.each(els, function(idx, element) {
			if(element.GetSectionID() == sectionID) {
				found.push(element);
			}
		});
		
		return found;
	}
};

FormHelper_Registry_Section = Class.extend(FormHelper_Registry_Section);
