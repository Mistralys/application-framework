var FormHelper_Registry = 
{
	'sections':null,
	'elements':null,
	
	init:function()
	{
		this.sections = [];
		this.elements = [];
	},
	
	AddSection:function(id, label)
	{
		var section = new FormHelper_Registry_Section(this, id, label);
		this.sections.push(section);
		return section;
	},
	
	AddElement:function(id, label, type, sectionID)
	{
		var element = new FormHelper_Registry_Element(this, id, label, type, sectionID);
		this.elements.push(element);
		return element;
	},
	
	GetElementByID:function(id)
	{
		for(var i=0; i<this.elements.length; i++)
		{
			if(this.elements[i].GetID() == id)
			{
				return this.elements[i];
			}
		}
		
		return null;
	},
	
	GetSection:function(id)
	{
		var found = null;
		$.each(this.sections, function(idx, section) {
			if(section.GetID() == id) {
				found = section;
				return false;
			}
		});
		
		return found;
	},
	
   /**
    * Retrieves information on all form elements in the registry.
    * @returns {FormHelper_Registry_Element[]}
    */
	GetElements:function()
	{
		return this.elements;
	},
	
   /**
    * Retrieves information about all form sections in the registry.
    * @returns {FormHelper_Registry_Section[]}
    */
	GetSections:function()
	{
		return this.sections;
	},
	
   /**
    * Attempts to retrieve a section by its label.
    * 
    * @param {String} label
    * @returns {FormHelper_Registry_Section|NULL}
    */
	GetSectionByLabel:function(label)
	{
		var found = null;
		$.each(this.sections, function(idx, section) {
			if(section.GetLabel() == label) {
				found = section;
				return false;
			}
		});
		
		return found;
	}
};

FormHelper_Registry = Class.extend(FormHelper_Registry);
