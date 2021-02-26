var Countries =
{
	'items':[],
	
	Register:function(id, iso, label)
	{
		var country = new Countries_Country(id, iso, label);
		this.items.push(country);
		return country;
	},
	
   /**
    * Retrieves all available country instances.
    * @returns {Countries_Country[]}
    */
	GetAll:function()
	{
		return this.items;
	},
	
   /**
    * Retrieves a country instance by its ID.
    * @param {Integer} id
    * @returns {Countries_Country}
    */
	GetByID:function(id)
	{
		var found = null;
		$.each(this.items, function(idx, item) {
			if(item.GetID() == id) {
				found = item;
				return false;
			}
		});
		
		return found;
	},
	
   /**
    * Displays a select items dialog to choose one or more countries.
    * 
    * @param {Function} confirmHandler Called when the user confirms his selection. Provides an indexed array with country instances.
    * @param {Function} cancelHandler Called when the user closes the selection without selecting anything.
    * @param {Integer[]} limitIDs Indexed array with country IDs to limit the selection to
    */
	DialogSelect:function(confirmHandler, cancelHandler, limitIDs)
	{
		if(isEmpty(limitIDs)) {
			limitIDs = [];
		}
		
		var select = application.createDialogSelectItems()
		.SetItemLabel(t('Country'), t('Countries'))
		.SetNoAutoRemove()
		.SetNoDescription()
		.SetConfirmHandler(function(countryIDs) {
			var countries = [];
			$.each(countryIDs, function(idx, country_id) {
				country = Countries.GetByID(country_id);
				if(country) {
					countries.push(country);
				}
			});
			
			confirmHandler.call(undefined, countries);
		})
		.SetCancelHandler(cancelHandler);
		
		var countries = this.GetAll();
		$.each(countries, function(idx, country) {
			if(!isEmpty(limitIDs) && !in_array(country.GetID(), limitIDs)) {
				return;
			}
			select.AddItem(country.GetID(), country.GetLabel());
		});
		
		select.Show();
	}
};

var Countries_Country = 
{
	init:function(id, iso, label)
	{
		this.id = id;
		this.iso = iso;
		this.label = label;
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	GetLabel:function()
	{
		return this.label;
	},
	
	GetISO:function()
	{
		return this.iso;
	}
};

Countries_Country = Class.extend(Countries_Country);