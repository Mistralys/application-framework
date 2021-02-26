/**
 * Utility class that handles collapsible and non collapsible
 * content sections in the body of the page.
 * 
 * @package UI
 * @subpackage Bootstrap
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_Section = 
{
	'title':null,
	'content':null,
	'abstractText':null,
	'type':null,
	'group':null,
	'collapsible':null,
	'collapsed':null,
	'eventHandlers':null,
	'rendered':null,
	'classes':null,
	'maxBodyHeight':null,
	
	init:function(title)
	{
		this.title = title;
		this.content = '';
		this.abstractText = null;
		this.type = 'content';
		this.id = nextJSID();
		this.group = '_default';
		this.rendered = false;
		this.collapsible = false;
		this.collapsed = true;
		this.maxBodyHeight = 0;
		this.classes = [];
		this.eventHandlers = {
			'Rendered':[]
		};
	},
	
	SetTitle:function()
	{
		this.title = title;
		return this;
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	MakeCollapsible:function(collapsed)
	{
		if(isEmpty(collapsed)) {
			collapsed = false;
		}
		
		this.collapsible = true;
		this.collapsed = collapsed;
		
		return this;
	},
	
   /**
    * A compact section has less bottom margin, 
    * and is generally made to use up less space
    * when collapsed. This is useful to display
    * large lists of sections.
    * 
    * @return {UI_Section}
    */
	MakeCompact:function()
	{
		return this.AddClass('section-compact');
	},
	
   /**
    * Adds a class to the main section container tag.
    * 
    * @return {UI_Section}
    */
	AddClass:function(className)
	{
		if(!in_array(className, this.classes)) {
			this.classes.push(className);
		}
		
		return this;
	},
	
	SetID:function(id)
	{
		this.id = id;
		return this;
	},
	
	elementID:function(name)
	{
		var result = this.id;
		if(!isEmpty(name)) {
			result += '-'+name;
		}
		
		return result;
	},
	
	element:function(name)
	{
		return $('#'+this.elementID(name));
	},
	
	MakeSidebar:function()
	{
		this.type = 'sidebar';
		return this;
	},
	
	SetAbstract:function(text)
	{
		this.abstractText = text;
		
		if(this.rendered) {
			this.element('abstract').html(text).show();
		}
		
		return this;
	},
	
	SetContent:function(content)
	{
		this.content = content;

		if(this.rendered) {
			this.element('content-container').html(content);
		}
		
		return this;
	},
	
	AppendContent:function(html)
	{
		this.content += html;
		return this;
	},
	
   /**
    * Limits the height of the body of the section 
    * to the specified pixel height. A height above
    * will display a scrollbar.
    * 
    * @return {UI_Section}
    */
	SetMaxBodyHeight:function(height)
	{
		this.maxBodyHeight = height;
		return this;
	},
	
	Render:function()
	{
		var title = this.title;
		var headerAtts = {};
		var headerClasses = ['section-header'];
		if(this.collapsible) {
			headerAtts['id'] = this.elementID('header');
			headerAtts['data-toggle'] = 'collapse';
			headerAtts['data-target'] = '#' +this.elementID('body');
			
		    if(this.collapsed) {
		        headerClasses.push('collapsed');
		        icon = UI.Icon().Expand()
		        .MakeMuted()
		        .SetID(this.elementID('caret'));
		    } else {
		        icon = UI.Icon().Collapse()
		        .MakeMuted()
		        .SetID(this.elementID('caret'));
		    }
		    
		    title += ' '+icon.Render();
		}
		
		headerAtts['class'] = headerClasses.join(' ');
		
		var bodyAtts = {'id': this.elementID('body')};
		var bodyClasses = ['section-body'];
		
		if(this.collapsible) {
		    bodyClasses.push('collapse');
		    if(!this.collapsed) {
		        bodyClasses.push('in');
		    }
		}
		
		bodyAtts['class'] = bodyClasses.join(' ');
		
		var sectionClasses = this.classes; 
		sectionClasses.push(this.type+'-section');
		
		if(this.collapsible) {
			sectionClasses.push('section-collapsible');
		}
		
		var wrapperAtts = {
			'id':this.elementID('body-wrapper'),
			'class':'body-wrapper'
		};
		
		if(this.maxBodyHeight > 0) {
			wrapperAtts['style'] = 'max-height:'+this.maxBodyHeight+'px;overflow:auto';
		}
		
		var html = ''+
		'<section class="'+sectionClasses.join(' ')+'" id="'+this.elementID()+'">'+
			'<h3'+UI.CompileAttributes(headerAtts)+'>'+
				title+
			'</h3>'+
			'<div'+UI.CompileAttributes(bodyAtts)+'>'+
				'<div' + UI.CompileAttributes(wrapperAtts) + '>'+
					this.RenderAbstract()+
					'<div id="'+this.elementID('content-container')+'">'+
						this.content+
					'</div>'+
				'</div>'+
			'</div>'+
		'</section>';
		
		var section = this;
		UI.RefreshTimeout(function() {
			section.Handle_PostRender();
		});
		
		return html;
	},
	
	Rendered:function(handler)
	{
		this.eventHandlers['Rendered'].push(handler);
		return this;
	},
	
	Handle_PostRender:function()
	{
		this.TriggerEvent('Rendered');
		
		this.rendered = true;
	},
	
	TriggerEvent:function(name)
	{
		if(typeof(this.eventHandlers[name]) == 'undefined') {
			return;
		}
		
		var section = this;
		$.each(this.eventHandlers[name], function(idx, handler) {
			handler.call(undefined, section);
		});
	},
	
	GetBodyElement:function()
	{
		return this.element('body-wrapper');
	},
	
	RenderAbstract:function()
	{
		var atts = {
			'id':this.elementID('abstract'),
			'class':'abstract'
		};
		
		if(this.abstractText==null) {
			atts['style'] = 'display:none';
		}

		return '<p'+UI.CompileAttributes(atts)+'>'+this.abstractText+'</p>';
	},
	
	Collapse:function()
	{
		return this.SetCollapsed(true);
	},
	
	Expand:function()
	{
		return this.SetCollapsed(false);
	},
	
	SetCollapsed:function(collapsed) 
	{
		if(collapsed != true) {
			collapsed = false;
		}
		
		if(!this.collapsible) {
			return this;
		}
		
		if(this.collapsed == collapsed) {
			return this;
		}
		
		this.collapsed = collapsed;
		this.element('header').click();
		
		return this;
	},
	
	Start:function()
	{
		var section = this;
		
		this.element('body').on('shown', function() {
			section.Handle_Expanded();
		});

		this.element('body').on('hidden', function() {
			section.Handle_Collapsed();
		});
	},
	
	Handle_Expanded:function()
	{
		this.collapsed = false;
		this.element('caret').addClass('fa-minus-circle').removeClass('fa-plus-circle');
		this.element().addClass('section-expanded').removeClass('section-collapsed');
	},
	
	Handle_Collapsed:function()
	{
		this.collapsed = true;
		this.element('caret').removeClass('fa-minus-circle').addClass('fa-plus-circle');
		this.element().removeClass('section-expanded').addClass('section-collapsed');
	},
	
	SetLast:function()
	{
		this.element().addClass('last');
	},
	
	toString:function()
	{
		return this.Render();
	}
};

UI_Section = Class.extend(UI_Section);