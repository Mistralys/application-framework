var Driver = 
{
	'version':null, // Set serverside.
	
	GetVersion:function()
	{
		return this.version;
	},
		
   /**
    * Shows the lookup dialog to search for searchable
    * items in the application.
    * 
    * @param string[] preselect List of lookup item IDs to preselect.
    */	
	DialogLookup:function(preselect)
	{
		application.loadScript(
			'application/dialog/lookup_items.js',
			function() {
				var dialog = new Application_Dialog_LookupItems();
				dialog.SetPreselect(preselect);
				dialog.Show();
			}
		);
	}
};