/**
 * Handles individial clientside forms, from the element 
 * collection and rendering to HTML to its validation.
 * 
 * To create a new form:
 * 
 * <pre>
 * FormHelper.createForm();
 * </pre>
 * 
 * @class
 * @see FormHelper.createForm
 */
var FormHelper_Form = 
{
	'ERROR_CANNOT_ADD_ELEMENT_ALREADY_EXISTS':529001,
		
	'id':null,
	'elements':null,
	'eventHandlers':null,
	'postRenderAttempts':null,
	'defaultElementName':null,
	'submits':null,
	'rendering':null,
	'rendered':null,
	'title':null,
	'abstractText':null,
	'activeHeader':null,
	'headers':null,
	'rules':null,
	'options':null,
	
   /**
    * Constructor. 
    * @param {String} name
    */
	init:function(name)
	{
		this.id = 'fm'+nextJSID();
		this.elements = [];
		this.postRenderAttempts = 0;
		this.defaultElementName = null;
		this.submits = [];
		this.rendered = false;
		this.rendering = false;
		this.title = null;
		this.abstractText = null;
		this.activeHeader = null;
		this.headers = [];
		this.rules = [];
		
		this.options = {
			'timeout-multiplier':1
		};
		
		this.eventHandlers = {
			'submit':[],
			'postRendered':[]
		};
		
		if(isEmpty(name)) {
			name = this.id;
		}
		
		this.name = name;
	},
	
   /**
    * Adds a pure HTML element that can be used to add
    * arbitrary HTML code to the form.
    * 
    * @param {String} name
    * @param {String} html
    * @returns {FormHelper_Form_Element_HTML}
    */
	AddHTML:function(name, html)
	{
		if(isEmpty(html)) {
			html = '';
		}
		
		var el = new FormHelper_Form_Element_HTML(this, name, html);
		return this.AddElement(el);
	},
	
	AddHint:function(hint, name)
	{
		if(isEmpty(name)) {
			name = 'hint'+nextJSID();
		}
		
		var html = ''+
		'<div class="form-hints">'+
			UI.Icon().Information().MakeInformation()+' '+
			'<b>'+t('Tip:')+'</b> '+
			hint+
		'</div>';
		
		return this.AddHTML(name, html);
	},
	
   /**
    * Adds a static element that is rendered like a regular form
    * element, but where the input itself is static content that
    * can be set using the element's <code>SetContent</code> method.
    * 
    * @param {String} name
    * @param {String} label
    * @returns {FormHelper_Form_Element_Static}
    */
	AddStatic:function(name, label)
	{
		var el = new FormHelper_Form_Element_Static(this, name, label);
		return this.AddElement(el);
	},
	
	AddHidden:function(name, value)
	{
		var el = new FormHelper_Form_Element_Hidden(this, name, value);
		return this.AddElement(el);
	},
	
   /**
    * Adds a regular checkbox element.
    * 
    * @param {String} name
    * @param {String} label
    * @returns {FormHelper_Form_Element_Checkbox}
    */
	AddCheckbox:function(name, label)
	{
		var el = new FormHelper_Form_Element_Checkbox(this, name, label);
		return this.AddElement(el);
	},
	
	AddText:function(name, label)
	{
		var el = new FormHelper_Form_Element_Text(this, name, label);
		return this.AddElement(el);
	},
	
	AddInteger:function(name, label, min, max)
	{
		return this.AddText(name, label)
		.MakeWidthS()
		.AddFilterTrim()
		.AddRuleInteger(min, max)
		.SetValue(min+'');
	},
	
	AddAlias:function(name, label)
	{
		if(isEmpty(name)) {
			name = 'alias';
		}
		
		if(isEmpty(label)) {
			label = t('Alias');
		}
		
		return this.AddText(name, label)
		.MakeWidthXXL()
		.AddFilterTrim()
		.AddRuleAlias();
	},
	
	AddLabel:function(name, label)
	{
		if(isEmpty(name)) {
			name = 'label';
		}
		
		if(isEmpty(label)) {
			label = t('Label');
		}
		
		return this.AddText(name, label)
		.MakeWidthXXL()
		.AddFilterTrim()
		.AddRuleLabel();
	},

	AddTextarea:function(name, label)
	{
		var el = new FormHelper_Form_Element_Textarea(this, name, label);
		return this.AddElement(el);
	},
	
	AddSwitch:function(name, label)
	{
		var el = new FormHelper_Form_Element_Switch(this, name, label);
		return this.AddElement(el);
	},

	AddSelect:function(name, label)
	{
		var el = new FormHelper_Form_Element_Select(this, name, label);
		return this.AddElement(el);
	},
	
	AddElement:function(element)
	{
		var name = element.GetName();
		if(this.GetElementByName(name)) {
			throw new ApplicationException(
				'Element already exists',
				'Cannot add element ['+name+'] of type ['+element.GetElementType()+'], an element with the same name already exists.',
				this.ERROR_CANNOT_ADD_ELEMENT_ALREADY_EXISTS
			);
		}
		
		this.elements.push(element);
		
		if(this.activeHeader != null && !element.IsHeader()) {
			this.activeHeader.RegisterElement(element);
		}
		
		return element;
	},
	
   /**
    * Creates a form header.
    * 
    * @param {String} name A name to be able to easily retrieve the header instance
    * @param {String} title The actual title text for the header
    * @returns {FormHelper_Form_Header}
    */
	AddHeader:function(name, title)
	{
		var header = new FormHelper_Form_Header(this, name, title);
		this.activeHeader = header;
		this.AddElement(header);
		this.headers.push(header);
		return header;
	},
	
   /**
    * Adds a button to the end of the form, and returns
    * the button instance. The button has to be configured
    * further, it has no event handlers.
    * 
    * @param {String} label
    * @returns {UI_Button}
    */
	AddButton:function(label)
	{
		var btn = UI.Button(label);
		this.submits.push(btn);
		return btn;
	},
	
   /**
    * Adds a submit button instance to the form, and
    * returns the instance. The button has the submit
    * event handler set, and is set as primary.
    * 
    * @param {String} label
    * @returns {UI_Button}
    */
	AddButtonSubmit:function(label)
	{
		var btn = this.AddButton(label);
		btn.MakePrimary();
		
		var form = this;
		btn.Click(function() {
			form.Handle_Submit();
		});

		return btn;
	},
	
   /**
    * Focuses on the specified element in the form.
    * @param {String} name
    * @return FormHelper_Form
    */
	FocusElement:function(name)
	{
		var el = this.GetElementByName(name);
		if(el) {
			el.Focus();
		}
		
		return this;
	},
	
   /**
    * Sets which element is the default and will be focused on initially when starting the form.
    * @param {String} name
    * @returns {FormHelper_Form}
    */
	SetDefaultElement:function(name)
	{
		this.defaultElementName = name;
		return this;
	},
	
   /**
    * Retrieves an element by its name.
    * @param {String} name
    * @returns {jQuery|NULL}
    */
	GetElementByName:function(name)
	{
		for(var i=0; i < this.elements.length; i++) {
			var el = this.elements[i];
			if(el.GetName()==name) {
				return el;
			}
		}
		
		return null;
	},
	
   /**
    * Retrieves an element by its ID.
    * @param {String} id
    * @returns {jQuery|NULL}
    */
	GetElementByID:function(id)
	{
		for(var i=0; i < this.elements.length; i++) {
			var el = this.elements[i];
			if(el.GetID()==id) {
				return el;
			}
		}
		
		return null;
	},
	
	GetElements:function()
	{
		return this.elements;
	},
	
	GetOption:function(name)
	{
		if(typeof(this.options[name]) != 'undefined') {
			return this.options[name];
		}
		
		return null;
	},
	
	SetOption:function(name, value)
	{
		this.options[name] = value;
		return this;
	},
	
   /**
    * Sets the timeout multiplier to use for triggering
    * the post rendering. Defaults to 1, can be increased
    * if the rendering is deferred further.
    */
	SetPostRenderMultiplier:function(multiplier)
	{
		this.SetOption('timeout-multiplier', multiplier);
	},
	
	Render:function()
	{
		if(this.rendered || this.rendering) {
			return;
		}
		
		this.log('Rendering all controls.', 'ui');
		
		this.rendering = true;
		var titleHidden = ' style="display:none"';
		if(this.title != null) {
			titleHidden = '';
		}
		
		var abstractHidden = ' style="display:none"';
		if(this.abstractText != null) {
			abstractHidden = '';
		}
		
		if(this.headers.length > 0) {
			this.headers[this.headers.length-1].SetLast();
		}
		
		var html = ''+ 
		'<div id="'+this.id+'-messages" class="form-messages"></div>'+
		'<form class="form-horizontal" id="'+this.id+'" name="'+this.name+'">'+
			'<h4 class="dialog-form-title" id="'+this.elementID('title')+'"'+titleHidden+'>'+this.title+'</h4>'+
			'<p class="dialog-form-abstract" id="'+this.elementID('abstract')+'"'+abstractHidden+'>'+this.abstractText+'</p>'+
			'<div class="hiddens">';
				$.each(this.elements, function(idx, element) {
					if(element.GetElementType()=='Hidden') {
						html += element.Render();
					}
				})
				html += ''+
			'</div>';
				
			var prevHeader = null;
			$.each(this.elements, function(idx, element) {
				if(element.GetElementType()=='Hidden') {
					return;
				}
				
				// headers have opening and closing tags, as they
				// act like collapsible form sections.
				if(element.IsHeader()) {
					if(prevHeader != null) {
						html += prevHeader.RenderClosing();
					}
					
					prevHeader = element;
					html += element.RenderOpening();
					return;
				}
				
				html += element.Render();
			});
			
			if(prevHeader != null) {
				html += prevHeader.RenderClosing(); 
			}
			
			if(this.submits.length > 0) {
				html += ''+
				'<div class="form-actions">';
					for(var i=0; i<this.submits.length; i++) {
						html += this.submits[i].Render() + ' '; 
					}
				'</div>';
			}
			html += ''+
			FormHelper.renderDummySubmit()+
		'</form>';
			
		var form = this;
		UI.RefreshTimeout(
			function() {
				form.PostRender();
			},
			this.GetOption('timeout-multiplier')
		);
			
		return html;
	},
	
   /**
    * Creates a form-specific element ID.
    * @protected
    * @param {String} partName
    * @returns {String}
    */
	elementID:function(partName)
	{
		return this.id+'_'+partName;
	},
	
   /**
    * Gets a form-specific HTML element by its part name.
    * @protected
    * @param {String} partName
    * @returns {jQuery}
    */
	element:function(partName)
	{
		return $('#'+this.elementID(partName));
	},
	
   /**
    * @protected
    */
	PostRender:function()
	{
		this.log('Executing the post render.', 'ui');
		
		var form = this;
		
		var el = this.GetFormElement();
		var maxAttempts = 4;
		if(el.length==0) {
			if(this.postRenderAttempts < maxAttempts) {
				this.postRenderAttempts++;
				this.log('Form not found, attempt '+this.postRenderAttempts+'/'+maxAttempts+'.', 'ui');
				UI.RefreshTimeout(function() {
					form.PostRender();
				});
			} else {
				this.log('Too many attempts, aborting.', 'error');
			}
			return;
		}
		
		el.on('submit', function(event) {
			event.preventDefault();
			form.Handle_Submit();
		});
		
		for(var i=0; i < this.elements.length; i++) {
			this.elements[i].PostRender();
		}
		
		if(this.defaultElementName != null) {
			this.log('Focussing on default element ['+this.defaultElementName+'].', 'ui');
			this.FocusElement(this.defaultElementName);
		}
		
		this.rendering = false;
		this.rendered = true;
		
		this.SetTitle(this.title);
		this.SetAbstract(this.abstractText);
		
		this.log('Executing ['+this.eventHandlers.postRendered.length+'] post render event handlers.', 'event');
		
		for(var i=0; i < this.eventHandlers.postRendered.length; i++) {
			this.eventHandlers.postRendered[i].call(this, this);
		}
	},
	
   /**
    * Retrieves the main form tag element.
    * @returns {jQuery}
    */
	GetFormElement:function()
	{
		return $('#'+this.id);
	},
	
   /**
    * Submits the form, or if a callback is specified, adds
    * an event handler for the form's submit event.
    * 
    * The handler function gets two arguments: the form's
    * values collection and the form object instance.
    * 
    * @param {Function} handler
    * @returns {FormHelper_Form}
    */
	Submit:function(handler)
	{
		if(isEmpty(handler)) {
			this.GetFormElement().submit();
			return this;
		}

		return this.AddEventHandler('submit', handler);
	},
	
   /**
    * Adds an event handler to the specified event.
    * @protected
    * @param {String} event The name of the event, e.g. "submit"
    * @param {Function} handler The handler function
    * @returns {FormHelper_Form}
    */
	AddEventHandler:function(event, handler)
	{
		this.eventHandlers[event].push(handler);
		return this;
	},
	
   /**
    * Called when the form is submitted. Calls all submit 
    * handler callback functions.
    * 
    * @protected
    */
	Handle_Submit:function()
	{
		if(!this.Validate()) {
			return;
		}
		
		var values = this.GetValues();
		
		this.log('Form has been submitted. Values follow:', 'event');
		this.log(values, 'data');
		
		this.log('Found ['+this.eventHandlers.submit.length+'] submit event handlers, calling them.', 'event');
		
		for(var i=0; i < this.eventHandlers.submit.length; i++) {
			this.eventHandlers.submit[i].call(undefined, values, this);
		}
	},
	
   /**
    * Goes through all elements and validates them, and returns the 
    * validation status.
    * 
    * @returns {Boolean}
    */
	Validate:function()
	{
		var valid = true;
		for(var i=0; i < this.elements.length; i++) {
			if(!this.elements[i].Validate()) {
				valid = false;
			}
		}
		
		var msgEL = $('#'+this.id+'-messages').hide();
		var messages = [];
		
		if(valid) {
			for(var i=0; i < this.rules.length; i++) {
				var rule = this.rules[i];
				if(rule.callback.call(this) != true) {
					messages.push(rule.message);
					valid = false;
				}
			}
		}
		
		if(messages.length > 0) {
			msgEL.html(
				'<p class="text-error">'+
					UI.Icon().Warning().MakeDangerous() + ' ' +
					'<b>' + t('Please review the following errors:') + '</b>' +
				'</p>'+
				'<ul class="unstyled">'+
					'<li>'+messages.join('</li><li>')+'</li>'+
				'</ul>'
			)
			.show();
		}
		
		return valid;
	},
	
   /**
    * Retrieves an object with element name => value pairs
    * for all elements in the form, including hidden element
    * values.
    * 
    * @returns {Object}
    */
	GetValues:function()
	{
		var values = {};
		for(var i=0; i < this.elements.length; i++) {
			var el = this.elements[i];
			if(el.IsHeader()) {
				continue;
			}
			
			var name = el.GetName();
			values[name] = el.GetValue();
		}
		
		return values;
	},
	
   /**
    * Retrieves a value by the element's name.
    * @param {String} name
    * @returns {String}
    */
	GetValue:function(name)
	{
		var el = this.GetElementByName(name);
		if(el) {
			return el.GetValue();
		}
		
		return null;
	},
	
   /**
    * Resets all elements in the form to empty values.
    * @return {FormHelper_Form}
    */
	Reset:function()
	{
		$.each(this.elements, function(idx, element) {
			element.Reset();
		});
		
		return this;
	},
	
   /**
    * Sets all of the specified values in the form, using
    * element name => value pairs. If an element cannot be
    * found by its name, it is silently ignored.
    * 
    * @param {Object}
    * @return {UI_Form}
    */
	SetValues:function(values)
	{
		if(isEmpty(values)) {
			return this;
		}
		
		// delay setting the values if the form has not
		// finished rendering. If it has been rendered
		// or not, the elements themselves will handle
		// the rest otherwise.
		if(this.rendering) {
			this.log('Delaying setting the values, the form has not finished rendering.', 'ui');
			var form = this;
			UI.RefreshTimeout(function() {
				form.SetValues(values);
			});
			
			return;
		}
		
		var form = this;
		$.each(values, function(name, value) {
			var el = form.GetElementByName(name);
			if(el) {
				el.SetValue(value);
			}
		});
		
		return this;
	},
	
	toString:function()
	{
		return this.Render();
	},
	
   /**
    * Adds a log message for the form.
    * @protected
    * @param {String} message
    * @param {String} category
    */
	log:function(message, category)
	{
		application.log('Client form | JSID ['+this.id+' "'+this.name+'"]', message, category);
	},
	
   /**
    * Adds an event handler for the form's postRendered event, wich
    * is called when the form has successfully completed its post 
    * rendering.
    * 
    * @param {Function} handler
    * @return {FormHelper_Form}
    */
	PostRendered:function(handler)
	{
		return this.AddEventHandler('postRendered', handler);
	},
	
   /**
    * Sets a title for the form, which gets rendered as a header
    * at the start of the form.
    * 
    * @param {String} title
    * @returns {FormHelper_Form}
    */
	SetTitle:function(title)
	{
		if(this.rendered) {
			if(isEmpty(title)) {
				this.element('title').hide();
			} else {
				this.element('title').show().html(title);
			}
		}
		
		this.title = title;
		return this;
	},
	
	SetAbstract:function(text)
	{
		if(this.rendered) {
			if(isEmpty(text)) {
				this.element('abstract').hide();
				this.element('title').removeClass('with-abstract');
			} else {
				this.element('abstract').show().html(text);
				this.element('title').addClass('with-abstract');
			}
		}
		
		this.abstractText = text;
		return this;
	},
	
   /**
    * Adds a rule globally to the form itself: this can be used to
    * validate relations between fields for example.
    * 
    * Note: these are run after all elements in the form have
    * been validated and are valid.
    * 
    * @param {Function} validationFunction
    * @param {String} errorMessage
    * @returns {FormHelper_Form}
    */
	AddRule:function(validationFunction, errorMessage)
	{
		this.rules.push({
			'callback':validationFunction,
			'message':errorMessage
		});
		
		return this;
	},
	
	Hide:function()
	{
		this.GetFormElement().hide();
	},
	
	Show:function()
	{
		this.GetFormElement().show();
	}

};

FormHelper_Form = Class.extend(FormHelper_Form);
