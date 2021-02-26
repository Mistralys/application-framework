/**
 * Button class used to create and configure buttons in
 * the application. Offers an easy to use API to create
 * buttons of any type.
 *
 * @package UI
 * @subpackage Bootstrap
 * @class 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_Button = 
{
	'id':null,
	'label':null,
	'layout':null,
	'loading':null,
	'icon':null,
	'classes':null,
	'styles':null,
	'size':null,
	'title':null,
	'loadingText':null,
	'tooltip':null,
	'shortcut':null,
	'postRenderAttempts':null,
	'maxPostRenderAttempts':null,
	'clickHandler':null,
	'attributes':null,
	'isDeveloper':null,
	'popover':null,
	'url':null,
	'rendered':null,
	'element':null,
	'cursor':null,
	'pushed':null,
	'confirmMessage':null,
	'properties':null,
	'types':['inverse', 'warning', 'primary', 'danger', 'developer', 'success', 'info', 'link', 'default', 'dull'],
	'isProcessing':null,
	
   /**
    * @constructs
    * @param {String} [label] The label of the button.
    * @param {String} [id] The ID to use for the button.
    * @param {Boolean} [serverside] Whether this button was generated serverside.
    */
	init:function(label, id, serverside, serverSettings)
	{
		if(isEmpty(label)) {
			label = '';
		}
		
		if(isEmpty(id)) {
			id = 'btn'+nextJSID();
		}
		
		this.id = id;
		this.label = label;
		this.layout = 'default';
		this.loading = false;
		this.type = 'button';
		this.icon = null;
		this.classes = [];
		this.styles = {};
		this.size = null;
		this.isProcessing = false;
		this.title = null;
		this.loadingText = null;
		this.tooltip = {
			'text':null,
			'placement':'top'
		};
		this.shortcut = null;
		this.postRenderAttempts = 0;
		this.maxPostRenderAttempts = 3;
		this.clickHandler = null;
		this.attributes = {};
		this.isDeveloper = false;
		this.url = null;
		this.rendered = false;
		this.element = null;
		this.popover = null;
		this.cursor = null;
		this.pushed = false;
		this.properties = {};
		this.confirmMessage = null;
		
		if(serverside == true) {
			this.element = $('#'+this.id);
			this.rendered = true;
			this.layout = serverSettings.layout;
			this.type = serverSettings.type;
		}
		
		// register the button so it can be retrieved by its ID
		// later using the UI's GetButton method.
		UI.Handle_RegisterButton(this);
	},
	
   /**
    * Retrieves the button's label.
    * @returns {String}
    */
	GetLabel:function()
	{
		return this.label;
	},
	
   /**
    * Adds a class to the button tag.
    *
    * @param {String} className
    * @returns {UI_Button}
    */
	AddClass:function(className)
	{
		if(this.rendered) {
			this.element.addClass(className);
		} else {
			if(!this.HasClass(className)) {
				this.classes.push(className);
			}
		}
		
		return this;
	},
	
   /**
    * Removes a class from the button tag. Has no effect
    * if it does not have the class.
    * 
    * @param {String} className
    * @returns {UI_Button}
    */
	RemoveClass:function(className)
	{
		if(this.rendered) {
			this.element.removeClass(className);
		} else {
			var keep = [];
			$.each(this.classes, function(idx, item) {
				if(item != className) {
					keep.push(item);
				}
			});
			this.classes = keep;
		}
		
		return this;
	},
		
   /**
    * Checks if the button has the specified class.
    *
    * @return {Boolean}
    */
	HasClass:function(className)
	{
		if(this.rendered) {
			return this.element.hasClass(className);
		}
		
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==className) {
				return true;
			}
		}
		
		return false;
	},
	
   /**
    * Styles the button as a primary button.
    *
    * @returns {UI_Button}
    */
	MakePrimary:function()
	{
		return this.MakeType('primary');
	},
	
   /**
    * Styles the button as a button for a dangerous operation, like deleting records.
    *
    * @returns {UI_Button}
    */
	MakeDangerous:function()
	{
		return this.MakeType('danger');
	},
	
   /**
    * Styles the button as a button for developer-only functions.
    * 
    * @returns {UI_Button}
    */
	MakeDeveloper:function()
	{
		this.isDeveloper = true;
		return this.MakeType('developer');
	},
	
   /**
    * Styles the button as an informational button.
    *
    * @returns {UI_Button}
    */
	MakeInformational:function()
	{
		return this.MakeType('info');
	},
	
   /**
    * Styles the button as a success button.
    *
    * @returns {UI_Button}
    */
	MakeSuccess:function()
	{
		return this.MakeType('success');
	},
	
   /**
    * Styles the button as a warning button for potentially dangerous operations.
    *
    * @returns {UI_Button}
    */
	MakeWarning:function()
	{
		return this.MakeType('warning');
	},
	
   /**
    * Styles the button as an inverted button.
    *
    * @returns {UI_Button}
    */
	MakeInverse:function()
	{
		return this.MakeType('inverse');
	},
	
   /**
    * Sets the type of the button, e.g. <code>primary</code>.
    * 
    * @param {String} type
    * @returns {UI_Button}
    */
	MakeType:function(type)
	{
		if(!this.TypeExists(type)) {
			return this;
		}
		
		if(this.rendered) {
			var button = this;
			// remove any existing button type classes first
			$.each(this.types, function(idx, knownType) {
				if(knownType != type) {
					button.element.removeClass('btn-' + knownType);
				}
			});
			this.element.addClass('btn-' + type);
			return this;
		} 
		
		this.layout = type;
		return this;
	},
	
	MakeDefault:function()
	{
		return this.MakeType('default');
	},

	MakeDull:function()
	{
		return this.MakeType('dull');
	},

   /**
    * Checks whether the specified button type exists.
    * 
    * @param {String} type e.g. <code>primary</code>
    * @returns {Boolean}
    */
	TypeExists:function(type)
	{
		return in_array(type, this.types);
	},
	
   /**
    * Turns the button into a submit button.
    *
    * @returns {UI_Button}
    */
	MakeSubmit:function()
	{
		this.type = 'submit';
		return this;
	},
	
   /**
    * Makes the button into a small button.
    *
    * @returns {UI_Button}
    */
	MakeSmall:function()
	{
		return this.MakeSize('small');
	},
	
   /**
    * Makes the button into a large button.
    *
    * @returns {UI_Button}
    */
	MakeLarge:function()
	{
		return this.MakeSize('large');
	},
	
   /**
    * Makes the button into a miniature button.
    *
    * @returns {UI_Button}
    */
	MakeMini:function()
	{
		return this.MakeSize('mini');
	},
	
	'sizes':{
		'2':{
			'large':'large',
			'small':'small',
			'mini':'mini'
		},
		'3':{
			'large':'lg',
			'small':'sm',
			'mini':'xs'
		}
	},
	
	MakeSize:function(size)
	{
		this.size = this.sizes[UI.BOOTSTRAP_VERSION][size];
		return this;
	},
	
   /**
    * Makes the button match the whole width of its container.
    *
    * @returns {UI_Button}
    */
	MakeFullWidth:function()
	{
		return this.AddClass('btn-block');
	},
	
   /**
    * Disables the button.
    * 
    * NOTE: does not disable event handlers!
    *
    * @returns {UI_Button}
    */
	MakeDisabled:function()
	{
		return this.AddClass('disabled');
	},
	
   /**
    * Enables the button.
    * 
    * @returns {UI_Button}
    */
	MakeEnabled:function()
	{
		if(this.HasClass('btn-locked')) {
			return this;
		}
		
		return this.RemoveClass('disabled');
	},
	
   /**
    * Checks whether the button is currently enabled,
    * as opposed to being disabled.
    * 
    * @returns {Boolean}
    */
	IsEnabled:function()
	{
		return !this.HasClass('disabled');
	},
	
   /**
    * Checks whether the button is currently
    * in its loading state.
    * 
    * @returns {Boolean}
    */
	IsLoading:function()
	{
		return this.loading;
	},
	
   /**
    * Checks whether the button has an href link.
    * 
    * @returns {Boolean}
    */
	IsLinked:function()
	{
		if(this.url != null) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Sets the text to display when you set the state of the button to loading.
    *
    * @param {String} text
    * @returns {UI_Button}
    */
	SetLoadingText:function(text)
	{
		this.loadingText = text;
		
		if(this.rendered) {
			this.element.attr('data-loading-text', text);
		}
		
		return this;
	},
	
   /**
    * Sets the tooltip text for the dynamic tooltip popup.
    *
    * @param {String} text
    * @returns {UI_Button}
    */
	SetTooltip:function(text)
	{
		this.tooltip.text = text;
		return this;
	},
	
   /**
    * Makes the tooltip display on the bottom.
    * @returns {UI_Button}
    */
	MakeTooltipBottom:function()
	{
		return this.SetTooltipPosition('bottom');
	},
	
   /**
    * Makes the tooltip display on the left.
    * @returns {UI_Button}
    */
	MakeTooltipLeft:function()
	{
		return this.SetTooltipPosition('left');
	},

   /**
    * Makes the tooltip display on the right.
    * @returns {UI_Button}
    */
	MakeTooltipRight:function()
	{
		return this.SetTooltipPosition('right');
	},
	
   /**
    * Makes the tooltip display on the top (default).
    * @returns {UI_Button}
    */
	MakeTooltipTop:function()
	{
		return this.SetTooltipPosition('top');
	},
	
   /**
    * Sets the position of the tooltip relative to the element.
    * 
    * @param {String} position "top" (default), "left", "right", "bottom"
    * @returns {UI_Button}
    */
	SetTooltipPosition:function(position)
	{
		if(in_array(position, ['top', 'left', 'right', 'bottom'])) {
			this.tooltip.placement = position;
		}
		
		return this;
	},
	
   /**
    * Sets the text to use for the title attribute. Note that
    * this is overridden if you set a tooltip text.
    *
    * @param {String}
    * @returns {UI_Button}
    */
	SetTitle:function(text)
	{
		this.title = text;
		return this;
	},
	
   /**
    * Sets the icon to use for the button.
    *
    * @param {UI_Icon} icon
    * @returns {UI_Button}
    */
	SetIcon:function(icon)
	{
		if(this.rendered) {
			$('#'+this.id+'-icon').html(icon.Render());
		}
		
		this.icon = icon;
		return this;
	},
	
   /**
    * Sets the ID attribute. The button always gets an automatic ID,
    * this can be used to override this and use your own ID.
    *
    * @param {String} id
    * @returns {UI_Button}
    */
	SetID:function(id)
	{
		this.id = id;
		return this;
	},
	
   /**
    * Sets the shortcut help text for the keyboard shortcut that can
    * be used to trigger the button's action. This has no function, it
    * just gets displayed next to the button.
    * 
    * Example:
    * 
    * <pre>
    * SetShortcut('CTRL+L');
    * </pre>
    *
    * @param {String} shortcut
    * @returns {UI_Button}
    */
	SetShortcut:function(shortcut)
	{
		this.shortcut = shortcut;
		return this;
	},
	
   /**
    * Retrieves the button's ID attribute.
    *
    * @returns {String}
    */
	GetID:function()
	{
		return this.id;
	},
	
   /**
    * Whether the button is currently active and
    * can be clicked, as opposed to being either
    * disabled or in its loading state.
    * 
    * @returns {Boolean}
    */
	IsActive:function()
	{
		if(this.IsEnabled() && !this.IsLoading()) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Sets the button's click handler. Can be used
    * before and after the button has been rendered.
    *
    * If the button is disabled or loading, this
    * handler will not be called.
    *
    * @param {Function} handler
    * @returns {UI_Button}
    */
	Click:function(handler)
	{
		if(this.rendered) 
		{
			this.GetDOMElement().click(handler);
		} 
		else 
		{
			this.clickHandler = handler;	
		}
		
		return this;
	},

   /**
    * Handles clicking the button. Checks if the
    * button is active, and if not, prevents any
    * actions that were defined, whether it's a 
    * click handler, or an URL to call.
    * 
    * @param {Object} e The event object for the click
    * @returns {Boolean}
    */
	Handle_Click:function(e)
	{
		if(!this.IsActive() || this.isProcessing) {
			this.log('Inactive, preventing clicks.');
			e.preventDefault();
			return false;
		}
		
		this.isProcessing = true;
		
		if(this.clickHandler != null) {
			if(this.confirmMessage != null && !confirm(this.confirmMessage)) {
				return true;
			}
			this.clickHandler.call(this);
		}
		
		this.isProcessing = false;
		return true;
	},
	
   /**
    * Logs a console message for this button.
    * @param {String} message
    * @param {String} [category=UI]
    */
	log:function(message, category)
	{
		if(isEmpty(category)) {
			category = 'UI';
		}
		
		application.log('Button ['+this.id+']', message, category);
	},
	
   /**
    * Renders the button's markup and returns it.
    * 
    * Note: Usually you do not need to call this manually, since
    * the button will do it on its own when used in a string context.
    *
    * @returns {String}
    */
	Render:function()
	{
		var atts = this.GetAttributes();
		var attsString = UI.CompileAttributes(atts);
		
		var label = this.label;
		if(this.icon != null) {
			label = '<span id="'+this.id+'-icon">'+this.icon.Render()+'</span> '+label;
		}
		
		if(this.isDeveloper) {
			label = '<b>DEV:</b> ' + label;
		}
		
		tagName = 'button';
		if(this.url != null) {
			tagName = 'a';
		}
		
		var el = ''+
		'<' + tagName + ' ' + attsString + '>'+
			label+
		'</' + tagName + '>';
		
		if(this.shortcut != null) {
			el += ' '+
			'<span class="keyboard-shortcut">'+
				this.shortcut+
			'</span>';
		}
		
		var button = this;
		UI.RefreshTimeout(function() {
			button.PostRender();
		});
		
		return el;
	},
	
   /**
    * Retrieves the button's DOM element.
    * 
    * @return {DOMElement|NULL} Only available after the button has been rendered.
    */
	GetDOMElement:function()
	{
		return this.element;
	},
	
	PostRender:function()
	{
		var el = $('#'+this.id);
		this.rendered = true;
		this.element = el;
		var button = this;
		
		// element not present in the DOM yet? Schedule another
		// attempt a little later.
		if(el.length==0) {
			this.postRenderAttempts++;
			if(this.postRenderAttempts==this.maxPostRenderAttempts) {
				return;
			}
			
			UI.RefreshTimeout(function() {
				button.PostRender();
			});
			return;
		}
		
		if(this.tooltip.text != null && this.popover==null) {
			UI.MakeTooltip(el, false, this.tooltip.placement);
		}
		
		this.element.on('click', function(e) {
			return button.Handle_Click(e);
		});
		
		if(this.popover != null) {
			el.popover({
				'html':true,
				'delay':{'show':500, 'hide':100},
				'trigger':this.popover.trigger,
				'placement':this.popover.position,
				'title':t('Information')
			});
		}
	},
	
   /**
    * Sets an attribute of the button tag. Note that this will
    * not work for setting attributes like the type or class
    * attributes since these are handled separately.
    *
    * @param {String} name
    * @param {String} value
    * @returns {UI_Button}
    */
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	},
	
   /**
    * Sets a javascript statement for the button tag's onclick attribute.
    * Alternatively, you can use the Click method to set a handler function.
    *

    * @param {String} statement
    * @returns {UI_Button}
    */
	SetOnclick:function(statement)
	{
		return this.SetAttribute('onclick', statement);
	},
	
	GetAttributes:function()
	{
		var atts = this.attributes;
		
		atts['id'] = this.id;
		atts['type'] = this.type;
		atts['autocomplete'] = 'off'; // avoid the firefox autocomplete bug
		
		var classes = this.classes;
		classes.push('btn');
		classes.push('btn-'+this.layout);
		
		if(this.size!=null) {
			classes.push('btn-'+this.size);
		}
		
		if(this.pushed) {
			classes.push('active');
		}
		
		atts['class'] = classes.join(' ');
		
		if(this.cursor != null) {
			this.SetStyle('cursor', this.cursor);
		}
		
		var styles = [];
		$.each(this.styles, function(name, value) {
			styles.push(name + ':' + value);
		});
		
		atts['style'] = styles.join(';');

		var title = '';
		if(this.title!=null) {
			title = this.title;
		}
		
		if(this.tooltip.text!=null) {
			title = this.tooltip.text;
		}
		
		if(this.popover != null) {
			title = this.popover.title;
			atts['data-content'] = this.popover.text;
		}
		
		if(title.length > 0) {
			atts['title'] = title;
		}
		
		if(this.loadingText!=null) {
			atts['data-loading-text'] = this.loadingText;
		}
		
		if(this.url != null) {
			var url = this.url.url;
			if(typeof(url) == 'function') {
				url = url.call(this);
			}
			
			atts['href'] = url;
			if(this.url.newTab) {
				atts['target'] = '_blank';
			}
		}
		
		return atts;
	},
	
   /**
    * Sets a style of the button's style attribute.
    * 
    * Examples: 
    * 
    * AddStyle('font-family', 'Arial');
    * AddStyle('display', 'none');
    *
    * This can be called before and after the button
    * has been rendered.
    * 
    * @param style The style to set
    * @param value The value to set for the style
    * @returns {UI_Button}
    */
	SetStyle:function(style, value)
	{
		if(this.rendered) {
			this.element.css(style, value);
		}
		
		this.styles[style] = value;
		return this;
	},	
	
	GetStyle:function(style)
	{
		if(typeof(this.styles[style]) != 'undefined') {
			return this.styles[style];
		}
		
		return null;
	},
	
	toString:function()
	{
		return this.Render();
	},
	
   /**
    * Makes the link redirect to an URL when clicked.
    * 
    * @param {String|Function} url Can be an URL, or a function that returns the URL. The function gets the button instance as <code>this</code>. Called once when the button is rendered.
    * @param {Boolean} [newTab=false] Whether to open the url in a new tab
    * @returns {UI_Button}
    */
	Link:function(url, newTab)
	{
		if(newTab != true) {
			newTab = false;
		}
		
		this.url = {
			'url':url,
			'newTab':newTab
		};

		return this;
	},
	
   /**
    * Sets the <code>display</code> style to <code>inline</code>.
    * @returns {UI_Button}
    */
	MakeInline:function()
	{
		return this.SetStyle('display', 'inline');
	},
	
   /**
    * Sets the button's label. Can be used to change it
    * once it has been rendered as well.
    * 
    * @param {String} label
    * @return {UI_Button}
    */
	SetLabel:function(label)
	{
		this.label = label;

		if(this.rendered) {
			if(this.icon != null) {
				label = this.icon + ' ' + label;
			}
			this.element.html(label);
		}
		
		return this;
	},
	
   /**
    * Switches the button into the loading state. Only 
    * works if the button has been rendered.
    * 
    * @returns {UI_Button}
    */
	Loading:function()
	{
		if(this.rendered) {
			this.element.button('loading');
		}
		
		this.loading = true;
		
		return this;
	},
	
   /**
    * Resets the button from the loading state. Only
    * works if the button has been rendered.
    * 
    * @returns {UI_Button}
    */
	Reset:function()
	{
		if(this.rendered) {
			this.element.button('reset');
		}
		
		this.loading = false;
		
		return this;
	},
	
	MakeLink:function()
	{
		return this.MakeType('link');
	},
	
   /**
    * Makes the button show a popover when clicked.
    * 
    * @param {String} text
    * @param {String} title
    * @param {String} [position=top] The position of the popover: top | bottom | left | right
    * @param {String} [trigger=hover] How to trigger the popover: hover | click
    * @return {UI_Button} 
    */
	Popover:function(text, title, position, trigger)
	{
		if(isEmpty(position)) {
			position = 'top';
		}
		
		if(isEmpty(trigger)) {
			trigger = 'hover';
		}
		
		this.popover = {
			'text':text,
			'title':title,
			'position':position,
			'trigger':trigger
		};
		
		return this;
	},
	
   /**
    * Sets the cursor to use when the user hovers over
    * the button. This can be any of the available standard
    * CSS cursor types:
    * 
    * alias = The cursor indicates an alias of something is to be created
    * all-scroll = The cursor indicates that something can be scrolled in any direction
    * auto = Default. The browser sets a cursor
    * cell = The cursor indicates that a cell (or set of cells) may be selected
    * context-menu = The cursor indicates that a context-menu is available
    * col-resize = The cursor indicates that the column can be resized horizontally
    * copy = The cursor indicates something is to be copied
    * crosshair = The cursor render as a crosshair
    * default = The default cursor
    * e-resize = The cursor indicates that an edge of a box is to be moved right (east)
    * ew-resize = Indicates a bidirectional resize cursor
    * grab = The cursor indicates that something can be grabbed
    * grabbing = The cursor indicates that something can be grabbed
    * help = The cursor indicates that help is available
    * move = The cursor indicates something is to be moved
    * n-resize = The cursor indicates that an edge of a box is to be moved up (north)
    * ne-resize = The cursor indicates that an edge of a box is to be moved up and right (north/east)
    * nesw-resize = Indicates a bidirectional resize cursor
    * ns-resize = Indicates a bidirectional resize cursor
    * nw-resize = The cursor indicates that an edge of a box is to be moved up and left (north/west)
    * nwse-resize = Indicates a bidirectional resize cursor
    * no-drop = The cursor indicates that the dragged item cannot be dropped here
    * none = No cursor is rendered for the element
    * not-allowed = The cursor indicates that the requested action will not be executed
    * pointer = The cursor is a pointer and indicates a link
    * progress = The cursor indicates that the program is busy (in progress)
    * row-resize = The cursor indicates that the row can be resized vertically
    * s-resize = The cursor indicates that an edge of a box is to be moved down (south)
    * se-resize = The cursor indicates that an edge of a box is to be moved down and right (south/east)
    * sw-resize = The cursor indicates that an edge of a box is to be moved down and left (south/west)
    * text = The cursor indicates text that may be selected
    * vertical-text = The cursor indicates vertical-text that may be selected
    * w-resize = The cursor indicates that an edge of a box is to be moved left (west)
    * wait = The cursor indicates that the program is busy
    * zoom-in = The cursor indicates that something can be zoomed in
    * zoom-out = The cursor indicates that something can be zoomed out
    * 
    * @param {String} type
    * @returns {UI_Button}
    */
	SetCursor:function(type)
	{
		this.cursor = type;
		return this;
	},
	
   /**
    * Displays the help cursor when the user hovers over the icon.
    * 
    * @returns {UI_Icon}
    */
	CursorHelp:function()
	{
		return this.SetCursor('help');
	},
	
	Hide:function()
	{
		if(this.rendered) {
			this.element.hide();
		}
		
		return this.SetStyle('display', 'none');
	},
	
	Show:function()
	{
		if(this.rendered) {
			this.element.show();
		}
		
		return this.SetStyle('display', 'inline-block');
	},
	
	Push:function()
	{
		this.pushed = true;
		
		if(this.rendered) {
			this.AddClass('active');
		}
		
		return this;
	},
	
	Unpush:function()
	{
		this.pushed = false;
		
		if(this.rendered) {
			this.RemoveClass('active');
		}
		
		return this;
	},
	
	IsShown:function()
	{
		return this.element.is(':visible');
	},
	
	Focus:function()
	{
		if(this.rendered) {
			this.element.focus();
		}
		
		return this;
	},
	
   /**
    * Sets a property: these are freeform properties that can be used 
    * to store custom data in the button. They have no other function.
    * 
    * @param {String} name
    * @param {Mixed} value
    * @returns {UI_Button}
    */
	SetProperty:function(name, value)
	{
		this.properties[name] = value;
		return this;
	},
	
	GetProperty:function(name)
	{
		if(typeof(this.properties[name]) != 'undefined') {
			return this.properties[name];
		}
		
		return null;
	},
	
   /**
    * Displays a confirmation message before the action of
    * the button is executed. The action is only executed 
    * if the user confirms the request.
    * 
    * @return {UI_Button}
    */
	MakeConfirm:function(message)
	{
		this.confirmMessage = message;
		return this;
	}
};

UI_Button = Class.extend(UI_Button);