/**
 * UI management hub.
 * 
 * @package Application
 * @subpackage UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class UI
 * @static
 * @main UI
 */
var UI =
{
	'BOOTSTRAP_VERSION':null, // Set serverside
		
   /**
    * @property TooltipDelay
    * @type {Object}
    */
	'TooltipDelay':{
		'show':500,
		'hide':0
	},
	
	'theme':null,
		
   /**
    * Creates a new icon object that can be customized as needed.
    * 
    * @returns {UI_Icon}
    */
	Icon:function()
	{
		return new UI_Icon();
	},
	
   /**
    * Creates a new button object that can be customized as needed.
    * 
    * @param {String} [label]
    * @returns {UI_Button}
    */
	Button:function(label)
	{
		return new UI_Button(label);
	},
	
	Link:function(label)
	{
		return new UI_Link(label);
	},
	
   /**
    * Creates a new dropdown menu button object that can be customized as needed.
    * 
    * @param {String} label The label for the dropdown button.
    * @returns {UI_DropMenu}
    */
	DropMenu:function(label)
	{
		return new UI_DropMenu(label);
	},
	
   /**
    * Creates a new menu object that can be used to create all sorts of menus
    * dynamically.
    * 
    * @return {UI_Menu}
    */
	Menu:function()
	{
		return new UI_Menu();
	},

	/**
	 * Bugfix for draggables in Firefox that are not shown in the correct
	 * position when the page is scrolled: call this in the 'start' event handler
	 * of the draggable configuration.
	 * 
	 * Like this:
	 * 
	 * <pre>
	 * 'start': function(event, ui){
	 *     UI.DragFixStart($(this), event, ui);
	 * }
	 * </pre>
	 * 
	 * <b>NOTE:</b> You also have to apply the fix to the drag event, see DragFixDrag().
	 * 

	 * @param {DOMElement} element
	 * @param {Object} event
	 * @param {Object} ui
	 */
	DragFixStart:function(element, event, ui)
	{
		var sourceElementTop = $(event.currentTarget).offset().top;
		var adjustPosition = false;
		if(ui.position.top != sourceElementTop) {
			console.log('Position mismatch detected, adjusting dragged element position.');
			adjustPosition = true;
			ui.position.top -= $(document).scrollTop();
		}
		
		element.data('adjustPosition', adjustPosition);
	},
	
   /**
    * Bugfix for draggables in Firefox that are not shown in the correct 
    * position when the page is scrolled: call this in the 'drag' event handler
    * of the draggable configuration.
    * 
    * Like this:
    * 
    * <pre>
    * 'drag':function(event, ui) {
    *     UI.DragFixDrag($(this), event, ui);
    * }
    * </pre>
    * 
    * @param {DOMElement} element
    * @param {Object} event
    * @param {Object} ui
    */
	DragFixDrag:function(element, event, ui)
	{
		if(element.data('adjustPosition')) {
			ui.position.top -= $(document).scrollTop();
		}
	},
	
   /**
    * Adds a tooltip to the specified element selector or
    * jQuery DOM element instance, using the global tooltip
    * settings.
    * 
    * @param {String|jQueryElement} elementOrSelector
    * @param {Boolean} [htmlAllowed=false] Whether HTML is allowed 
    */
	MakeTooltip:function(elementOrSelector, htmlAllowed, placement)
	{
		if(isEmpty(htmlAllowed)) {
			htmlAllowed = false;
		}
		
		if(isEmpty(placement)) {
			placement = 'top';
		}
		
		var el = $(elementOrSelector);
		if(el.length==0 || !(el instanceof jQuery) || typeof(el.context)=='undefined') {
			application.log('MakeTooltip', 'Could not find element by selector ['+elementOrSelector+'] to make into a tooltip.', 'debug');
			return;
		}
		
		el.addClass('tooltipified');
		
		/*
		 * Tooltips cause issues when used in conjunction with 
		 * collapsibles, as well as modals: when hovering over the 
		 * tooltipified element, the hidden event somehow gets called
		 * on the collapsible or modal, even though the event does not
		 * actually get executed on them (the collapsible does not actually
		 * collapse).
		 * 
		 * More information on this issue here:
		 * https://github.com/twbs/bootstrap/issues/6942
		 * 
		 * Added the stop propagation of the events, but this is
		 * not a catch all. 
		 */
		el.tooltip({
			'html':htmlAllowed,
			'delay':this.TooltipDelay,
			'container':'body',
			'placement':placement
		}).on('show', function(e) {
			e.stopPropagation();
		}).on('hidden', function(e) {
			e.stopPropagation();
		});
	},
	
	CloseAllTooltips:function()
	{
		$('.tooltipified').tooltip('hide');
		application.log('Closing all tooltips.', 'ui');
	},
	
   /**
    * Compiles a collection of attributes into an attribute
    * string to be used when creating html dynamically.
    * 
    * Example attributes object:
    * 
    * {
    *    'class' => 'button',
    *    'href' => 'http://mistralys.eu'
    * }
    * 
    * Result:
    * 
    * class="button" href="http://mistralys.eu"
    * 
    * @param {Object} attributes Attribute name => value pairs.
    * @return {String}
    */
	CompileAttributes:function(attributes)
	{
		if(isEmpty(attributes)) {
			return '';
		}
		
		var attsString = '';
		$.each(attributes, function(name, value) {
			if(isEmpty(value)) {
				value = '';
			}
			attsString += name+'="'+value+'" ';
		});
		
		// prepend a space if the string is not empty in case
		// it gets inserted right next to the tag name.
		if(attsString.length > 0) {
			attsString = ' '+attsString;
		}
		
		return attsString;
	},
	
   /**
    * Adds a timeout with the global UI refresh setting used to
    * ensure that DOM elements have the time to be inserted by
    * the browser before you use them.
    * 
    * Usage: after inserting HTML into the DOM, use this timeout
    * to delay any operations that you need to do on the newly
    * inserted elements, like attaching event handlers.
    * 
    * @param {Function} handler
    * @param {Integer} [multiplier=1] Multiplies the delay by this amount to create a longer delay.
    */
	RefreshTimeout:function(handler, multiplier)
	{
		if(typeof(multiplier)=='undefined') {
			multiplier = 1;
		}
		
		if(multiplier < 1) {
			multiplier = 1;
		}
		
		return setTimeout(handler, application.uiRefreshDelay*multiplier);
	},
	
   /**
    * The fixed top navigation height has to be taken into account
    * when scrolling somewhere in a page programatically. This returns
    * the height of the navigation bar including a safe bottom margin.
    * 
    * @return {Integer}
    */
	GetNavigationHeight:function()
	{
		var safeMargin = 30;
		var height = 0;
		var el = $('nav.navbar-fixed-top').first();
		if(el.length > 0) {
			height += el.height();
		}
		
		var bars = $('.navbar .navbar-toolbars').first();
		if(bars.length > 0) {
			height += bars.height();
		}
		
		return safeMargin + height;
	},
	
   /**
    * Scrolls the page to the specified offset, taking into account
    * the height of the fixed top navigation.
    * 
    * @param {Integer} offset
    * @param {Object} [options]
    * @param {Integer} [options.delay=500]
    */
	ScrollToOffset:function(offset, options)
	{
		var defaults = {
			'delay':application.scrollToDelay
		};
		
		if(typeof(options)!='object') {
			options = {};
		}
		
		$.each(defaults, function(name, value) {
			if(typeof(options[name]) == 'undefined') {
				options[name] = value;
			}
		});
			
		offset = offset - this.GetNavigationHeight();
		if(offset < 0) {
			offset = 0;
		}

		$('html, body').animate({
		    scrollTop:offset
		},options.delay);
	},
	
   /**
    * Scrolls the page to the specified element, taking into account
    * the height of the fixed top navigation. Returns false if the 
    * target element could not be found.
    * 
    * @param {Integer} offset
    * @param {Object} [options]
    * @param {Integer} [options.delay=500]
    * @return {Boolean}
    */
	ScrollToElement:function(selectorOrElement, options)
	{
		var el = $(selectorOrElement);
		if(el.length==0) {
			return false;
		}

		var offset = el.offset().top;
		UI.ScrollToOffset(offset, options);
		return true;
	},

	/**
	 * Returns the html code required to display a pretty
	 * icon for a yes/no value.
	 *
	 * @param {String|Boolean} value The value for the flag, can be 'yes', 'no' or a boolean true/false.
	 * @return {UI_Icon}
	 */
	RenderYesNoFlag:function(value)
	{
		if(value=='yes' || value=='true' || value==true) {
			return UI.Icon().Yes().MakeSuccess();
		}
		
		return UI.Icon().No().MakeDangerous();
	},
	
   /**
    * Creates a new button group helper object that can be 
    * used to create button groups.
    * 
    * @param {String} [id] Optional ID for the group. Will be prepended with "btnGroup_".
    * @return {UI_ButtonGroup}
    */
	ButtonGroup:function(id)
	{
		return new UI_ButtonGroup(id);
	},
	
	'buttons':[],
	
   /**
    * This is called by buttons when they are instantiated.
    * They are added to the global collection so they can be
    * retrieved using the {@link GetButton} method.
    * 
    * @param {UI_Button} button
    */
	Handle_RegisterButton:function(button)
	{
		this.buttons.push(button);
	},
	
   /**
    * Registers a button that was created serverside so it
    * is accassible in the UI as well.
    * 
    * @param {String} id
    */
	Handle_RegisterServerButton:function(id, layout, type)
	{
		// the button will register itself
		var btn = new UI_Button(
			'', 
			id, 
			true, 
			{
				'layout':layout,
				'type':type
			}
		);
	},
	
   /**
    * Retrieves a button instance by the button ID.
    * 
    * @param {String} id
    * @returns {UI_Button|NULL}
    */
	GetButton:function(id)
	{
		for(var i=0; i<this.buttons.length; i++) {
			if(this.buttons[i].GetID() == id) {
				return this.buttons[i];
			}
		}
		
		return null;
	},
	
	PulsateElement:function(element)
	{
		if(isEmpty(element) || typeof(element.length)=='undefined' || element.length == 0) {
			application.log('UI', 'Cannot pulsate element, not a valid DOM element.', 'error');
			console.log(element);
			return;
		}
			
		element.addClass('pulsating').effect(
			"pulsate",
			{ times:3 },
			1200,
			function() {$(this).removeClass('pulsating');}
		);
	},
	
   /**
    * Creates a new section helper that can be used
    * to render the markup for regular content sections.
    * 
    * @param {String} title
    * @returns {UI_Section}
    */
	Section:function(title)
	{
		return new UI_Section(title);
	},
	
   /**
    * Creates a new sidebar section helper that can be
    * used to render the markup for sidebar sections.
    * 
    * @param {String} title
    * @returns {UI_Section}
    */
	SidebarSection:function(title)
	{
		var section = new UI_Section(title);
		section.MakeSidebar();
		return section;
	},
	
   /**
    * Parses a style object into a style attribute string.
    * 
    * Example styles object:
    * 
    * {
    *    'display' => 'block',
    *    'color' => '#000'
    * }
    * 
    * Result:
    * 
    * display:block;color:#000
    * 
    * @param {Object} styles
    * @returns {String}
    */
	CompileStyles:function(styles)
	{
		if(isEmpty(styles)) {
			return '';
		}
		
		var tokens = [];
		$.each(styles, function(name, value) {
			if(isEmpty(value)) {
				value = '';
			}
			tokens.push(name + ':' + value);
		});
		
		if(tokens.length==0) {
			return '';
		}
		
		return tokens.join(';');
	},
	
	'sections':[],
	
	RegisterSection:function(id, type, collapsible, collapsed, group)
	{
		var section = new UI_Section(null);
		section.id = id;
		section.type = type;
		section.collapsible = collapsible;
		section.collapsed = collapsed;
		section.group = group;
		
		this.sections.push(section);
		
		return section;
	},
	
	GetSection:function(id)
	{
		var found = null;
		$.each(this.sections, function(idx, section) {
			if(section.GetID() == id) {
				found = section;
				return false;
			}
		});
		
		return found;
	},
	
	CollapseSections:function(group)
	{
		$.each(this.sections, function(idx, section) {
			if(section.group == group) {
				section.Collapse();
			}
		});
	},
	
	ExpandSections:function(group)
	{
		$.each(this.sections, function(idx, section) {
			if(section.group == group) {
				section.Expand();
			}
		});
	},
	
	Start:function()
	{
		if (document.createElement("detect").style.zoom === "") {
			$('body').addClass('zoomable');
		} else {
			$('body').addClass('non-zoomable');
		}
		
		$('form').submit(function() {
			UI.formSubmitting = true;
		});
		
		$.each(this.sections, function(idx, section) {
			section.Start();
		});
		
		var groups = this.GetSectionGroups();
		$.each(groups, function(idx, group) {
			var sections = UI.GetGroupSections(group);
			var last = sections[sections.length-1];
			last.SetLast();
		}); 
	},
	
	IsFormSubmitting:function()
	{
		return this.formSubmitting;
	},

	GetGroupSections:function(group)
	{
		var sections = [];
		$.each(this.sections, function(idx, section) {
			if(section.group == group) {
				sections.push(section);
			}
		});
		
		return sections;
	},
	
	GetSectionGroups:function()
	{
		var groups = [];
		$.each(this.sections, function(idx, section) {
			if(!in_array(section.group, groups)) {
				groups.push(section.group);
			}
		});
		
		return groups;
	},
	
   /**
    * Creates a new data grid instance.
    * 
    * @param {String} [id]
    * @returns {UI_Datagrid}
    */
	DataGrid:function(id)
	{
		if(isEmpty(id)) {
			id = nextJSID();
		}
		
		var grid = new UI_Datagrid(id);
		return grid;
	},
	
   /**
    * Checks whether datagrids are enabled/loaded.
    * @return bool
    */
	IsDatagridsEnabled:function()
	{
		return typeof(UI_Datagrid) != 'undefined';
	},
	
   /**
    * Loads all assets required to enable datagrids.
    * 
    * @param {Function} successHandler
    * @param {Function} failureHandler
    */
	EnableDatagrids:function(successHandler, failureHandler)
	{
		if(this.IsDatagridsEnabled()) {
			successHandler.call(undefined);
			return;
		}
		
		application.loadScripts(
			[
				'ui/datagrid.js', 
				'ui/datagrid/column.js',
				'ui/datagrid/entry.js'
			],
			successHandler,
			failureHandler
		);
	},

   /**
    * Creates a new HTML text instance. This very simple object
    * allows finer control over the rendering of text, as well
    * as the possibility to easily modify the text dynamically.
    * 
    * @param {String} text
    * @returns {UI_Text}
    */
	Text:function(text)
	{
		return new UI_Text(text);
	},
	
	
   /**
    * Creates a graphically large-styled list intended to
    * be used to select from few options, to highlight the
    * choices. Builds on the regular menu.
    *  
    * @return {UI_BigSelection}
    */
	BigSelection:function()
	{
		return new UI_BigSelection();
	},
	
   /**
    * Creates a new label instance for rendering label elements.
    * 
    * @param {String} content 
    * @return {UI_Label}
    */
	Label:function(content)
	{
		var label = new UI_Label(content);
        label.SetType('label');
        return label;
	},

   /**
    * Creates a new badge instance for rendering badges.
    * 
    * @param {String} content
    * @return {UI_Badge}
    */
	Badge:function(content)
	{
		return this.Label(content).SetType('badge');
	},
	
	SetTheme:function(id, url)
	{
		this.theme = new UI_Theme(id, url);
	},
	
   /**
    * @return {UI_Theme}
    */
	GetTheme:function()
	{
		return this.theme;
	}
};
