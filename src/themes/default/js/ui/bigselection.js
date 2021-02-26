var UI_BigSelection = 
{
	Render:function()
	{
		this.AddClass('bigselection');
		this.RemoveClass('dropdown-menu');
		
		var atts = {
			'class':this.classes.join(' '),
			'id':this.id
		};
		
		var html = ''+
		'<ul '+UI.CompileAttributes(atts)+'>'+
			this._RenderItems()+
		'</ul>';
		
		return html;
	},
	
	MakeSmall:function()
	{
		return this.AddClass('size-small');
	}
};

UI_BigSelection = UI_Menu.extend(UI_BigSelection);