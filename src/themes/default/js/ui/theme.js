var UI_Theme = 
{
	'id':null,
	'url':null,
		
	init:function(id, url)
	{
		this.id = id;
		this.url = url;
	},
	
	GetJavascriptsURL:function()
	{
		return this.url + '/js';
	}
};

UI_Theme = Class.extend(UI_Theme);