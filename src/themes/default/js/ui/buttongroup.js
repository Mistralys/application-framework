/**
 * Utility class for handling UI button groups: allows
 * adding buttons and renders them so they fit together
 * using bootstrap's markup.
 * 
 * @class
 * @package UI
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_ButtonGroup =
{
	'id':null,
	'buttons':null,
	'options':null,
	
   /**
    * @constructs
    * @param {String} [id] Optional id, automatically prepended with "btnGroup_"
    */
	init:function(id)
	{
		if(typeof(id)=='undefined' || id===null) {
			id = nextJSID();
		}
		
		this.buttons = [];
		this.id = 'btnGroup_' + id;
		this.options = {
			'single-nogroup':true
		};
	},
	
   /**
    * Sets a button group option.
    * @param {String} name
    * @param {String} value
    * @returns {UI_ButtonGroup}
    */
	SetOption:function(name, value)
	{
		this.options[name] = value;
		return this;
	},
	
   /**
    * Adds an existing button object.
    * 
    * @param {UI_Button} button
    * @return {UI_ButtonGroup}
    */
	Add:function(button)
	{
		var isButton = button instanceof UI_Button;
		var isMenu = button instanceof UI_DropMenu;
		if(!isButton && !isMenu) {
			this.log('The specified button is not an instance of UI_Button or UI_DropMenu.', 'error');
			return this;
		}
		
		this.buttons.push(button);
		return this;
	},
	
   /**
    * Creates a new button and adds it, and returns
    * the new button object to configure it further.
    * 
    * @param {String} [label] Optional button label
    * @return {UI_Button}
    */
	Create:function(label)
	{
		var btn = UI.Button(label);
		this.Add(btn);
		return btn;
	},
	
   /**
    * Renders the HTML markup for the group and returns it.
    * If no buttons have been added, returns an empty string.
    * 
    * NOTE: If only a single button has been added, it will not 
    * be rendered as a group, but as a single button.
    * 
    * @return {String}
    */
	Render:function()
	{
		if(this.buttons.length < 1) {
			return '';
		}
		
		if(this.buttons.length == 1 && this.options['single-nogroup']) {
			this.log('Only one button present, rendering as a regular single button.', 'ui');
			return this.buttons[0].Render();
		}
		
		var html = ''+
		'<div class="btn-group" id="' + this.id + '">';
			$.each(this.buttons, function(idx, button) {
				var isMenu = button instanceof UI_DropMenu;
				if(isMenu) {
					button.NoContainer();
				}
				html += button.Render();
			});
			html += ''+
		'</div>';
			
		return html;
	},
	
	toString:function()
	{
		return this.Render();
	},
	
	log:function(message, category)
	{
		application.log(
			'Button group [' + this.id + ']',
			message,
			category
		);
	}
};

UI_ButtonGroup = Class.extend(UI_ButtonGroup);