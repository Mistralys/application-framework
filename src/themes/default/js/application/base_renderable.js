var Application_BaseRenderable = 
{
	'ERROR_MISSING_METHOD':15101,
		
	'jsID':null,
	'rendered':null,
		
	init:function()
	{
		this.jsID = nextJSID();
		this.rendered = false;
	},
		
   /**
    * Retrieves a dom element by its name.
    * 
    * @param {String} name
    * @returns {jQuery}
    */
	element:function(name)
	{
		return $('#'+this.elementID(name));
	},

   /**
    * Retrieves a dom element name unique to this class.
    * The specified name can be used to retrieve it later,
    * it is automatically namespaced.
    * 
    * @param {String} name
    * @returns {jQuery}
    */
	elementID:function(name)
	{
		if(typeof(name)!='undefined') {
			return this.jsID+'_'+name;
		}

		return this.jsID;
	},
	
   /**
    * Renders the element to string.
    * @returns {String}
    */
	Render:function()
	{
		var markup = this._Render();
		
		var renderable = this;
		UI.RefreshTimeout(function() {
			renderable.Handle_PostRender();
		});
		
		return markup;
	},
	
   /**
    * Actual implementation of the element's rendering,
    * should be implemented by the concrete class.
    * 
    * @abstract
    * @protected
    */
	_Render:function()
	{
		return '';
	},
	
	Handle_PostRender:function()
	{
		this._PostRender();
		this.rendered = true;
	},
	
	_GetTypeName:function()
	{
		throw new ApplicationException(
			'Missing method',
			'The [_GetTypeName] method has to be implemented.',
			this.ERROR_MISSING_METHOD
		);
	},
	
	_PostRender:function()
	{
		
	},
	
	toString:function()
	{
		return this.Render();
	},
	
	IsRendered:function()
	{
		return this.rendered;
	},
	
	log:function(message, category)
	{
		application.log(this._GetTypeName() + ' [' + this.jsID + ']', message, category);
	},
	
	logError:function(message)
	{
		this.log(message, 'error');
	}
};

Application_BaseRenderable = Class.extend(Application_BaseRenderable);

var Application_RenderableHTML = 
{
	'classes':null,
	'attributes':null,
	'styles':null,
	'eventHandlers':null,
	
	init:function()
	{
		this._super();
		
		this.classes = [];
		this.attributes = {};
		this.styles = {};
		this.eventHandlers = {};
	},

   /**
    * Adds a class to the button tag.
    *
    * @param {String} className
    * @returns {UI_Renderable_InterfaceHTML}
    */
	AddClass:function(className)
	{
		if(!this.HasClass(className)) {
			this.classes.push(className);
		}
		
		if(this.IsRendered()) {
			this.element().addClass(className);
		}
		
		return this;
	},
	
   /**
    * Removes a class from the button tag. Has no effect
    * if it does not have the class.
    * 
    * @param {String} className
    * @returns {UI_Renderable_InterfaceHTML}
    */
	RemoveClass:function(className)
	{
		var keep = [];
		$.each(this.classes, function(idx, item) {
			if(item != className) {
				keep.push(item);
			}
		});
		
		this.classes = keep;
		
		if(this.IsRendered()) {
			this.element().removeClass(className);
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
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==className) {
				return true;
			}
		}
		
		return false;
	},

   /**
    * Sets an attribute of the element. Note that this will
    * not work for setting attributes like the type or class
    * attributes since these are handled separately.
    *
    * @param {String} name
    * @param {String} value
    * @returns {UI_Renderable_InterfaceHTML}
    */
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	},
	
   /**
    * Removes the specified attribute.
    * 
    * @param {String} name
    * @return {UI_Renderable_InterfaceHTML}
    */
	RemoveAttribute:function()
	{
		if(typeof(this.attributes[name]) != 'undefined') {
			delete this.attributes[name];
		}
		
		return this;
	},
	
   /**
    * Sets a style of the element's style attribute.
    * 
    * Examples: 
    * 
    * AddStyle('font-family', 'Arial');
    * AddStyle('display', 'none');
    *
    * @param style The style to set
    * @param value The value to set for the style
    * @returns {UI_Renderable_InterfaceHTML}
    */
	SetStyle:function(style, value)
	{
		this.styles[style] = value;
		
		if(this.IsRendered()) {
			this.element().css(style, value);
		}
		
		return this;
	},	
	
	RemoveStyle:function(style)
	{
		if(typeof(this.styles[style]) != 'undefined') {
			delete this.styles[style];
		} 
		
		if(this.IsRendered()) {
			this.element().css(style, '');
		}
		
		return this;
	},
	
   /**
    * Retrieves a style value.
    * 
    * @param {String} style
    * @returns {String}
    */
	GetStyle:function(style)
	{
		if(typeof(this.styles[style]) != 'undefined') {
			return this.styles[style];
		}
		
		return null;
	},
	
   /**
    * Renders all attributes to string. This includes class and 
    * style attributes.
    * 
    * @returns {String}
    */
	RenderAttributes:function()
	{
		var atts = this.attributes;
		
		if(typeof(atts['id']) == 'undefined') {
			atts['id'] = this.jsID;
		}
		
		atts['class'] = this.classes.join(' ');
		atts['style'] = UI.CompileStyles(this.styles);
		
		return UI.CompileAttributes(atts);
	},
	
   /**
    * Adds an event handler for the specified event. All event
    * handlers get the renderable object instance as first argument,
    * additional parameters depend on the event.
    * 
    * @param {String} eventName
    * @param {Function} handler
    * @returns {Application_RenderableHTML}
    */
	AddEventHandler:function(eventName, handler)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			this.eventHandlers[eventName] = [];
		}
		
		this.eventHandlers[eventName].push(handler);
		return this;
	},
	
   /**
    * Triggers the specified event. 
    * 
    * The handler function gets the renderable instance
    * as first parameter. <code>this</code> is undefined.
    * Any additional arguments are passed on to the event 
    * handling functions.
    *  
    * @param {String} eventName
    */
	TriggerEvent:function(eventName)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined' || this.eventHandlers[eventName].length == 0) {
			return;
		}
		
		var args = [];
		args.push(this);
		
		for(var i=1; i < arguments.length; i++) {
			args.push(arguments[i]);
		}
		
		$.each(this.eventHandlers[eventName], function(idx, handler) {
			handler.apply(undefined, args);
		});
	},

   /**
    * Checks if at least one event handler has been added for the specified event.
    * @param {String} eventName
    * @returns {Boolean}
    */
	HasEventHandler:function(eventName)
	{
		if(typeof(this.eventHandlers[eventName]) != 'undefined' && this.eventHandlers[eventName].length > 0 ) {
			return true;
		}
		
		return false;
	},
	
	Hide:function()
	{
		if(this.IsRendered()) {
			this.element().hide();
		}
		
		return this.SetStyle('display', 'none');
	},
	
	Show:function()
	{
		if(this.IsRendered()) {
			this.element().show();
		}
		
		return this.RemoveStyle('display');
	}
};

Application_RenderableHTML = Application_BaseRenderable.extend(Application_RenderableHTML);