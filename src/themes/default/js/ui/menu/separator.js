/**
 * A separator entry in a bootstrap menu.
 * 
 * @package UI
 * @subpackage Bootstrap
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends UI_Menu_Item
 * @see UI_Menu.AddSeparator
 */
var UI_Menu_Separator = 
{
	GetType:function() 
	{
		return 'separator';
	},
	
	Render:function() 
	{
		this.AddClass('divider');
		
		var atts = {
			'class':this.classes.join(' '),
			'id':this.id
		};
		
		return '<li'+UI.CompileAttributes(atts)+'></li>';
	},
	
	PostRender:function()
	{
		this.listElement = $('#'+this.id);
		this.anchorElement = null;
	}
};

UI_Menu_Separator = UI_Menu_Item.extend(UI_Menu_Separator);