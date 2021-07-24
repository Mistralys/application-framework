var Driver = 
{
	'version':null, // Set serverside.
	'notepad':null,
	
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
	},

	/**
	 * Displays the Notepad UI to take notes that
	 * are stored in the user's account.
	 */
	DialogNotepad:function()
	{
		var driver = this;

		application.loadScripts(
		[
				'application/notepad.js',
				'application/notepad/note.js'
			],
			function() {
				driver.Handle_NotepadLoaded();
			}
		);
	},

	Handle_NotepadLoaded:function()
	{
		if(this.notepad === null)
		{
			this.notepad = new Application_Notepad();
		}

		this.notepad.Open();
	}
};