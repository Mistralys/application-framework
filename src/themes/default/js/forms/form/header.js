/**
 * Form header class: used to handle headers in a form. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var FormHelper_Form_Header = 
{
	'form':null,
	'title':null,
	'name':null,
	'elements':null,
	'jsID':null,
	'collapsed':null,
	'bodyEl':null,
	'headerEl':null,
	'caretEl':null,
	'isLast':null,
	'eventHandlers':null,
		
   /**
    * @protected
    */
	init:function(form, name, title)
	{
		this.form = form;
		this.name = name;
		this.title = title;
		this.elements = [];
		this.jsID = nextJSID();
		this.collapsed = true;
		this.rendered = false;
		this.bodyEl = null;
		this.headerEl = null;
		this.caretEl = null;
		this.isLast = false;
		this.eventHandlers = {
			'collapsed':[],
			'expanded':[]
		};
	},
	
	GetElementType:function()
	{
		return 'header';
	},
	
	GetName:function()
	{
		return this.name;
	},
	
	IsHeader:function()
	{
		return true;
	},
	
	IsExpanded:function()
	{
		return !this.collapsed;
	},
	
	Validate:function()
	{
		return true;
	},
	
	Collapse:function()
	{
		this.collapsed = true;
		
		if(this.rendered) {
			this.log('Collapsing the header.', 'ui');
			this.headerEl.addClass('collapsed');
			this.caretEl.removeClass('fa-minus-circle').addClass('fa-plus-circle');
			this.bodyEl.hide();
			this.Handle_Collapsed();
		}
		
		return this;
	},
	
	Expand:function()
	{
		this.collapsed = false;
		
		if(this.rendered) {
			this.log('Expanding the header.', 'ui');
			this.headerEl.removeClass('collapsed');
			this.caretEl.addClass('fa-minus-circle').removeClass('fa-plus-circle');
			this.bodyEl.show();
			this.Handle_Expanded();
		}
		
		return this;
	},
	
	Toggle:function()
	{
		this.log('Toggling the header.', 'ui');
		
		if(this.collapsed) {
			this.Expand();
		} else {
			this.Collapse();
		}
	
		return this;
	},
	
   /**
    * Registers a form element that is contained within this header.
    * 
    * @protected
    * @param {FormHelper_Form_Element} element
    * @return FormHelper_Form_Header
    */
	RegisterElement:function(element)
	{
		this.elements.push(element);
		return this;
	},
	
   /**
    * @protected
    */
	SetLast:function()
	{
		this.isLast = true;
		return this;
	},
	
	IsRequired:function()
	{
		var required = false;
		
		$.each(this.elements, function(idx, element) {
			if(element.IsRequired()) {
				required = true;
				return false;
			}
		});
		
		return required;
	},
	
   /**
    * @protected
    */
	RenderOpening:function()
	{
		var classes = ['modal-subsection'];

		if(this.IsRequired()) {
			classes.push('form-section-required');
		}
		
		if(this.isLast) {
			classes.push('last');
		}
		
		var title = this.title;
		if(this.IsRequired()) {
			title += ' ' +
			UI.Icon().Required()
			.MakeMuted()
			.SetTooltip(t('Contains required fields.'));
		}
		
		var html = '' +
		'<section id="' + this.jsID + '" class="' + classes.join(' ') + '">'+
			'<h3 id="' + this.jsID + '-header" class="modal-subsection-header collapsible">' + 
				title + ' ' +
				'<i class="fa fa-minus-circle muted" id="' + this.jsID + '-caret"></i>'+
			'</h3>'+
			'<div id="' + this.jsID + '-body" class="modal-subsection-body">'+
				'<div class="body-wrapper">';
		
		return html;
	},
	
   /**
    * @protected
    */
	RenderClosing:function()
	{
		return ''+ 
				'</div>'+
			'</div>'+
		'</section>';
	},
	
	Reset:function()
	{
		// do nothing
	},
	
   /**
    * @protected
    */
	PostRender:function()
	{
		this.log('Post rendering, adding event handlers');
		
		var header = this;
		
		this.rendered = true;
		this.bodyEl = $('#'+this.jsID+'-body');
		
		this.bodyEl
		.on('hidden', function() {
			header.Handle_Collapsed();
		})
		.on('shown', function() {
			header.Handle_Expanded();
		});
		
		this.headerEl = $('#'+this.jsID+'-header'); 
		this.caretEl = $('#'+this.jsID+'-caret');
		
		this.headerEl.on('click', function() {
			header.Toggle();
		});
		
		if(this.collapsed) {
			this.log('Initial status is [collapsed].', 'ui');
			this.Collapse();
		}
	},
	
   /**
    * @protected
    */
	Handle_Collapsed:function()
	{
		this.TriggerEvent('collapsed');
	},
	
   /**
    * @protected
    */
	Handle_Expanded:function()
	{
		this.TriggerEvent('expanded');
	},
	
   /**
    * @protected
    */
	log:function(message, category)
	{
		this.form.log('Header ['+this.jsID+'] | ' + message, category);
	},
	
   /**
    * Adds an event handler for the 'collapsed' event.
    * The event handler's this points to the header instance.
    * 
    * @param {Function} handler
    * @returns {FormHelper_Form_Header}
    */
	Collapsed:function(handler)
	{
		return this.AddEventHandler('collapsed', handler);
	},

   /**
    * Adds an event handler for the 'expanded' event.
    * The event handler's this points to the header instance.
    * 
    * @param {Function} handler
    * @returns {FormHelper_Form_Header}
    */
	Expanded:function(handler)
	{
		return this.AddEventHandler('expanded', handler);
	},
	
   /**
    * @protected
    */
	AddEventHandler:function(eventName, handler)
	{
		if(typeof(this.eventHandlers[eventName]) != 'undefined') {
			this.eventHandlers[eventName].push(handler);
		}
		
		return this;
	},
	
   /**
    * @protected
    */
	TriggerEvent:function(eventName)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			return;
		}
		
		this.log('Event | Triggered ['+eventName+'].', 'event');
		
		var header = this;
		$.each(this.eventHandlers[eventName], function(idx, handler) {
			handler.call(header);
		});
	}
};

FormHelper_Form_Header = Class.extend(FormHelper_Form_Header);