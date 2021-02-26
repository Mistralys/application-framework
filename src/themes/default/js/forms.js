/**
 * Form management utility class used to handle and create
 * clientside forms.
 * 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class 
 * @static
 */
var FormHelper =
{
	'ERROR_FORM_NOT_FOUND':340001,
	'ERROR_COULD_NOT_FIND_ELEMENT':340002,
		
	'ID_PREFIX':null, // set serverside
	'initDone':false,

	init:function()
	{
		// avoid doing this multiple times in case different scripts
		// enqueue this command.
		if(this.initDone) {
			return;
		}

		$('[structural="yes"]').change(function(event) {
			var el = $(event.currentTarget);
			var form = el.closest('form');
			var formName = form.attr('id');
			FormHelper.setStructuralElementChanged(el.attr('id'), formName);
		});

		this.initDone = true;
	},

   /**
    * Renders a single row in a form. It is made so it can be used
    * with other methods like {@link makeError} for example.
    *
    * @param {String} label
    * @param {String} elementID
    * @param {String} field
    * @param {Boolean} [required=false]
    * @param {String} [help]
    * @param {Array} [groupClasses]
    * @param {Boolean} [structural=false]
    * @returns {String}
    */
	renderItem:function(label, elementID, field, required, help, groupClasses, structural)
	{
		if(isEmpty(groupClasses)) {
			groupClasses = [];
		}
		
		if(structural != true) {
			structural = false;
		}
		
		var html =
		'<div class="control-group form-container '+groupClasses.join(' ')+'" id="'+elementID+'_form_container">';
			if(label != null) {
				html += ''+
				'<label class="control-label" for="'+elementID+'">'+
					label+
					this.renderRequired(elementID, required)+
					this.renderStructural(elementID, structural)+
				'</label>';
			}
			html += ''+
			'<div class="controls">'+
				field+
				'<span id="'+elementID+'_form_error"></span>'+
				 this.renderHelp(help, elementID)+
			 '</div>'+
		'</div>';

		return html;
	},

   /**
    * Resets the error status of any number of form elements,
    * simply specify the element IDs as parameters. 
    *
    * @param {String} [elementID]*
    */
   resetErrorStati:function()
	{
		for(var i=0; i<arguments.length; i++) {
			this.resetErrorStatus(arguments[i]);
		}
	},

   /**
    * Resets the error status of a single form element after 
    * having been marked as erroneous by the {@link makeError}
    * method. Has no effect if it was not erroneous.
    *

    * @param {String} elementID
    */
	resetErrorStatus:function(elementID)
	{
        var elementID = elementID.replace(/\[/g, '_').replace(/\]/g, '');
		$('#'+elementID+'_form_container').removeClass('error');
		$('#'+elementID+'_form_error').html('');
	},

   /**
    * Marks the specified form element as erroneous, with an
    * error hint message.
    *
    * @param {String} elementID
    * @param {String} errorText
    */
	makeError:function(elementID, errorText)
	{
		var container = $('#'+elementID).parents('.form-container');

		if(container.length == 0)
		{
			throw new ApplicationException(
				'Could not find parent container element for element ['+elementID+'].'
			);
		}

		container.addClass('error');
		container.find('.form-error-message').html('<span class="help-inline">'+errorText+'</span>');
		
		var sectionEl = container.parents('section').first();
		
		if(sectionEl.length == 0)
		{
			return;
		}
		
		var section = UI.GetSection(sectionEl.attr('id'));
		
		// Automatically expand the section in which the erroneous
		// element is located, so it does not go unnoticed. This is
		// the same behavior as serverside.
		if(section != null)
		{
			section.Expand();
		}
	},
	
	setRequired:function(elementID, required)
	{
		var display = 'none';
		if(required == true) {
			display = 'inline-block';
		}
		
		$('#'+elementID+'_form_container .icon-required').css('display', display);
	},

   /**
    * Renders the markup for a help text of a form element.
    * @protected
    * @param {String} helpText
    * @param {String} parentID
    * @returns {String}
    */
	renderHelp:function(helpText, parentID)
	{
		var display = 'none';
		if(!isEmpty(helpText)) {
			display = 'block';
		}
		
		return '<span class="help-block" id="'+parentID+'_help" style="display:'+display+';">'+helpText+'</span>';
	},
	
	renderHeading:function(label)
	{
		return this.renderHeader(label, 3);
	},
	
	renderSubheading:function(label)
	{
		return this.renderHeader(label, 4);
	},
	
	renderHeader:function(title, level)
	{
		var anchorName = nextJSID();
		
		var html = ''+ 
		'<a id="heading'+anchorName+'"></a>'+
        '<h'+level+'>'+
        	title+
        '</h'+level+'>';
		
		this.registerHeading(anchorName, level, label);
		
		return html;
	},

   /**
    * Renders the required icon if the required flag is true.
    * @protected
    * @param {Boolean} required
    * @returns {String}
    */
	renderRequired:function(elementID, required)
	{
		if(isEmpty(required)) {
			required = false;
		}

		var active = '';
		if(required) {
			active = 'active';
		}
		
		var html = '' +
		'<span id="'+elementID+'-required" class="form-icon-required '+active+'">' +
			UI.Icon().Required()
			.MakeDangerous()
			.CursorHelp()
			.SetTooltip(t('This field is required.')) +
		'</span>';

		return html;
	},

   /**
    * Renders the structural icon if the flag is true.
    * @protected
    * @param {Boolean} required
    * @returns {String}
    */
	renderStructural:function(elementID, structural)
	{
		if(isEmpty(structural)) {
			structural = false;
		}

		var active = '';
		if(structural) {
			active = 'active';
		}
		
		var html = '' +
		'<span id="'+elementID+'-structural" class="form-icon-structural '+active+'">' +
			UI.Icon().Structural()
			.MakeMuted()
			.CursorHelp()
			.SetTooltip(t('This field is structural, and will affect the record\'s state if modified.')) +
		'</span>';

		return html;
	},
	
   /**
    * To be able to submit a form with the enter key, the form needs to
    * have a submit element. When there is none, this can be used to add
    * an invisible one to achieve the same effect. Only one is needed per
    * form.
    *
    * @returns {String}
    */
	renderDummySubmit:function()
	{
		var html =
		'<div style="width:1px;height:1px;overflow:hidden;">'+
			'<input type="submit" name="dummySubmit" value="true" tabindex="-1"/>'+
		'</div>';

		return html;
	},

   /**
    * Resets the selection of a select field by removing the selected property
    * on all option elements.
    *
    * @param {String} nameIDOrObject The element instance or ID
    * @returns {FormHelper}
    */
	resetSelectField:function(nameIDOrObject)
	{
		var el = this.getElement(nameIDOrObject);
		if(!el) {
			return this;
		}
		
		var elementID = el.attr('id');
		
		// remove "selected" from any options that might already be selected
		$('#'+elementID+' option[selected="selected"]').each(
			function() {
				$(this).removeAttr('selected');
			}
		);

		// mark the first option as selected
		$('#'+elementID+' option:first').attr('selected','selected');

		// display glitch fix in Chrome
		if(el.hasClass('select-multiselect')) {
			el.multiselect('refresh');
		} else {
			el.hide().show();
		}
		
		// register the change, as the event may not fire 
		el.change();
		
		return this;
	},

   /**
    * Resets the specified form fields. Accepts an arbitrary
    * number of element IDs or element instances.
    * 
    * @returns {FormHelper}
    */
	resetFields:function()
	{
		for (i = 0; i < arguments.length; i++) {
	        this.resetField(arguments[i]);
	    }
		
		return this;
	},
	
   /**
    * Resets a form field according to its element type. Unchecks
    * checkboxes / radios, deselects all entries from selects, empties
    * text fields, etc.
    * 
    * @param {String|Object} nameIDOrObject The element instance or ID 
    * @returns {FormHelper}
    * @throws {ApplicationException}
    */
	resetField:function(nameIDOrObject)
	{
		var el = this.requireElement(nameIDOrObject);
		
		if(this.isElementSelect(el)) {
			return this.resetSelectField(nameIDOrObject);
		}
		
		if(this.isElementCheckable(el)) {
			el.prop('checked', false);
			el.prop('selected', false);
			return this;
		}
		
		el.val('');
		return this;
	},
	
	'registries':{},
	
   /**
    * Retrieves a form registry for the specified form. This is used
    * to access information about elements and sections in forms. Note
    * that it is only populated if the registry has been explicitly
    * enabled serverside.
    * 
    * @param {String} formName
    * @returns {FormHelper_Registry}
    * @formtype Serverside
    */
	getRegistry:function(formName)
	{
		if(typeof(this.registries[formName]) == 'undefined') {
			this.registries[formName] = new FormHelper_Registry();
		}
		
		return this.registries[formName];
	},
	
	isElementImageUpload:function(nameIDOrObject)
	{
		var el = this.getElement(nameIDOrObject);
		if(el && el.hasClass('imageuploader_name')) {
			return true;
			}
		
		return false;
	},
	
   /**
    * Checks whether the specified element is a checkbox or radio element.
    * 
    * @param {String|Object} nameIDOrObject The element instance or ID
    * @returns {Boolean}
    */
	isElementCheckable:function(nameIDOrObject)
	{
		var el = this.getElement(nameIDOrObject);
		if(el && (el.attr('type') == 'checkbox' || el.attr('type') == 'radio')) {
			return true;
		}
		
		return false;
	},

   /**
    * Renders a select element with the specified items.
    * The items list can be either an object with key => 
    * value pairs, or an indexed array with item objects.
    * This is the preferred way to handle numeric keys, as
    * javascript is known to jumble the order of the keys.
    * 
    * Example with associate options
    *  
    * <pre>
    * renderSelect(
    *     'element_id',
    *     {
    *         'item_key_one':'Item One Label',
    *         'item_key_two':'Item Two Label'
    *     },
    *     'item_key_one'
    * );
    * </pre>
    * 
    * Example with indexed options
    * 
    * <pre>
    * renderSelect(
    *     'element_id',
    *     [
    *         {
    *             'value':'item_key_one',
    *             'text':'Item One Label'
    *         },
    *         {
    *             'value':'item_key_two',
    *             'text':'Item Two Label'
    *         }
    *     ],
    *     'item_key_one'
    * );
    * </pre>
    * 
    * Example with grouped options (only possible with
    * indexed options)
    * 
    * <pre>
    * renderSelect(
    *     'element_id',
    *     [
    *         {
    *             'group':'Label for the group',
    *             'options':
    *             [
    *                 {
    *                     'value':'item_key_one',
    *                     'text':'Item One Label'
    *                 },
    *                 {
    *                     'value':'item_key_two',
    *                     'text':'Item Two Label'
    *                 }
    *             ]
    *         },
    *         ...
    *     ],
    *     'item_key_one'
    * );
    * </pre>
    *
    * @param {String} id
    * @param {String} items
    * @param {String} [activeItems] The value to activate as selected element, or in case of a multiple-enabled select, an indexed array with select values.
    * @param {Object} [options]
    * @param {Boolean} [options.please_select=false]
    * @param {String} [options.please_select_label]
    * @param {Object} [options.attributes={}]
    * @returns {String}
    */
	renderSelect:function(id, items, activeItems, options)
	{
		if(typeof(activeItems)=='undefined') {
			activeItems = null;
		}
		
		if(typeof(options)=='undefined') {
			options = {};
		}

		if(typeof(options.please_select)=='undefined') {
			options.please_select = false;
		}

		if(typeof(options.please_select_label)=='undefined') {
			options.please_select_label = t('Please select...');
		}

		if(typeof(options.attributes)=='undefined') {
			options.attributes = {};
		}

		options.attributes.id = id;
		
		if(typeof(options.attributes['class'])=='undefined') {
			options.attributes['class'] = '';
		}
		
		if(typeof(options.attributes['multiple']) != 'undefined') {
			options.attributes.multiple = 'multiple';
		}
		
		options.attributes['class'] += ' mousetrap'; // make sure hotkeys work even when focus is on the field

		var html =
		'<select'+this.compileAttributes(options.attributes)+'>';
			if(options.please_select) {
				html +=
				'<option value="">'+options.please_select_label+'</option>';
			}
			html += this.renderSelectItems(items, activeItems)+
		'</select>';

		return html;
	},
	
   /**
    * Renders all option items in a select element. 
    * @protected
    * @param {Object} items
    * @param {Object} active
    * @returns {String}
    */
	renderSelectItems:function(items, active)
	{
		var html = '';
		
		$.each(items, function(key, label) {
			html += FormHelper.renderSelectItem(key, label, active);
		});
		
		return html;
	},
	
   /**
    * Renders a single item in a select element.
    * @protected
    * @param {String} key
    * @param {String} label
    * @param {Object} activeItems
    * @returns {String}
    */
	renderSelectItem:function(key, label, activeItems)
	{
		if(typeof(label)=='object' && typeof(label.group) != 'undefined') {
			return '' +
			'<optgroup label="'+label.group+'">'+
				this.renderSelectItems(label.options, activeItems)+
			'</optgroup>';
		}
		
		if(typeof(label)=='object') {
			key = label.value;
			label = label.text;
		}
		
		var atts = {
			'value':key
		};
		
		// convert the active value to an array if it isn't already
		if(activeItems == null || typeof(activeItems)!='object') {
			var item = activeItems;
			activeItems = [];
			if(item != null) {
				activeItems.push(item);
		}
		} 
		
		// go through the selected values: this is an array
		// to support multiselect elements
		$.each(activeItems, function(idx, item) {
			if(key==item) {
				atts['selected'] = 'selected';
			}
		});
		
		return '<option'+UI.CompileAttributes(atts)+'>'+label+'</option>';
	},

	compileAttributes:function(attributes)
	{
		var atts = '';
		$.each(attributes, function(name, value) {
			atts += ' '+name+'="'+value+'"';
		});

		return atts;
	},

   /**
    * Renders the markup for a checkbox form row.
    *
    * @param {String} label
    * @param {String} name
    * @param {Boolean} [checked=false]
    * @param {String} [helpText]
    * @param {String} [idPrepend='f-']
    * @param {Boolean} [disabled=false]
    * @returns {String}
    */
	renderCheckbox:function(label, name, checked, helpText, idPrepend, disabled)
	{
		return new FormHelper_CheckboxLine(label, name, checked, helpText, idPrepend, disabled).Render();
	},

   /**
    * Creates a checkbox instance.
    * 
    * @param {String} label
    * @param {String} name
    * @param {Boolean} [checked=false]
    * @param {String} [helpText]
    * @param {String} [idPrepend='f-']
    * @param {Boolean} [disabled=false]
    * @returns {FormHelper_CheckboxLine}
    */
	createCheckbox:function(label, name, checked, helpText, idPrepend, disabled)
	{
		return new FormHelper_CheckboxLine(label, name, checked, helpText, idPrepend, disabled);
	},
	
	renderStatic:function(label, content, elementID, required, help)
	{
		if(typeof(elementID)=='undefined') {
			elementID = nextJSID();
		}		
		
		if(typeof(required)=='undefined') {
			required = false;
		}
		
		var html =
		'<div class="control-group form-container" id="'+elementID+'_form_container">'+
			'<label class="control-label" for="'+elementID+'">'+
				label+
				this.renderRequired(required)+
			'</label>'+
			'<div class="controls">'+
				content+
				'<span class="help-inline" id="'+elementID+'_form_error"></span>'+
				 this.renderHelp(help, elementID)+
			'</div>'+
		'</div>';

		return html;
	},

   /**
    * Sets the value of a checkbox.
    * 
    * @param {String} name The checkbox name/ID as specified when created
    * @param {Boolean|String} state Boolean or string boolean representation to set whether it is checked (true) or unchecked (false).
    */
	setCheckbox:function(name, state)
	{
		var el = this.requireElement(name);
		var state = string2bool(state);
		
		if(el.attr('type')=='hidden') {
			el.val(bool2string(state, true));
		} else {
			el.prop('checked', state);
		}
	},

   /**
    * Checks whether the specified element is checked (for radio buttons
    * and checkboxes). 
    * 
    * @param {Object|String} name Either an existing DOM form element or the element ID as set with the renderCheckbox/renderRadio methods.
    * @returns {Boolean}
    * @throws {ApplicationException}
    */
	isChecked:function(name)
	{
		var el = this.requireElement(name);
		
		// the checkbox/radio may be frozen, in which case the value is 
		// stored in a hidden field. We check both types just in case.
        if (el.prop('checked') || (el.attr('type')=='hidden' && el.val() == 'yes')) {
        	return true;
        } 
        
        return false;
	},

   /**
    * Retrieves a form element by its name or ID.
    * 
    * @param {String|DOMElement} nameIDOrObject The field name, ID or an existing element instance
    * @returns {DOMElement|NULL}
    */
	getElement:function(nameIDOrObject)
	{
		if(typeof(nameIDOrObject) == 'object') {
			return $(nameIDOrObject);
		}
		
		// a specific ID has been specified
		if(nameIDOrObject.substring(0, 1) == '#') {
			el = $(nameIDOrObject);
			if(el.length > 0) {
				return el;
			}
			
			return null;
		}
		
		// try finding the element with the usual naming convention
		el = $('#' + this.ID_PREFIX + nameIDOrObject);
		if(el.length > 0) {
			return el;
		}
		
		// it may be an image uploader element, try that
		el = $('#'+nameIDOrObject+'_name');
		if(el.length > 0) {
			return el;
		}
		
		// it may be a custom ID, so we try that
		el = $('#'+nameIDOrObject);
		if(el.length > 0) {
			return el;
		}
		
		return null;
	},
	
   /**
    * Retrieves a form element by its name or ID, and throws
    * an exception if it does not exist.
    * 
    * @param {String|DOMElement} nameIDOrObject The field name, ID or an existing element instance
    * @returns {DOMElement}
    * @throws {ApplicationException}
    */
	requireElement:function(name)
	{
		var el = this.getElement(name);
		if(el) {
			return el;
		}
		
		throw new ApplicationException(
			'Cannot find form element',
			'Tried finding form element [' + name + '], but if was not present.',
			this.ERROR_COULD_NOT_FIND_ELEMENT
		);
	},

    checkAllItems:function(fieldName)
	{
		$('INPUT[name^="'+fieldName+'"]').prop('checked', true);
	},

	uncheckAllItems:function(fieldName)
	{
		$('INPUT[name^="'+fieldName+'"]').prop('checked', false);
	},

	renderRadio:function(label, name, value, checked, helpText, idPrepend)
	{
		var checkedAtts = '';
		if(typeof(checked)!='undefined' && checked) {
			checkedAtts = ' checked="checked"';
		}
		
		if(typeof(idPrepend)=='undefined') {
			idPrepend = FormHelper.ID_PREFIX;
		}

		var id = idPrepend+name+'_'+value;

		var html = ''+
		'<div class="control-group form-container checkbox-line">'+
			'<div class="controls">'+
				'<label class="radio">'+
					'<input type="radio" name="'+name+'" value="'+value+'"'+checkedAtts+' id="'+id+'" class="mousetrap"> '+label+
				'</label>'+
				this.renderHelp(helpText, id)+
			'</div>'+
		'</div>';

		return html;
	},

	getElementType:function(nameIDOrObject)
	{
		var el = this.getElement(nameIDOrObject);
		
		if(this.isElementSwitch(el)) {
			return 'YesNo';
		} else if(this.isElementImageUpload(el)) {
			return 'ImageUpload';
		}
		
		return 'Regular';
	},
	
   /**
    * Sets the value of an element, automatically determining the
    * type of element and setting the value accordingly.
    * 
    * @param {String|Object} nameIDOrObject
    * @param {String|Object|Number} value 
    * @param {String} [fieldType] The type of field to expect. This is required to be set to <code>YesNo</code> for switch elements.
    * @returns {FormHelper}
    */
	setElementValue:function(nameIDOrObject, value)
	{
		var el = this.getElement(nameIDOrObject);
		
		if(el==null) {
			this.log('Cannot find element ['+nameIDOrObject+'].', 'error');
			return this;
		}
		
		if(typeof(value)=='undefined') {
			this.log('Tried setting value of element ['+el.attr('id')+'] to undefined.', 'error');
			return this;
		}
		
		fieldType = this.getElementType(el);
		
		switch(fieldType) {
			case 'YesNo':
				if(value=='true') {
					switchElement.turnOn(el.attr('id'));
				} else {
					switchElement.turnOff(el.attr('id'));
				}
				break;

			default:
				this.resetField(el);
			
				// select elements that allow multiple selection
				if(this.isElementSelect(el) && el.prop('multiple')) {
					// empty value: nothing to do, the field has already been reset.
					if(isEmpty(value)) {
						this.log('Value to set for multiselect is empty.', 'ui');
						return this;
					}
					
					if(typeof(value)!='object') {
						this.log('Setting value of multiple selects requires an array.', 'error');
						return this;
					}
					
					$.each(value, function(idx, option) {
						el.find('option[value="'+option+'"]').prop('selected', true);
					});
					
					if(el.hasClass('select-multiselect')) {
						this.log('Refreshing multiselect.', 'ui');
						el.multiselect('refresh');
					}
					return this;
				}
			
				// checkboxes and radios
				if(this.isElementCheckable(el)) {
					el.prop('checked', string2bool(value));
					return this;
				}
			
				// all other fields: textarea, text, date, select with a single value...
				el.val(value);
				break;
		}
		
		return this;
	},
	
	getElementValue:function(nameIDOrObject)
	{
		var el = this.getElement(nameIDOrObject);
		if(!el) {
			return null;
		}
		
		if(this.isElementSwitch(el)) {
			return switchElement.getValue(el.attr('id'));
		}
		
		return el.val();
	},
	
	isElementSwitch:function(nameIDOrObject)
	{
		var el = this.getElement(nameIDOrObject);
		return el.hasClass('bootstrap-switch');
	},
	
	renderFrozen:function(displayValue, elementID, hiddenValue)
	{
		if(typeof(hiddenValue)=='undefined') {
			hiddenValue = displayValue;
		}

		if(displayValue.length < 1) {
			displayValue = '<span class="muted">('+t('Empty value')+')</span>';
		}

		var html =
		'<span class="control-value-frozen">'+displayValue+'</span>'+
		'<input type="hidden" id="'+elementID+'" value="'+hiddenValue+'"/>';

		return html;
	},

	renderFrozenSelect:function(options, value, elementID, hiddenValue)
	{
		if(isEmpty(hiddenValue)) {
			hiddenValue = value;
		}
		
		if(typeof(value) != 'object') {
			value = [value];
		}
		
		var items = [];
		$.each(options, function(idx, option){
			if(in_array(option.value, value)) {
				items.push(option.text);
			}
		});
		
		var displayValue;
		
		if(items.length > 0) {
			displayValue = items.join('<br/>');
		} else {
			displayValue = '<span class="muted">('+t('No items selected.')+')</span>';
		}
		
		var html =
		'<span class="control-value-frozen">'+displayValue+'</span>'+
		'<input type="hidden" id="'+elementID+'" value="'+hiddenValue+'"/>';
		
		return html;
	},

	renderFrozenCheckbox:function(isChecked, elementID)
	{
		var html = '';
		var value = '';

		if(isChecked===true || isChecked=='yes' || isChecked=='true') {
			html +=
			UI.Icon().OK().MakeSuccess();
			value = 'yes';
		} else {
			html +=
			UI.Icon().NotAvailable().MakeDangerous();
		}

		html +='<input type="hidden" id="'+elementID+'" value="'+value+'"/>';
		return html;
	},

   /**
    * Renders a static form element that displays a value, but
    * cannot be edited.
    *
    * @param {String} label
    * @param {String} displayValue
    * @param {String} helpText 
    * @return {String}
    */
	renderUneditable:function(label, displayValue, helpText)
	{
		return this.renderItem(
			label,
			'',
			'<span class="input-xlarge uneditable-input">'+displayValue+'</span>',
			false,
			helpText
		);
	},

   /**
	* Checks whether the specified string or number is a positive integer.
	* 

	* @param {Number} number
	* @returns {Boolean}
	* @see http://stackoverflow.com/questions/10834796/validate-that-a-string-is-a-positive-integer#10835227
	*/
	validate_integer:function(number)
	{
		return number >>> 0 === parseFloat(number);
	},
	
	'ALIAS_MIN_LENGTH':1,
	'ALIAS_MAX_LENGTH':80,

   /**
    * Checks whether the specified string is a valid item alias, which
    * means it may only contain numbers, letters, hyphens and underscores.
    * Also, its length must be between 2 to 80 characters.
    *

    * @param {String} alias
    * @returns {Boolean}
    */
	validate_alias:function(alias)
	{
		if(typeof(alias)=='undefined') {
			return false;
		}

		var ex = new RegExp('^[a-z][0-9_a-z-.]{'+this.ALIAS_MIN_LENGTH+','+this.ALIAS_MAX_LENGTH+'}$');
		if(ex.test(alias)) {
			return true;
		}

		return false;
	},

   /**
    * Retrieves the text used as validation help for alias fields.
    * Details which characters are allowed, as well as the allowed
    * length.
    *
    * @returns {String}
    */
	getValidationHint_alias:function()
	{
		return t('Allowed characters:')+' '+
		t('Lowercase letters, digits, dots (.), underscores (_) and hyphens (-).')+' '+
		t('%1$s to %2$s characters.', this.ALIAS_MIN_LENGTH, this.ALIAS_MAX_LENGTH)+' '+
		t('Must start with a letter.');
	},
	
	getValidationHint_nohtml:function()
	{
		return t('HTML is not allowed.');
	},

   /**
    * Checks whether the specified string is a valid item label,
    * which means that a range of characters are not allowed to
    * be used. Length is not checked.
    *

    * @param {String} label
    * @returns {Boolean}
    */
	validate_label:function(label)
	{
		if(typeof(label)=='undefined') {
			return false;
		}

		if(/^[/\W\/\w\s'*.\-\(\)\[\]{}?=´`$!&%+#_,;:|]+$/i.test(label)) {
			return true;
		}

		return false;
	},
	
   /**
    * Checks whether the target string contains html tags.
    *
    */
	validate_nohtml:function(text)
	{
		var result = /<[a-z\/][\s\S]*>/i.test(text);
		return !result;
	},

   /**
    * Retrieves the text used as validation help for label fields.
    * Details which characters are allowed, as well as the allowed
    * length.
    *

    * @returns {String}
    */
	getValidationHint_label:function()
	{
		return t('Allowed characters:')+' '+
		t(
			'Lower and uppercase letters, digits, punctuation (%1$s), hyphens (%2$s), underscores (%3$s), special characters (%4$s) and brackets (%5$s).',
			'-',
			'_',
			'.:;,?!',
			'$&%#|*',
			'[]{}()'
		);
	},
	
   /**
    * Generates an alias using transliteration, using the value
    * from a label element to copy it into an alias element.
    * This is mainly used by the serverside formable method
    * <code>appendGenerateAliasButton</code>
    * 
    * @param {Object} buttonEl The DOM button element or UI_Button instance
    * @param {String} aliasID The ID of the alias input element
    * @param {String} labelID The ID of the label input element
    */
	generateAlias:function(buttonEl, aliasID, labelID)
	{
		if(typeof(buttonEl.GetDOMElement) != 'undefined') {
			buttonEl = buttonEl.GetDOMElement();
		}
		
		var aliasEl = $('#'+aliasID);
		var labelEl = $('#'+labelID);
		var buttonEl = $(buttonEl);
		
		// the text snippet after the button
		var text = buttonEl.data('statusText');
		if(text == null) {
			text = UI.Text('');
			buttonEl.after(' ' + text.Render());
			buttonEl.data('statusText', text);
			
			UI.RefreshTimeout(function() {
				FormHelper.generateAlias(buttonEl, aliasID, labelID);
			});
			
			return;
		}
		
		var label = labelEl.val().trim();
		if(isEmpty(label)) 
		{
			labelEl.focus();
			text.MakeNormal();
			text.SetText(t('Please enter a label.'));
			text.ShowAndFade();
			return;
		}	
		
		text.MakeNormal();
		text.SetText(application.renderSpinner(t('Generating...')));
		text.Show();
		
		application.transliterate(
			label, 
			function(string) {
				aliasEl.val(string);
				aliasEl.focus();
				
				text.MakeSuccess();
				text.SetText(t('Alias generated successfully.'));
				text.ShowAndFade();
			}, 
			function() {
				aliasEl.focus();
				
				text.SetText(t('Could not transliterate the string.'));
				text.MakeError();
				text.Show();
			}, 
			'_', 
			true
		);
	},

   /**
    * Creates a new instance of a radio group helper element.
    *
    * @param {String} name
    * @return {FormHelper_RadioGroup}
    */
	createRadioGroup:function(name, activeValue)
	{
		return new FormHelper_RadioGroup(name, activeValue);
	},
	
   /**
    * Creates a single radio element.
    * 
    * @obsolete
    * @param {String} name
    * @param {String} value
    * @param {String} label
    * @return {FormHelper_Radio}
    */
	createRadio:function(name, value, label)
	{
		return new FormHelper_Radio(name, value, label);
	},
	
   /**
    * Adds a log message.
    * @protected
    * @param {String} message
    * @param {String} category
    */
	log:function(message, category)
	{
		application.log('Forms', message, category);
	},
	
   /**
    * Enables the simulation mode in the target form, by setting
    * the hidden variable <code>simulate_only</code>. If it is not
    * already present in the form, it is added automatically.
    * 
    * @param {String} formName The form ID as specified serverside
    */
	enableSimulation:function(formName)
	{
		this.log('Enabling simulation for form [' + formName + ']', 'data');
		
		FormHelper.addHidden(formName, 'simulate_only', 'yes');
	},
	
   /**
    * Adds a hidden var to the specified form.
    * 
    * @param {String} formName The form ID as specified serverside
    * @param {String} name The name of the variable 
    * @param {String} value The value for the variable
    * @returns {DOMElement}
    */
	addHidden:function(formName, name, value)
	{
		if(typeof(value)=='undefined' || value==null) {
			value = '';
		}
		
		var form = this.requireFormElement(formName);
		var id = 'f-' + name;
		
		// check if it already exists, return that instead
		var el = $('#'+id);
		if(el.length > 0) {
			el.val(value); // make sure to set the value
			return el;
		}
		
		this.log('Adding hidden variable [' + name + '] with value [' + value + '].');
		
		var atts = {
			'id':id, 
			'type':'hidden',
			'name':name,
			'value':value
		};
		
		// group all hidden fields in the .hiddens container that
		// automatically gets added in server side forms, and otherwise
		// simply add them to the form itself.
		var hiddensEl = form.find('.hiddens');
		if(hiddensEl.length==0) {
			hiddensEl = form;
		}
		
		hiddensEl.append(
			'<input' + UI.CompileAttributes(atts) + '/>'
		);
		
		return $('#'+id);
	},
	
   /**
    * Retrieves the DOM element of the <code>&lt;form&gt;</code> tag
    * for the specified form name (as specified serverside as the
    * form ID). 
    * 
    * @param {String} formName
    * @returns {DOMElement|NULL}
    */
	getFormElement:function(formName)
	{
		// try to find by id directly
		var el = $('#'+formName);
		if(el.length==1) {
			return el;
		}
		
		// use the common naming convention
		var id = formName;
		if(id.substring(0,4) != 'form-') {
			id = 'form-' + formName;
		}

		var el = $('#'+id);
		if(el.length==1) {
			return el;
		}
		
		return null;
	},
	
   /**
    * Retrieves the DOM element of the <code>&lt;form&gt;</code> tag
    * for the specified form name (as specified serverside as the
    * form ID). Throws an exception if the form element is not found.
    * 
    * @param {String} formName
    * @returns {DOMElement}
    * @throws {ApplicationException}
    */
	requireFormElement:function(formName)
	{
		var el = this.getFormElement(formName);
		if(el != null && el.length == 1) {
			return el;
		}
		
		throw new ApplicationException(
			'Form not found',
			'Cannot find the form [' + formName + '] in the current document.',
			this.ERROR_FORM_NOT_FOUND
		);
	},
	
    /**
     * Puts the focus on the specified element. Accepts an element
     * ID or an existing jquery DOM element. Delays the focus in case
     * the element does not exist in the DOM yet.
     *
     * @param {DOMElement|String} elementOrID
     */
    focusField: function (elementOrID) 
    {
        // allow for elements that have been newly added to the
        // document to finish initializing before we access them
        UI.RefreshTimeout(function () {
            var el = FormHelper.getElement(elementOrID);
            if (isEmpty(el)) {
                FormHelper.log('Cannot find element [' + elementOrID + '] to focus on it.', 'error');
                return;
            }

            // check if the element is in a form section, and whether that
            // is collapsed, in which case we can't focus on the element. 
            var container = el.closest('.content-subsection-body');
            if(container.length > 0 && container.hasClass('collapse') && !container.hasClass('in')) {
            	return;
            }
            
            el.focus();
        });
    },
    
   /**
    * Submits the specified form. Automatically checks whether
    * the form may be submitted or not.
    * 
    * @param {String} formName The form name or ID as specified serverside
    * @param {Boolean} [develMode=false] Whether to submit the form in simulation mode, with the <code>simulate_only</code> variable set to <code>yes</code>
    * @returns {Boolean}
    */
    submit:function(formName, develMode, removeHandlers) 
    {
    	this.log('Submitting form [' + formName + ']. Developer mode: [' + bool2string(develMode, true) + ']');
    	
        if (!this.submissionAllowed) {
            alert(this.formSubmissionReason);
            return false;
        }

        var form = this.requireFormElement(formName);
    	
        if(removeHandlers == true) {
    		form.removeAttr('onsubmit').off(); // remove the submit event handler if any
        }
        
        if (develMode == true) {
            this.enableSimulation(formName);
        }

        form.submit();
        return true;
    },

    'submissionAllowed': true,
    'submissionReason': null,

    preventSubmission:function(reason) 
    {
        this.submissionReason = reason;
        this.submissionAllowed = false;
    },

    allowSubmission:function() 
    {
        this.submissionAllowed = true;
        this.submissionReason = null;
    },
    
    isElementSelect:function(nameIDOrObject)
    {
    	var el = this.getElement(nameIDOrObject);
    	if(!el) {
    		return false;
    	}
    	
    	if(el.prop('tagName')=='SELECT') {
    		return true;
    	}
    	
    	return false;
    },
    
    /**
     * Creates a form element ID using the global naming convention
     * from server side formables, by using the javascript ID of the
     * formable and the element name.
     * 
     * @param {String} jsID
     * @param {String} elementName
     * @returns {String}
     */
 	createElementID:function(jsID, elementName)
 	{
 		elementName = elementName.replace('[', '_');
 		elementName = elementName.replace(']', '');
 		
 		return jsID + '_field_' + elementName;
 	},
 	
   /**
    * Creates a new clientside form object that can be used to
    * create and handle forms with an easy API not unlike the
    * serverside QuickForm API.
    * 
    * @param {String} name
    * @returns {FormHelper_Form}
    */
 	createForm:function(name)
 	{
 		return new FormHelper_Form(name);
 	},
	
	'structuralElements':{},
	
   /**
    * Registers a change with a structural form element.
    * 
    * @param {String} elementID
    * @param {String} formName
    */
	setStructuralElementChanged:function(elementID, formName)
	{
		if(typeof(this.structuralElements[formName]) == 'undefined') {
			this.structuralElements[formName] = [];
		}
		
		if(!in_array(elementID, this.structuralElements[formName])) {
			this.structuralElements[formName].push(elementID);
			this.log('Form ['+formName+'] | Structural element ['+elementID+'] | Element has been modified.', 'event');
		}
	},
	
   /**
    * Checks whether any structural elements were modified in the
    * target form. This is used by serverside forms, see the 
    * PHP UI_Form::makeStructural() method for details, as well as
    * the {@link init()} method.
    * 
    * @param {String} formName
    * @returns {Boolean}
    */
	hasStructuralChanges:function(formName)
	{
		var form = this.getFormElement(formName);
		var name = form.attr('id'); // because the formName != the ID stored in the object
		
		if(typeof(this.structuralElements[name]) != 'undefined') {
			return true;
		}
		
		return false;
	}
};

/**
 * Handles individual checkboxes.
 * 
 * @class
 * @see FormHelper.renderCheckbox
 * @see FormHelper.createCheckbox
 */
var FormHelper_CheckboxLine = 
{
	'id':null,
	'label':null,
	'name':null,
	'checked':null,
	'helpText':null,
	'disabled':null,
	'idPrepend':null,
	'classes':null,
	'attributes':null,
	'idPrepend':null,
		
	init:function(label, name, checked, helpText, idPrepend, disabled) 
	{
		this.id = null;
		this.label = label;
		this.name = name;
		this.checked = checked;
		this.helpText = helpText;
		this.disabled = disabled;
		this.idPrepend = FormHelper.ID_PREFIX;
		this.classes = [];
		this.attributes = {};
		
		if(typeof(idPrepend) != 'undefined' && idPrepend != null) {
			this.idPrepend = idPrepend;
		}
	},
	
   /**
    * Checks whether the element has the specified class.
    *
    * @param {String} name
    * @returns {Boolean}
    */
	HasClass:function(name)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==name) {
				return true;
			}
		}
		
		return false;
	},
	
   /**
    * Adds a class to the element. Each class is only added once.
    * 
    * @param {String} name
    * @returns {FormHelper_CheckboxLine}
    */
	AddClass:function(name)
	{
		if(!this.HasClass(name)) {
			this.classes.push(name);
		}
		
		return this;
	},
	
   /**
    * Sets an attribute of the element, overwriting any existing value.
    *
    * @param {String} name
    * @param {String} value
    * @returns {FormHelper_CheckboxLine}
    */
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	},
	
   /**
    * Sets the ID for the element. An ID is given automatically, 
    * this can be used to overwrite that.
    *
    * @param {String} id
    * @returns {FormHelper_CheckboxLine}
    */
	SetID:function(id)
	{
		this.id = id;
		return this;
	},
	
   /**
    * Retrieves the element's id.

    * @returns {String}
    */
	GetID:function()
	{
		return this.id;
	},
	
	Render:function()
	{
		this.SetAttribute('type', 'checkbox');
		this.SetAttribute('value', 'yes');
		this.SetAttribute('name', this.name);
		this.SetAttribute('id', this.idPrepend+this.name),
		this.SetAttribute('class', this.classes.join(' '));
		
		if(this.checked==true) {
			this.SetAttribute('checked', 'checked');
		}
		
		if(this.id != null) {
			this.SetAttribute('id', this.id);
		}
		
		var checkbox = '<input'+UI.CompileAttributes(this.attributes)+'> '+this.label;

		if(this.disabled==true) {
			checkbox = FormHelper.renderFrozenCheckbox(this.checked, this.attributes.id) + ' ' + this.label;
		}

		var html = ''+
		'<div class="control-group checkbox-line">'+
			'<div class="controls">'+
				'<label class="checkbox">'+
					checkbox +
				'</label>'+
				FormHelper.renderHelp(this.helpText, this.attributes.id)+
			'</div>'+
		'</div>';

		return html;
	},
	
	toString:function()
	{
		return this.render();
	}
};

FormHelper_CheckboxLine = Class.extend(FormHelper_CheckboxLine);
