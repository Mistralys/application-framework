/**
 * Utility class for bootstrap menu items, offering an easy API to
 * configure and customize it.
 *
 * @package UI
 * @subpackage Bootstrap
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Menu.AddSubmenu
 */
var UI_Menu_Submenu = 
{
	'parentMenu':null,
	'label':null,
		
   /**
    * Constructor.
    * 
    * @param {UI_Menu} parentMenu
    * @param {String} label
    */
	init:function(parentMenu, label)
	{
		this._super();
		
		this.parentMenu = parent;
		this.label = label;
		
		this.RemoveAttribute('role');
		this.RemoveAttribute('aria-labelledby');
	},
	
	GetType:function()
	{
		return 'submenu';
	},
	
	Render:function()
	{
		var html = ''+
		'<li class="dropdown-submenu">'+
			'<a tabindex="-1" href="#">'+this.label+'</a>'+
			this._super()+
		'</li>';
		
		return html;
	}
};

UI_Menu_Submenu = UI_Menu.extend(UI_Menu_Submenu);