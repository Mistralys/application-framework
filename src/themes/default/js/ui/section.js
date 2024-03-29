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
		this.id = 'ELS' + nextJSID();
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
		return this.AddClass('compact');
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
		var sectionClasses = this.classes;
		var headerAtts = {};
		var headerClasses = [
			'section-header',
			this.type + '-section-header'
		];

		if(this.collapsible)
		{
			headerAtts['id'] = this.elementID('header');
			headerAtts['data-toggle'] = 'collapse';
			headerAtts['data-target'] = '#' +this.elementID('body');
			headerClasses.push('collapsible');
			sectionClasses.push('collapsible');

		    if(this.collapsed) {
		        headerClasses.push('collapsed');
		    }

		    title += ' '+
			UI.Icon().CaretDown()
				.AddClass('toggle')
				.AddClass('toggle-expand')
				.SetID(this.elementID('expand'))
				.Render()+
			UI.Icon().CaretUp()
				.AddClass('toggle')
				.AddClass('toggle-expand')
				.SetID(this.elementID('collapse'))
				.Render();
		}
		else
		{
			headerClasses.push('regular');
		}
		
		headerAtts['class'] = headerClasses.join(' ');
		
		var bodyAtts = {'id': this.elementID('body')};
		var bodyClasses = ['section-body', 'section-'+this.type+'-body'];

		if(this.collapsible) {
		    bodyClasses.push('collapse');
		    if(!this.collapsed) {
		        bodyClasses.push('in');
		    }
		}
		
		bodyAtts['class'] = bodyClasses.join(' ');
		
		sectionClasses.push('section');
		sectionClasses.push(this.type+'-section');

		var wrapperClasses = ['body-wrapper'];
		var wrapperAtts = {
			'id':this.elementID('body-wrapper'),
		};

		if(this.maxBodyHeight > 0) {
			wrapperClasses.push('max-height');
			wrapperAtts['style'] = 'max-height:'+this.maxBodyHeight+'px;';
		}

		wrapperAtts['class'] = wrapperClasses.join(' ');

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

		this.log('Finished rendering.');

		this.Start();
	},

	/**
	 * @param {String} message
	 */
	log:function(message)
	{
		application.log('Section #' + this.id, message);
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

	/**
	 *
	 * @param {Boolean} collapsed
	 * @returns {UI_Section}
	 */
	SetCollapsed:function(collapsed) 
	{
		if(collapsed !== true) {
			collapsed = false;
		}
		
		if(!this.collapsible) {
			return this;
		}
		
		if(this.collapsed === collapsed) {
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

		this.log('Started the section.');

		if(this.collapsed) {
			this.Handle_Collapsed();
		} else {
			this.Handle_Expanded();
		}
	},
	
	Handle_Expanded:function()
	{
		this.collapsed = false;
		this.element('expand').hide();
		this.element('collapse').show();
		this.element().addClass('section-expanded').removeClass('section-collapsed');
	},
	
	Handle_Collapsed:function()
	{
		this.collapsed = true;
		this.element('expand').show();
		this.element('collapse').hide();
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
