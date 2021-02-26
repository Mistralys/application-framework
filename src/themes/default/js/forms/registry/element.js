var FormHelper_Registry_Element = 
{
	'registry':null,
	'id':null,
	'label':null,
	'type':null,
	'sectionID':null,
	'container':null,
		
	init:function(registry, id, label, type, sectionID)
	{
		this.registry = registry;
		this.id = id;
		this.label = label;
		this.type = type;
		this.sectionID = sectionID;
	},
	
	GetID:function()
	{
		return this.id;
	},
	
   /**
    * Retrieves the DOM element for the element's ID. Note that
    * this may not always be a form element, since this depends
    * on the element type and how it renders itself.
    * 
    * @returns {jQuery}
    */
	GetElement:function()
	{
		return $('#'+this.GetID());
	},
	
   /**
    * Retrieves the DIV element for the form row containing the element.
    * @return {jQuery}
    */
	GetContainer:function()
	{
		if(isEmpty(this.container)) {
			this.container = this.GetElement().parents('.control-group').first();
		}
		
		return this.container;
	},
	
	GetLabel:function()
	{
		return this.label;
	},
	
	Focus:function()
	{
		if(this.HasSection()) {
			this.GetSection().Expand();
		}
		
		var el = this.GetElement();
		
		UI.ScrollToElement(el);
		el.focus();
	},
	
	GetType:function()
	{
		return this.type;
	},
	
	HasSection:function()
	{
		return !isEmpty(this.sectionID);
	},
	
	GetSection:function()
	{
		if(this.HasSection()) {
			return this.registry.GetSection(this.sectionID);
		}
		
		return null;
	},
	
	GetSectionLabel:function()
	{
		if(this.HasSection()) {
			return this.GetSection().GetLabel();
		}
		
		return '';
	}
};

FormHelper_Registry_Element = Class.extend(FormHelper_Registry_Element);
