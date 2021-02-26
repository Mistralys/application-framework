/**
 * Base class for form elements. This is extended by
 * all available form elements. 
 * 
 * @package Application
 * @subpackage Forms
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var FormHelper_Form_Element = 
{
	'ERROR_LIVE_VALUE_METHOD_NOT_IMPLEMENTED':530001,
	'ERROR_LIVE_SET_VALUE_METHOD_NOT_IMPLEMENTED':530002,
	'ERROR_UNKNOWN_EVENT':530003,
		
	'form':null,
	'name':null,
	'id':null,
	'label':null,
	'helpText':null,
	'required':null,
	'structural':null,
	'attributes':null,
	'classes':null,
	'styles':null,
	'value':null,
	'filters':null,
	'rules':null,
	'hsize':null,
	'append':null,
	'hsizeClasses':null,
	'eventHandlers':null,
	'triggerEvents':null,
	'autoHelp':null,
	'ruleTypes':null,
	'tags':null,
		
	init:function(form, name, label)
	{
		this.form = form;
		this.name = name;
		this.label = label;
		this.required = false;
		this.structural = false;
		this.helpText = null;
		this.id = 'ff'+nextJSID();
		this.attributes = {};
		this.styles = {};
		this.classes = [];
		this.value = null;
		this.rendered = false;
		this.filters = [];
		this.rules = [];
		this.hsize = null;
		this.append = '';
		this.eventHandlers = {};
		this.triggerEvents = true;
		this.autoHelp = [];
		this.ruleTypes = [];
		this.tags = [];
		this.hsizeClasses = {
			'xs':'input-mini',
			's':'input-small',
			'm':'input-medium',
			'l':'input-large',
			'xl':'input-xlarge',
			'xxl':'input-xxlarge'
		};
		
		this.eventHandlers = {
			'change':[]
		};
		
		this._init();
	},
	
   /**
    * @protected
    */
	_init:function()
	{
		// can be extended for element-specific init
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	GetName:function()
	{
		return this.name;
	},
	
	GetLabel:function()
	{
		return this.label;
	},
	
	GetValue:function()
	{
		if(this.rendered) {
			// initialize the value from the live element
			var updated = this.UpdateValue();
			if(typeof(updated)=='undefined') {
				this.log('Element has an undefined live value.', 'error');
				updated = '';
			}
			
			this.triggerEvents = false;
			this.SetValue(updated);
			this.triggerEvents = true;
		}

		return this.value;
	},
	
	SetValue:function(value)
	{
		this.value = value;
		
		if(this.rendered) {
			this._SetLiveValue(value);
			this.Change();
		}
		
		return this;
	},
	
	IsRequired:function()
	{
		return this.required;
	},
	
	GetHelpText:function()
	{
		return this.helpText;
	},
	
	GetElementType:function()
	{
		throw new Exception('Type method not implemented.');
	},
	
	GetElementInstance:function()
	{
		return $('#'+this.id);
	},
	
	SetAttribute:function(name, value)
	{
		if(this.rendered) {
			this.GetElementInstance().attr(name, value);
		}
		
		this.attributes[name] = value;
		return this;
	},
	
	SetStyle:function(style, value)
	{
		if(this.rendered) {
			this.GetElementInstance().css(style, value);
		}
		
		this.styles[style] = value;
		return this;
	},
	
   /**
    * Marks the element as required. A required validation
    * rule is used automatically. Can be used after the 
    * element has been rendered.
    * 
    * @returns {FormHelper_Form_Element}
    */
	MakeRequired:function()
	{
		if(!this.required) {
			this.required = true;
	
			if(this.rendered) {
				$('#'+this.id+'-required').addClass('active');
			}
		}
		
		return this;
	},
	
   /**
    * Marks the element as being structural, meaning that the state
    * of the record will be affected by changes to this data key.
    * 
    * @returns {FormHelper_Form_Element}
    */
	MakeStructural:function()
	{
		if(!this.structural) {
			this.structural = true;
			
			if(this.rendered) {
				$('#'+this.id+'-structural').addClass('active');
			}
		}
		
		return this;
	},
	
   /**
    * @returns {Boolean}
    */
	IsStructural:function()
	{
		return this.structural;
	},
	
   /**
    * Disables the element.
    * @returns {FormHelper_Form_Element}
    */
	MakeDisabled:function()
	{
		return this.SetAttribute('disabled', 'disabled');
	},
	
   /**
    * Enables the element after having disabled it using {@link MakeDisabled()}.
    * @returns {FormHelper_Form_Element}
    */
	MakeEnabled:function()
	{
		return this.RemoveAttribute('disabled');
	},

   /**
    * Removes an attribute of the element if it exists.
    * @param {String} name
    * @returns {FormHelper_Form_Element}
    */
	RemoveAttribute:function(name)
	{
		if(this.rendered) {
			this.GetElementInstance().removeAttr(name);
		}
		
		if(typeof(this.attributes[name] != 'undefined')) {
			delete this.attributes[name];
		}
		
		return this;
	},
	
   /**
    * Reverts a required element back to its original optional state.
    * Has no effect if it is not required. Can be used after the element
    * has been rendered. 
    * 
    * @returns {FormHelper_Form_Element}
    */
	MakeOptional:function()
	{
		if(this.required) {
			this.required = false;
			
			if(this.rendered) {
				$('#'+this.id+'-required').removeClass('active');
			}
		}
		
		return this;
	},
	
   /**
    * Focuses on this element.
    * @returns {FormHelper_Form_ElementClass}
    */
	Focus:function()
	{
		this.GetElementInstance().focus();
		return this;
	},
	
   /**
    * Adds a class to the element. In case of composite elements
    * that do not have a main element, the class will typically
    * be added to the element container.
    * 
    * @param {String} className
    * @return FormHelper_Form_Element
    */
	AddClass:function(className)
	{
		if(!this.HasClass(className)) {
			this.classes.push(className);
		}
		
		return this;
	},
	
   /**
    * Check whether the element already has the specified class name.
    * @param {String} className
    * @return boolean
    */
	HasClass:function(className)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i] == className) {
				return true;
			}
		}
		
		return false;
	},
	
	RemoveClass:function(className)
	{
		var keep = [];
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i] != className) {
				keep.push(className);
			}
		}
		
		this.classes = keep;
		return this;
	},
	
	Render:function()
	{
		// add the size class to the element which controls how wide it is
		if(this.hsize != null) {
			this.AddClass(this.hsizeClasses[this.hsize]);
		}
		
		return ''+
		'<div id="'+this.id+'-container" style="display:inline;">'+
			FormHelper.renderItem(
				this.GetLabel(),
				this.GetID(),
				this._Render(),
				this.IsRequired(),
				this.RenderHelpText(),
				['control-type-'+this.GetElementType().toLowerCase()],
				this.IsStructural()
			)+
		'</div>';
	},
	
   /**
    * @protected
    */
	_Render:function()
	{
		return 'Element type '+this.GetElementType();
	},
	
   /**
    * @protected
    */
	PostRender:function()
	{
		this.rendered = true;

		this._PostRender();
		
		// attach all event handlers
		var element = this;
		$.each(this.eventHandlers, function(eventName, handlers) {
			$.each(handlers, function(idx, handler) {
				element.AttachHandler(eventName, handler);
			});
		});
		
		if(this.hidden) {
			this.Hide();
		}
	},
	
	Hide:function()
	{
		this.hidden = true;
		
		if(this.rendered) {
			$('#'+this.id+'-container').hide();
		}
		
		return this;
	},
	
	Show:function()
	{
		this.hidden = false;
		
		if(this.rendered) {
			$('#'+this.id+'-container').show();
		}
		
		return this;
	},
	
   /**
    * @protected
    */
	_PostRender:function()
	{
		
	},
	
   /**
    * Retrieves all attributes, and compiles dynamic attributes
    * like class and style. 
    * 
    * @returns {Object}
    */
	GetAttributes:function()
	{
		var atts = this.attributes;
		atts['id'] = this.id;
		atts['name'] = this.name;
		
		if(this.classes.length > 0) {
			atts['class'] = this.classes.join(' ');
		}
		
		var style = UI.CompileStyles(this.styles);
		if(style.length > 0) {
			atts['style'] = style;
		}
		
		return atts;
	},
	
   /**
    * Fetches the value from the live element value. All
    * available filters are also applied to the value at
    * this point.
    *  
    * @protected
    * @returns {String}
    */
	UpdateValue:function()
	{
		var value = this._GetLiveValue();
		
		$.each(this.filters, function(idx, filter) {
			value = filter.call(undefined, value);
		});
		
		return value;
	},
	
   /**
    * Retrieves the current value of the element from the related dom element.
    * This is element-specific and must be implemented by each element type.
    * 
    * @protected
    * @abstract
    */
	_GetLiveValue:function()
	{
		throw new ApplicationException(
			'Method not implemented',
			'The GetLiveValue method must be implemented by the ['+this.GetElementType()+'] element type.',
			this.ERROR_LIVE_VALUE_METHOD_NOT_IMPLEMENTED
		);
	},

   /**
    * Sets the current value of the element into the related dom element.
    * This is element-specific and must be implemented by each element type.
    * 
    * @protected
    * @abstract
    */
	_SetLiveValue:function(value)
	{
		throw new ApplicationException(
			'Method not implemented',
			'The SetLiveValue method must be implemented by the ['+this.GetElementType()+'] element type.',
			this.ERROR_LIVE_SET_VALUE_METHOD_NOT_IMPLEMENTED
		);
	},

	Validate:function()
	{
		this.ResetError();
		
		var value = this.GetValue();
		
		// empty values: only invalid if required
		if(isEmpty(value)) {
			if(this.required) {
				this.MakeError(t('This field is required.'));
				return false;
			}
			
			return true;
		}
		
		// run all validation rules on the value
		for(var i=0; i<this.rules.length; i++) {
			var rule = this.rules[i];
			if(rule.callback.call(undefined, value) !== true) {
				this.MakeError(rule.message);
				return false;
			}
		}
		
		var errorMessage = this._Validate();
		if(errorMessage===true) {
			return true;
		}
		
		this.MakeError(errorMessage);
		return false;
	},
	
   /**
    * Element-specific validation routine. Must check the value,
    * and either return a boolean true if valid, or an error message
    * text if invalid.
    * 
    * @protected
    * @return {String|Boolean}
    */
	_Validate:function()
	{
		return true;
	},
	
   /**
    * Marks the element as erroneous. This is done automatically
    * during the validation routine.
    * 
    * @protected
    * @param {String} errorText
    */
	MakeError:function(errorText)
	{
		FormHelper.makeError(this.id, errorText);
	},
	
   /**
    * Resets the element's error status.
    * @protected
    */
	ResetError:function()
	{
		FormHelper.resetErrorStatus(this.id);
	},
	
   /**
    * Adds a filter function that is applied to the value
    * before it is validated.
    * 
    * @param {Function} filterFunction
    * @returns {FormHelper_Form_Element}
    */
	AddFilter:function(filterFunction)
	{
		this.filters.push(filterFunction);
		return this;
	},
	
   /**
    * Adds a trim filter to the element, to trim any 
    * whitespace from the beginning or end of the string.
    * 
    * @returns {FormHelper_Form_Element}
    */
	AddFilterTrim:function()
	{
		return this.AddFilter(function(value) { return trim(value); });
	},
	
   /**
    * Adds a validation rule function. This function gets the 
    * value to be validated, and must return a boolean true or
    * false. The error message is used if the value is not valid.
    * 
    * @param {Function} validationFunction
    * @param {String} errorMessage
    * @returns {FormHelper_Form_Element}
    */
	AddRule:function(validationFunction, errorMessage)
	{
		this.rules.push({
			'callback':validationFunction,
			'message':errorMessage
		});
		
		return this;
	},
	
   /**
    * Adds a validation rule for regular element labels.
    * @param {String} [errorMessage] Optional custom error message
    * @returns {FormHelper_Form_Element}
    */
	AddRuleLabel:function(errorMessage)
	{
		if(isEmpty(errorMessage)) {
			errorMessage = t('Must be a valid label.');
		}
		
		this.RegisterRuleType('label');
		
		return this.AddRule(
			function(value) {
				return FormHelper.validate_label(value);
			},
			errorMessage
		);
	},
	
	AddRuleNoHTML:function(errorMessage)
	{
		if(isEmpty(errorMessage)) {
			errorMessage = t('Must not contain HTML.');
		}
		
		this.RegisterRuleType('nohtml');
		
		return this.AddRule(
			function(value) {
				return FormHelper.validate_nohtml(value);
			},
			errorMessage
		);
	},
	
   /**
    * Adds a validation rule for element aliases.
    * @param {String} [errorMessage] Optional custom error message
    * @returns {FormHelper_Form_Element}
    */
	AddRuleAlias:function(errorMessage)
	{
		if(isEmpty(errorMessage)) {
			errorMessage = t('Must be a valid alias.');
		}
		
		this.RegisterRuleType('alias');
		
		return this.AddRule(
			function(value) {
				return FormHelper.validate_alias(value);
			},
			errorMessage
		);
	},
	
	AddRuleInteger:function(min, max, errorMessage)
	{
		if(isEmpty(errorMessage)) {
			errorMessage = t('Must be a valid whole number.');
		}
		
		if(isEmpty(min)) {
			min = 0;
		}
		
		if(isEmpty(max)) {
			max = 0;
		}
		
		this.SetTag('integer-size', {
			'min':min,
			'max':max
		});
		
		this.RegisterRuleType('integer');
		
		return this.AddRule(
			function(value) {
				var val = value*1;
				if(max > 0 && val > max) {
					return false;
				}
				
				if(val < min) {
					return false;
				}
				
				return true;
			},
			errorMessage
		);
	},
	
   /**
    * Sets a "tag", which can be used to attach arbitrary
    * data to the element, solely for storing and retrieving.
    * It has no other functionality. 
    * 
    * @param {String} name
    * @param {Mixed} value
    * @return {FormHelper_Form_Element}
    */
	SetTag:function(name, value)
	{
		this.tags[name] = value;
		return this;
	},
	
   /**
    * Retrieves a tag previously set with {@link SetTag()}.
    * 
    * @param {String} name
    * @param {Mixed} defaultValue The value to return if empty. Defaults to NULL.
    * @returns {Mixed}
    */
	GetTag:function(name, defaultValue)
	{
		if(typeof(this.tags[name]) != 'undefined') {
			return this.tags[name];
		}
		
		if(isEmpty(defaultValue)) {
			return null;
		}
		
		return defaultValue;
	},
	
   /**
    * Registers a rule type when it is added, like the alias
    * rule. This is used to automatically add type hints to
    * the element's help text when it is rendered.
    * 
    * @protected
    * @param {String} typeName
    * @return {FormHelper_Form_Element}
    */
	RegisterRuleType:function(typeName)
	{
		if(!in_array(typeName, this.ruleTypes)) {
			this.ruleTypes.push(typeName);
		}
		
		return this;
	},
	
   /**
    * Adds a regular expression rule.
    * 
    * @param {String|RegExp} The regular expression, typically without starting/ending characters
    * @param {String} The error message in case the expression does not match
    * @return {FormHelper_Form_Element}
    */
	AddRuleRegex:function(regularExpression, errorMessage)
	{
		var regexObj = regularExpression;
		if(!(regexObj instanceof RegExp)) {
			regexObj = new RegExp(regularExpression);
		}
		
		return this.AddRule(
			function(value) {
				var reg = new RegExp(regularExpression);
				return reg.test(value);
			},
			errorMessage
		);
	},
	
	AddRegexRule:function(regexObj, errorMessage, trueIsFalse)
	{
		if(isEmpty(trueIsFalse)) {
			trueIsFalse = false;
		}
		
		return this.AddRule(
			function(value) {
				var result = regexObj.test(value);
				if(trueIsFalse) {
					return !result;
				}
				
				return result;
			},
			errorMessage
		);
	},
	
   /**
    * Resets all elements in the form to their default values.
    * @returns {FormHelper_Form_Element}
    */
	Reset:function()
	{
		this.ResetError();
		this._Reset();
		return this;
	},
	
   /**
    * @protected
    */
	_Reset:function()
	{
		this.SetValue(this.GetDefaultValue());
	},
	
   /**
    * Sets the help text to show below the element. Overwrites any
    * existing help text.
    * 
    * @param {String} text
    * @returns {FormHelper_Form_Element}
    */
	SetHelpText:function(text)
	{
		this.helpText = text;
		
		if(this.rendered) {
			$('#'+this.id+'_help').show().html(this.RenderHelpText());
		}
		
		return this;
	},
	
   /**
    * Renders the element's help text: this is the text
    * specified via {@SetHelpText()} and any automatic
    * help texts that the element type may add.
    * 
    * @returns {String}
    */
	RenderHelpText:function()
	{
		var text = '';
		
		if(!isEmpty(this.helpText)) {
			text = this.helpText;
		}
		
		this.autoHelp = []; // reset this each time
		this.Handle_AutoHelpTexts();
		
		if(this.autoHelp.length > 0) {
			text += ' ' + this.autoHelp.join(' ');
		}
		
		return text;
	},

   /**
    * Called by elements to add automatic help texts.
    * @protected
    * @param {String} text
    * @returns {FormHelper_Form_Element}
    */
	RegisterAutoHelpText:function(text)
	{
		this.autoHelp.push(text);
		return this;
	},
	
	Handle_AutoHelpTexts:function()
	{
		this._Handle_AutoHelpTexts();
		
		for(var i=0; i < this.ruleTypes.length; i++) {
			switch(this.ruleTypes[i]) 
			{
				case 'nohtml':
					this.RegisterAutoHelpText(FormHelper.getValidationHint_nohtml());
					break;
					
				case 'alias':
					this.RegisterAutoHelpText(FormHelper.getValidationHint_alias());
					break;
					
				case 'label':
					this.RegisterAutoHelpText(FormHelper.getValidationHint_label());
					break;
					
				case 'integer':
					var text = '';
					var size = this.GetTag('integer-size');
					
					if(size.max > 0) {
						text = t('Must be a number between %1$s and %2$s.', size.min, size.max);
					} else {
						text = t('Must be a number equal to or higher than %1$s.', size.min);
					}
					
					this.RegisterAutoHelpText(text);
					break;
			}
		}
	},
	
   /**
    * @protected
    * @abstract
    */
	_Handle_AutoHelpTexts:function()
	{
		
	},
	
	GetAttribute:function(name, defaultValue)
	{
		if(typeof(this.attributes[name]) != 'undefined') {
			return this.attributes[name];
		}

		if(!isEmpty(defaultValue)) {
			return defaultValue;
		}
		
		return null;
	},
	
   /**
    * Sets the element's size to extra small (XS).
    * @returns {FormHelper_Form_Element}
    */
	MakeWidthXS:function() { return this.MakeWidth('xs'); },

   /**
    * Sets the element's size to small (S).
    * @returns {FormHelper_Form_Element}
    */
	MakeWidthS:function() { return this.MakeWidth('s'); },

   /**
    * Sets the element's size to medium (M).
    * @returns {FormHelper_Form_Element}
    */
	MakeWidthM:function() { return this.MakeWidth('m'); },
	
   /**
    * Sets the element's size to large (L).
    * @returns {FormHelper_Form_Element}
    */
	MakeWidthL:function() { return this.MakeWidth('l'); },

   /**
    * Sets the element's size to extra large (XL).
    * @returns {FormHelper_Form_Element}
    */
	MakeWidthXL:function() { return this.MakeWidth('xl'); },

   /**
    * Sets the element's size to extra small (XXL).
    * @returns {FormHelper_Form_Element}
    */
	MakeWidthXXL:function() { return this.MakeWidth('xxl'); },
	
	MakeWidth:function(size)
	{
		this.hsize = size;
		return this;
	},
	
	GetDefaultValue:function()
	{
		return '';
	},
	
	IsHeader:function()
	{
		return false;
	},
	
	
   /**
    * @protected
    */
	RegisterEventHandler:function(eventName, handler)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			this.eventHandlers[eventName] = [];
		}
		
		this.eventHandlers[eventName].push(handler);
		return this;
	},
	
   /**
    * @protected
    */
	Handle_Change:function()
	{
		this.TriggerEvent('change');
	},
	
   /**
    * @protected
    */
	TriggerEvent:function(eventName)
	{
		if(!this.triggerEvents) {
			return;
		}
		
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			return;
		}
		
		var element = this;
		$.each(this.eventHandlers[eventName], function(idx, handler) {
			handler.call(element);
		});
	},
	
	toString:function()
	{
		return this.Render();
	},
	
   /**
    * @protected
    * @param {String} message
    * @param {String} category
    */
	log:function(message, category)
	{
		this.form.log('Element ['+this.name+' #'+this.id+'] | ' + message, category);
	},
	
   /**
    * Adds an onchange handler function to the element.
    * @param handler
    * @returns {FormHelper_Form_ElementClass}
    */
	Change:function(handler)
	{
		return this.On('change', handler);
	},
	
   /**
    * Adds an event handling function for the specified event.
    * The handler gets the element's instance as the first parameter
    * of the event.
    * 
    * @param {String} eventName E.g. "change"
    * @param {Function} handler
    * @return {FormHelper_Form_ElementClass}
    */
	On:function(eventName, handler)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			throw new ApplicationException(
				'Unknown event',
				'The event ['+eventName+'] is not supported by the element ['+this.name+' #'+this.id+'].',
				this.ERROR_UNKNOWN_EVENT
			);
		}
		
		if(isEmpty(handler)) {
			if(this.IsReady()) {
				this.TriggerEvent(eventName);
			}
			return this;
		}
		
		var element = this;
		
		// create the handler that will call the specified
		// event handler and provide the needed parameters.
		var internalHandler = function() {
			element.log('Event ['+eventName+'] | The event has been triggered.');
			handler.call(element, element);
		};
		
		this.eventHandlers[eventName].push(internalHandler);
		
		// attach the handler if the element has already been rendered.
		if(this.rendered) {
			this.AttachHandler(eventName, internalHandler);
		}
		
		return this;
	},
	
   /**
    * @protected
    */
	AttachHandler:function(eventName, handler)
	{
		this.log('Event ['+eventName+'] | Attaching the event handler.');
		
		this._AttachHandler(eventName, handler);
	},
	
   /**
    * Attaches an event handler function to the element.
    * The default behavior is to attach the event handler
    * to the main element, but this can be overridden by
    * extending this method and implementing it differently.
    * 
    * @param {String} eventName
    * @param {Function} handler
    */
	_AttachHandler:function(eventName, handler)
	{
		this.GetElementInstance().on(eventName, handler);
	},
	
   /**
    * Checks whether the element's UI elements have all been rendered. 
    * @returns {Boolean}
    */
	IsReady:function()
	{
		return this.rendered;
	},
	
   /**
    * Appends the specified HTML to the element.
    * 
    * NOTE: this is not supported by all elements.
    * Hidden values for example simply ignore this.
    * 
    * @param {String} content
    * @return {FormHelper_Form_Element}
    */
	Append:function(content)
	{
		this.append = content;
		return this;
	},
	
   /**
    * Appends a button to the element. 
    * 
    * @param {UI_Button} button
    * @returns {FormHelper_Form_Element}
    */
	AppendButton:function(button)
	{
		button.AddClass('after-input');
		return this.Append(button);
	},
	
   /**
    * Appends a button the the element that can be
    * used to transliterate a label into an alias.
    * Uses the value of the source element to generate
    * the alias.
    * 
    * @param {FormHelper_Form_Element}
    */
	AppendGenerateAliasButton:function(sourceElement, label)
	{
		if(isEmpty(label)) {
			label = t('Generate from label');
		}
		
		// ensure the button fits
		this.MakeWidthL();
		
		var aliasElement = this;
		
		return this.AppendButton(
			UI.Button(label)
			.SetIcon(UI.Icon().Generate())
			.Click(function() {
				FormHelper.generateAlias(
					this, 
					aliasElement.GetID(), 
					sourceElement.GetID()
				);
			})
		);
	}
};

FormHelper_Form_Element = Class.extend(FormHelper_Form_Element);
