var Changelog =
{
	'Revisionable':{
		'Label':null, // set serverside
		'TypeName':null, // set serverside
		'PrettyRevision':null, // set serverside
		'CurrentRevision':null, // set serverside
		'LatestRevision':null, // set serverside
		'Table':null, // set serverside
		'OwnerPrimary':null // set serverside
	},
	
	'dialogSwitchRevision':null,
	'entries':{},
	
	DialogSwitchRevision:function()
	{
		if(this.dialogSwitchRevision==null) {
			this.dialogSwitchRevision = new Changelog_Dialog_SwitchRevision();
		}
		
		this.dialogSwitchRevision.Show();
	},
	
	Add:function(id, author, date, text, before, after)
	{
		this.entries[id] = {
			'id':id,
			'author':author,
			'date':date,
			'text':text,
			'before':before,
			'after':after
		};
	},
	
	DialogDetails:function(id)
	{
		if(typeof(this.entries[id])=='undefined') {
			return;
		}
		
		var entry = this.entries[id];
		var dialog = new Changelog_Dialog_Entry();
		dialog.SetEntry(entry);
		dialog.Show();
	}
};