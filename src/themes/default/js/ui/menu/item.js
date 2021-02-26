/**
 * Utility class for bootstrap menu items, offering an easy API to
 * configure and customize it.
 *
 * @package UI
 * @subpackage Bootstrap
 * @class 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_Menu_Item = 
{
	'id':null,
	'name':null,
	'menu':null,
	'label':null,
	'icon':null,
	'classes':null,
	'styles':null,
	'textStyle':null,
	'clickHandler':null,
	'postRenderAttempts':null,
	'maxPostRenderAttempts':null,
	'disabled':null,
	'urlParams':null,
    'urlTarget':null,
	'popover':null,
	'hidden':null,
	'rendered':null,
	'anchorElement':null,
	'listElement':null,
	'description':null,
	'tooltipText':null,
	'disabledTooltip':null,

   /**
    * @constructs
    * @param {UI_Menu} menu
    * @param {String} label
    */
	init:function(menu, label, name)
	{
		if(isEmpty(name)) {
			name = null;
		}
		
		this.name = name;
		this.id = 'menuitem'+nextJSID();
		this.menu = menu;
		this.label = label;
		this.icon = null;
		this.classes = [];
		this.styles = {};
		this.textStyle = null;
		this.clickHandler = null;
		this.postRenderAttempts = 0;
		this.maxPostRenderAttempts = 3;
		this.disabled = false;
		this.urlParams = null;
        this.urlTarget = null;
		this.popover = null;
		this.hidden = false;
		this.rendered = false;
		this.description = null;
		this.tooltipText = null;
		this.disabledTooltip = null;
	},
	
   /**
    * Retrieves the type of menu item. This is used only by the
    * menu itself to differentiate between the possible items.
    * 
    * @return {String}
    */
	GetType:function()
	{
		return 'item';
	},

   /**
    * Retrieves the menu item's ID attribute.
    * 
    * @returns {Number}
    */
	GetID:function()
	{
		return this.id;
	},
	
   /**
    * Hides the menu item by setting its display style
    * to "none".
    * 
    * @return {UI_Menu_Item}
    */
	Hide:function()
	{
		this.hidden = true;
		
		if(this.rendered) {
			this.listElement.hide();
		}
		
		return this;
	},
	
   /**
    * Shows the menu item again when it has been hidden previously.
    * Has no effect if it is already visible.
    * 
    * @returns {UI_Menu_Item}
    */
	Show:function()
	{
		this.hidden = false;
		
		if(this.rendered) {
			this.listElement.show();
		}
		
		return this;
	},
	
   /**
    * Adds a CSS style to add to the item's style attribute.
    * Note: do not add the ending semicolon, it will be added
    * automatically.
    * 
    * @param {String} styleName The style name, e.g. "display"
    * @param {String} value The style value, e.g. "15px"
    * @return {UI_Menu_Item}
    */
	AddStyle:function(styleName, value)
	{
		this.styles[styleName] = value;
		
		if(this.rendered) {
			this.listElement.css(styleName, value);
		}
		
		return this;
	},
	
   /**
    * Removes a style.
    * @param {String} targetStyleName
    * @returns {UI_Menu_Item}
    */
	RemoveStyle:function(targetStyleName)
	{
		if(!this.HasStyle(targetStyleName)) {
			return this;
		}
		
		// remove the whole style attribute, we will add all needed
		// styles anew to ensure the are no leftover styles
		// http://stackoverflow.com/questions/4036857/jquery-remove-style-added-with-css-function
		if(this.rendered) {
			this.listElement.removeAttr('style');
		}
		
		var keep = {};
		$.each(this.styles, function(styleName, value) {
			if(styleName != targetStyleName) {
				keep[styleName] = value;
				
				// and restore the styles we want to keep
				if(this.rendered) {
					this.listElement.css(styleName, value);
				}
			}
		});
		
		this.styles = keep;
		
		return this;
	},
	
   /**
    * Checks if the item already has the specified style.
    * 
    * @param {String} style
    * @return {Boolean}
    */
	HasStyle:function(style)
	{
		if(typeof(this.styles[style]) != 'undefined') {
			return true;
		}
		
		return false;
	},

   /**
    * Sets the ID attribute. The button always gets an automatic ID,
    * this can be used to override this and use your own ID.
    * 
    * @param {String} id
    * @returns {UI_Menu_Item}
    */
	SetID:function(id)
	{
		this.id = id;
		return this;
	},
	
   /**
    * Sets the icon to use for the menu item.
    * 
    * @param {UI_Icon} icon
    * @returns {UI_Menu_Item}
    */
	SetIcon:function(icon)
	{
		this.icon = icon;
		return this;
	},
	
   /**
    * Disables the menu item. Also disables the click
    * handler if any is set. If a disabled tooltip has
    * been set, the tooltip text will be switched 
    * accordingly.
    * 
    * @param {Boolean} [disabled=true]
    * @returns {UI_Menu_Item}
    */
	MakeDisabled:function(disabled)
	{
		if(disabled != false) {
			disabled = true;
		}
		
		this.disabled = disabled;
		
		if(disabled) {
			this.AddClass('disabled');
		} else {
			this.RemoveClass('disabled');
		}
		
		if(this.rendered) {
			this.DestroyTooltip();
			this.listElement.attr('title', this.GetTooltipText());
			UI.MakeTooltip(this.listElement);
		}
		
		return this;
	},
	
	DestroyTooltip:function()
	{
		if(!this.rendered) {
			return;
		}
		
		this.listElement.tooltip('destroy');
	},
	
   /**
    * Checks whether the item is currently disabled.
    * @returns {Boolean}
    */
	IsDisabled:function()
	{
		return this.disabled;
	},
	
   /**
    * Adds a class to the menu item.
    * 
    * @param {String} className
    * @returns {UI_Menu_Item}
    */
	AddClass:function(className)
	{
		if(!this.HasClass(className)) {
			this.classes.push(className);
			if(this.rendered) {
				this.listElement.addClass(className);
			}
		}
		
		return this;
	},
	
	RemoveClass:function(className)
	{
		if(!this.HasClass(className)) {
			return this;
		}
		
		var keep = [];
		$.each(this.classes, function(idx, checkClass) {
			if(checkClass != className) {
				keep.push(checkClass);
			}
		});
		
		this.classes = keep;
		
		if(this.rendered) {
			this.listElement.removeClass(className);
		}
		
		return this;
	},
		
   /**
    * Checks if the menu item has the specified class.
    * 
    * @return {Boolean}
    */
	HasClass:function(className)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==className) {
				return true;
			}
		}
		
		return false;
	},
	
   /**
    * Sets the menu item's click handler.
    *

    * @param {Function} handler
    * @returns {UI_Menu_Item}
    */
	Click:function(handler)
	{
		this.clickHandler = handler;
		return this;
	},
	
	MakeDangerous:function()
	{
		this.AddClass('danger');
		return this;
	},
	
   /**
    * Sets the URL to link the item to.
    * 
    * @param {Object} urlParams The parameters for the URL to link to
    * @param {String} target The target window to open in
    * @returns {UI_Menu_Item}
    */
	Link:function(urlParams, target)
	{
		this.urlParams = urlParams;
		this.urlTarget = target;
		return this;
	},
	
   /**
    * Enables the item's tooltip and sets the text 
    * for the tooltip.
    * 
    * @param {String} tooltipText
    * @return {UI_Menu_Item}
    */
	SetTooltip:function(tooltipText)
	{
		this.tooltipText = tooltipText;
		return this;
	},
	
   /**
    * Sets the tooltip to show when the item is disabled.
    * 
    * @param {String} tooltipText
    * @return {UI_Menu_Item}
    */
	SetDisabledTooltip:function(tooltipText)
	{
		this.disabledTooltip = tooltipText;
		return this;
	},
	
	Render:function()
	{
		var label = this.label;
		if(this.icon != null) {
			label = this.icon.Render()+' '+label;
		}
		
		var liAtts = {};
		liAtts['id'] = this.id+'_li';
		
		if(this.classes.length > 0) {
			liAtts['class'] = this.classes.join(' ');
		}

		var styles = this.styles;
		if(this.hidden) {
			styles['display'] = 'none';
		}
		
		var style = UI.CompileStyles(styles);
		if(style.length > 0) {
			liAtts['style'] = style;
		}

		if(this.tooltipText != null) {
			liAtts['title'] = this.GetTooltipText();
		}
		
		var linkAtts = {};
		linkAtts['id'] = this.id;
		linkAtts['tabindex'] = '-1';
		linkAtts['href'] = 'javascript:void(0)';
		
		if(this.urlParams != null) {
			linkAtts['href'] = application.buildURL(this.urlParams);
            if(!isEmpty(this.urlTarget)) {
				linkAtts['target'] = this.urlTarget;
			}
		}
		
		var html = ''+
		'<li'+UI.CompileAttributes(liAtts)+'>'+
			'<a'+UI.CompileAttributes(linkAtts)+'>'+
				label+
			'</a>'+
			this.RenderDescription()+
		'</li>';
		
		this.SchedulePostRender();

		return html;
	},
	
	GetAnchorElement:function()
	{
		return $('#'+this.id);
	},
	
	GetListElement:function()
	{
		return $('#'+this.id+'_li');
	},
	
   /**
    * Retrieves the text to use for the tooltip. When the menu item
    * is disabled and a disabled tooltip has been set, returns the
    * disabled text.
    * 
    * @returns {String}
    */
	GetTooltipText:function()
	{
		var text = null;
		
		if(this.tooltipText != null) {
			text = this.tooltipText;
		}
		
		if(this.disabled && this.disabledTooltip != null) {
			text = this.disabledTooltip;
		}

		return text;
	},
	
	RenderDescription:function()
	{
		if(isEmpty(this.description)) {
			return '';
		}
		
		return '<div class="menu-item-description">'+this.description+'</div>';
	},
	
	SchedulePostRender:function()
	{
		// do the post rendering, which will attach event handlers and co
		var object = this;
		UI.RefreshTimeout(function() {
			object.PostRender();
		});
	},
	
	PostRender:function()
	{
		var item = this;
		var el = $('#'+this.id);
		var elLI = $('#'+this.id+'_li');
		
		// element not present in the DOM yet? Schedule another
		// attempt a little later.
		if(el.length==0) {
			this.postRenderAttempts++;
			if(this.postRenderAttempts==this.maxPostRenderAttempts) {
				this.log(
					'Could not do the menu item\'s post rendering afer ['+this.postRenderAttempts+'] attempts.', 
					'error'
				);
				return;
			}
			
			this.SchedulePostRender();
			return;
		}

		// add the click handler
		elLI.click(function() {
			item.Handle_Click();
		});
		
		UI.MakeTooltip(elLI);
		
		if(this.popover!=null) {
			elLI.popover({
				'html':true,
				'placement':'right',
				'trigger':'hover',
				'title':UI.Icon().Information() + ' ' + this.popover.title,
				'content':this.popover.text,
				'delay':{
					'show':300,
					'hide':50
				}
			});
		}
		
		this.rendered = true;
		this.anchorElement = el;
		this.listElement = elLI;
	},
	
	Handle_Click:function()
	{
		if(this.disabled) {
			return;
		}
		
		if(this.clickHandler == null) {
			return;
		}
		
		this.clickHandler.call(undefined, this);
	},
	
	log:function(message, category)
	{
		application.log(
			'Menu item ['+this.id+']',
			message, 
			category
		);
	},
	
	toString:function()
	{
		return this.Render();
	},
	
   /**
    * Sets the text/html for a popover that will be shown when the
    * user hovers over the menu item, like a tooltip but accomodating
    * more information.
    * 
    * @param {String} text
    * @param {String} [title=""]
    * @returns {UI_Menu_Item}
    */
	SetPopover:function(text, title)
	{
		if(isEmpty(title)) {
			title = t('Information');
		}
		
		this.popover = {
			'text':text,
			'title':title
		};
		
		return this;
	},
	
	GetName:function()
	{
		return this.name;
	},
	
   /**
    * Sets a detailed description text for this item. This is added below 
    * the menu link.
    * 
    * @param {String} description
    * @returns {UI_Menu_Item}
    */
	SetDescription:function(description)
	{
		this.description = description;
		return this;
	},
	
	SetActive:function(active)
	{
		if(active == false) {
			return this.RemoveClass('active');
		}
		
		return this.AddClass('active');
	}
};

UI_Menu_Item = Class.extend(UI_Menu_Item);