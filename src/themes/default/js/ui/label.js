/**
 * Utility class to create graphical text labels in different styles. 
 * 
 * @package UI
 * @subpackage Bootstrap
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_Label = 
{
	'variants':['default', 'success', 'warning', 'important', 'info', 'inverse'],
	'type':null,
	'content':null,
	'jsID':null,
	'variant':null,
	'title':null,
	'tooltip':null,
	'postRenderAttempts':null,
	'maxPostRenderAttempts':null,
	'cursor':null,
	'classes':null,
	'styles':null,
	'eventHandlers':null,
	'rendered':null,
	
	init:function(content)
	{
		this.jsID = 'badge'+nextJSID();
		this.content = content;
		this.type = 'label';
		this.variant = 'default';
		this.title = null;
		this.tooltip = false;
		this.postRenderAttempts = 0;
		this.maxPostRenderAttempts = 3;
		this.cursor = null;
		this.classes = [];
		this.styles = [];
		this.rendered = false;
		this.eventHandlers = {
			'click':[]
		};
	},
	
	GetID:function()
	{
		return this.jsID;
	},
	
	SetID:function(id)
	{
		this.jsID = id;
	},
	
	MakeBadge:function()
	{
		this.type = 'badge';
		return this;
	},
	
	MakeSuccess:function() { return this.SetVariant('success'); },
	MakeWarning:function() { return this.SetVariant('warning'); },
	MakeInfo:function() { return this.SetVariant('info'); },
	MakeError:function() { return this.SetVariant('important');	},
	MakeBlocked:function() { return this.SetVariant('inverse');	},
	
	SetType:function(labelType)
	{
		if(labelType=='label' || labelType=='badge') {
			this.type = labelType;
		}
		
		return this;
	},
	
	SetVariant:function(variant)
	{
		if(this.IsVariantValid(variant)) {
			this.variant = variant;
		}
		
		return this;
	},

   /**
    * Adds a class name that will be added to the 
    * icon tag's class attribute.
    * 
    * @param {String} className
    * @return {UI_Label}
    */
	AddClass:function(className)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==className) {
				return this;
			}
		}
		
		this.classes.push(className);
		return this;
	},
	
   /**
    * Adds a style that will be added to the icon
    * tag's style attribute. Do not add the ending
    * semicolon, it is added automatically.
    * 
    * @param {String} style For example "display:inline-block"
    * @return {UI_Label}
    */
	AddStyle:function(style)
	{
		if(!this.HasStyle(style)) {
			this.styles.push(style);
		}
		
		return this;
	},
	
	HasStyle:function(style)
	{
		for(var i=0; i<this.styles.length; i++) {
			if(this.styles[i] == style) {
				return true;
			}
		}
		
		return false;
	},
	
	Render:function()
	{
		var atts = {};
		
		this.AddClass(this.type);
		this.AddClass(this.type + '-' + this.variant);
		
		if(this.title != null) {
			atts['title'] = this.title;
		}

		if(this.cursor != null) {
			this.AddStyle('cursor:'+this.cursor);
		}
		
		atts['id'] = this.jsID;
		atts['class'] = this.classes.join(' ');
		
		if(this.styles.length > 0) {
			atts['style'] = this.styles.join(';');
		}
		
		var html = ''+
		'<span ' + UI.CompileAttributes(atts) + '>' +
			'<span id="'+this.jsID+'-icon" style="display:none"></span>'+
			this.content +
		'</span>';

		var label = this;
		UI.RefreshTimeout(function() {
			label.PostRender();
		});
		
		return html;
	},
	
	PostRender:function()
	{
		var label = this;
		var el = $('#'+this.jsID);
		
		// element not present in the DOM yet? Schedule another
		// attempt a little later.
		if(el.length==0) {
			this.postRenderAttempts++;
			if(this.postRenderAttempts==this.maxPostRenderAttempts) {
				return;
			}
			
			UI.RefreshTimeout(function() {
				label.PostRender();
			});
			return;
		}

		if(this.tooltip) {
			UI.MakeTooltip(el);
		}
		
		el.click(function() {
			label.Handle_Click();
		});
		
		if(this.icon) {
			$('#'+this.jsID+'-icon').html(this.icon+' ').show();
		}
		
		this.rendered = true;
	},
	
	SetTooltip:function(text)
	{
		this.title = text;
		this.tooltip = true;
		
		this.SetCursorHelp();
		return this;
	},
	
	IsVariantValid:function(variant)
	{
		for(var i=0; i<this.variants.length; i++) {
			if(this.variants[i]==variant) {
				return true;
			}
		}

		return false;
	},
	
	SetCursor:function(type)
	{
		this.cursor = type;
		return this;
	},

	SetCursorHelp:function()
	{
		return this.SetCursor('help');
	},
	
	SetCursorPointer:function()
	{
		return this.SetCursor('pointer');
	},
	
	toString:function()
	{
		return this.Render();
	},
	
	log:function(message, category)
	{
		application.log(
			'Label [' + this.jsID + ']',
			message,
			category
		);
	},
	
   /**
    * Adds a handler for click events on the label.
    * @param {Function} handler
    * @returns {UI_Label}
    */
	Click:function(handler)
	{
		this.SetCursor('pointer');
		
		this.eventHandlers['click'].push(handler);
		return this;
	},
	
	Handle_Click:function()
	{
		var label = this;
		$.each(this.eventHandlers['click'], function(idx, handler) {
			handler.call(label);
		});
	},
	
	Destroy:function()
	{
		$('#'+this.jsID).tooltip('destroy');
	},
	
	SetIcon:function(icon)
	{
		this.icon = icon;
		
		if(this.rendered) {
			$('#'+this.jsID+'-icon').html(this.icon+' ').show();
		}
		
		return this;
	}
};

UI_Label = Class.extend(UI_Label);