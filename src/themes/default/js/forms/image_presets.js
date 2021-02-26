/**
 * Image presets manager: Creates the UI to add and edit image
 * presets with name, alias and resize mode. The presets data
 * can be imported from and exported to CSV format, with the 
 * <code>FromCSV</code> and <code>ToCSV</code> methods respectively.
 * 
 * Usage:
 * 
 * <pre>
 * // create a presets handler instance with the ID of a dom element
 * // into which the presets UI will be inserted. 
 * var handler = new FormHelper_ImagePresets(
 *     'dom_element_id', 
 *     function() {
 *         // event handler; called when presets are modified by the user
 *     }
 * );
 *	
 * // give the handler the initial presets. Can be an empty string.
 * handler.FromCSV(presetsCSV);
 * 
 * // start the UI: this will inject the HTML into the target dom container
 * handler.Start();
 * </pre>
 * 
 * @package Application
 * @subpackage Forms
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 */
var FormHelper_ImagePresets = 
{
	'ERROR_INVALID_PRESET_TYPE':630001,
		
	'containerID':null,
	'container':null,
	'modifiedHandler':null,
	'presets':null,
	'presetTypes':null,
	'idCounter':null,
	
   /**
    * @constructs
    * @param {String} containerID
    * @param {Function} modifiedHandler Event handler, called when the presets are modified
    */
	init:function(containerID, modifiedHandler)
	{
		this.jsID = nextJSID();
		this.containerID = containerID;
		this.presets = [];
		this.modifiedHandler = modifiedHandler;
		this.container = null;
		this.idCounter = 0;
		
		this.presetTypes = {
			'fixed':t('Fixed size'),
			'fixed_width':t('Fixed width'),
			'fixed_height':t('Fixed height'),
			'unconstrained':t('Unconstrained')
		};
	},
	
   /**
    * Adds a preset to the collection.
    * 
    * @param {Integer} id
    * @param {String} name
    * @param {String} alias
    * @param {String} type
    * @param {Integer} width
    * @param {Integer} height
    * @param {String} [state=normal]
    * @return {FormHelper_ImagePresets_Preset}
    */
	AddPreset:function(id, name, alias, type, width, height, state)
	{
		this.log('Adding the preset ['+alias+'].', 'data');
		
		if(!this.PresetTypeExists(type)) {
			throw new ApplicationException(
				'Invalid preset type',
				'The preset type ['+type+'] is not a valid type. Valid types are: [' + this.GetPresetTypeIDs().join(', ') + '].',
				this.ERROR_INVALID_PRESET_TYPE
			);
		}
		
		var preset = new FormHelper_ImagePresets_Preset(this, id, name, alias, type, width, height, state);
		this.presets.push(preset);
		
		return preset;
	},
	
   /**
    * Retrieves an indexed array with all available 
    * IDs of the image resizing types.
    * 
    * @returns {Array}
    */
	GetPresetTypeIDs:function()
	{
		var ids = [];
		$.each(this.presetTypes, function(type, label) {
			ids.push(type);
		});
		
		return ids;
	},
	
   /**
    * Retrieves the human readable label for the specified
    * image resizing type.
    * 
    * @param {String} type
    * @returns {String} The label, or an empty string if unknown
    */
	GetPresetTypeLabel:function(type)
	{
		if(typeof(this.presetTypes[type])!='undefined') {
			return this.presetTypes[type];
		}
		
		return '';
	},
	
   /**
    * Checks whether the target alias already exists in one of
    * the available presets.
    * 
    * @param {String} alias
    * @returns {Boolean}
    */
	AliasExists:function(alias)
	{
		for(i=0; i<this.presets.length; i++) {
			if(this.presets[i].GetAlias()==alias) {
				return true;
			}
		}
		
		return false;
	},
	
   /**
    * Checks whether the target image resizing type ID exists.
    * 
    * @param {String} type
    * @returns {Boolean}
    */
	PresetTypeExists:function(type)
	{
		if(typeof(this.presetTypes[type])!='undefined') {
			return true;
		}
		
		return false;
	},
	
   /**
    * Starts the UI of the presets manager: renders the required
    * HTML, and injects it into the DOM element specified at instantiation.
    * 
    * @returns {Boolean} Whether the manager could be started successfully
    */
	Start:function()
	{
		this.log('Starting the image presets management.', 'ui');
		
		this.container = $('#'+this.containerID);
		if(this.container.length==0) {
			this.log('Cannot find container element [#'+this.containerID+'].', 'error');
			return false;
		}
		
		this.Render();
		return true;
	},
	
	Render:function()
	{
		this.log('Rendering the presets table.', 'ui');
		
		var obj = this;
		
		var html = ''+
		'<table class="table table-condensed">'+
			'<thead>'+
				'<tr>'+
					'<th>'+t('Name')+'</th>'+
					'<th>'+t('Alias')+'</th>'+
					'<th style="width:1%">'+t('Type')+'</th>'+
					'<th style="width:1%" class="align-right">'+t('Width')+'</th>'+
					'<th style="width:1%" class="align-right">'+t('Height')+'</th>'+
					'<th style="width:1%"></th>'+
				'</tr>'+
			'</thead>'+
			'<tfoot>'+
				'<tr id="'+this.elementID('messages')+'" style="display:hidden">'+
					'<td colspan="6" id="'+this.elementID('messages-body')+'" class="cell-messages"></td>'+
				'</tr>'+
				'<tr id="'+this.elementID('list-form-add')+'" style="display:none;">'+
					'<td>'+
						'<input type="text" id="'+this.elementID('new-name')+'"/>'+
					'</td>'+
					'<td>'+
						'<input type="text" id="'+this.elementID('new-alias')+'"/>'+
					'</td>'+
					'<td>'+
						FormHelper.renderSelect(
							this.elementID('new-type'), 
							this.presetTypes, 
							null, 
							{
								'attributes':{'style':'width:150px'}
							}
						)+
					'</td>'+
					'<td class="align-right">'+
						'<input type="number" min="1" class="input-number-4" id="'+this.elementID('new-width')+'"/>'+
					'</td>'+
					'<td class="align-right">'+
						'<input type="number" min="1" class="input-number-4" id="'+this.elementID('new-height')+'"/>'+
					'</td>'+
					'<td>'+
						UI.ButtonGroup()
						.Add(
							UI.Button()
								.SetIcon(UI.Icon().OK())
								.MakeSuccess()
								.MakeSmall()
								.Click(function() {
									obj.Handle_ClickConfirmAddPreset();
								})
						)
						.Add(
							UI.Button()
								.SetIcon(UI.Icon().Delete())
								.MakeDangerous()
								.MakeSmall()
								.Click(function() {
									obj.Handle_ClickCancelAddPreset();
								})
						)+
					'</td>'+
				'</tr>'+
			'</tfoot>'+
			'<tbody id="'+this.elementID('list-body')+'">'+
			'</tbody>'+
		'</table>'+
		'<p id="'+this.elementID('btn-add')+'">'+
			UI.Button(t('Add preset...'))
				.SetIcon(UI.Icon().Add())
				.MakeSmall()
				.Click(function() {
					obj.Handle_ClickAddPreset();
				})+
		'</p>'+
		'<div id="'+this.element('footer')+'">'+
		'</div>';
		
		this.container.html(html);
		
		var obj = this;
		UI.RefreshTimeout(function() {
			obj.PostRender();
		});
	},
	
	Handle_ClickAddPreset:function()
	{
		this.log('Handling click on the add preset button.', 'event');
		
		this.element('list-form-add').show();
		this.element('btn-add').hide();

		this.ClearMessages();
		
		FormHelper.resetFields(
			this.elementID('new-name'),
			this.elementID('new-alias'),
			this.elementID('new-type'),
			this.elementID('new-width'),
			this.elementID('new-height')
		);
		
		this.element('new-name').focus();
	},
	
	Handle_ClickConfirmAddPreset:function()
	{
		var values = this.ValidatePresetForm();
		if(!values) {
			return;
		}
		
		this.ClearMessages();
		this.element('list-form-add').hide();
		this.element('btn-add').show();
		
		this.AddPreset(this.GetNextPresetID(), values.name, values.alias, values.type, values.width, values.height, 'new');
		
		this.SetModified(true);
		
		this.Refresh();
	},
	
	GetNextPresetID:function()
	{
		var next = 0;
		$.each(this.presets, function(idx, preset) {
			if(preset.GetID() > next) {
				next = preset.GetID();
			}
		});
		
		next++;
		
		return next;
	},
	
	Handle_PresetDeleted:function(targetPreset)
	{
		var keep = [];
		$.each(this.presets, function(idx, preset) {
			if(preset != targetPreset) {
				keep.push(preset);
			}
		});
		
		this.presets = keep;
		
		this.SetModified(true);
	},
	
	FromCSV:function(csv)
	{
		this.log('Reading csv string', 'data');
		
		if(typeof(csv)=='undefined' || csv==null || csv.length==0) {
			return;
		}
		
		var lines = csv.split('\n');
		
		for(i=0; i<lines.length; i++) {
			values = lines[i].split(',');
			id = trim(values[0], '"');
			name = trim(values[1], '"');
			alias = trim(values[2], '"');
			type = trim(values[3], '"');
			width = trim(values[4], '"');
			height = trim(values[5], '"');
			
			try{
				this.AddPreset(id, name, alias, type, width, height);
			} catch(e) {
				// ignore errors, just log them
				this.log('The CSV preset line ['+lines[i]+'] is not valid.', 'error');
			}
		}
	},
	
	SetSaved:function()
	{
		$.each(this.presets, function(idx, preset) {
			preset.SetSaved();
		});
		
		return this;
	},
	
	SetModified:function(structural)
	{
		this.modifiedHandler.call(null, this, structural);
	},
	
	GetPresets:function()
	{
		return this.presets;
	},
	
	ToCSV:function()
	{
		var presets = [];
		$.each(this.presets, function(idx, preset) {
			presets.push(preset.ToCSV());
		});
		
		return presets.join('\n');
	},
	
	Handle_ClickCancelAddPreset:function()
	{
		this.element('list-form-add').hide();
		this.element('btn-add').show();
	},
	
	Handle_NewTypeChanged:function()
	{
		var type = this.element('new-type').val();

		this.element('new-width').prop('disabled', false);
		this.element('new-height').prop('disabled', false);
		
		switch(type) {
			case 'fixed':
				break;
				
			case 'fixed_width':
				this.element('new-height').val('').prop('disabled', true);
				
				break;
				
			case 'fixed_height':
				this.element('new-width').val('').prop('disabled', true);
				break;
				
			case 'unconstrained':
				this.element('new-height').val('').prop('disabled', true);
				this.element('new-width').val('').prop('disabled', true);
				break;
		}
	},
	
	ValidatePresetForm:function()
	{
		var data = {
			'name':trim(this.element('new-name').val()),
			'alias':trim(this.element('new-alias').val()),
			'type':this.element('new-type').val(),
			'width':this.element('new-width').val(),
			'height':this.element('new-height').val()
		};
		
		if(!FormHelper.validate_label(data.name)) {
			this.DisplayErrorMessage(t('Please enter a valid label.'));
			this.element('new-name').focus();
			return false;
		}
		
		if(!FormHelper.validate_alias(data.alias)) {
			this.DisplayErrorMessage(t('Please enter a valid alias.'));
			this.element('new-alias').focus();
			return false;
		}
		
		var widthRequired = false;
		var heightRequired = false;
		
		switch(data.type) {
			case 'fixed':
				widthRequired = true;
				heightRequired = true;
				break;
				
			case 'fixed_width':
				widthRequired = true;
				break;
				
			case 'fixed_height':
				heightRequired = true;
				break;
		}
		
		if(widthRequired && !FormHelper.validate_integer(data.width)) {
			this.DisplayErrorMessage(t('Please enter a width.'));
			this.element('new-width').focus();
			return false;
		}
		
		if(heightRequired && !FormHelper.validate_integer(data.height)) {
			this.DisplayErrorMessage(t('Please enter a height.'));
			this.element('new-height').focus();
			return false;
		}
		
		return data;
	},
	
	PostRender:function()
	{
		this.log('Doing the post rendering.', 'ui');
		
		var obj = this;
		this.element('new-type')
			.on('change', function(){
				obj.Handle_NewTypeChanged();
			});	
		
		this.Refresh();
	},
	
	Refresh:function()
	{
		this.log('Refreshing the presets list.', 'ui');
		
		$.each(this.presets, function(idx, preset) {
			preset.Refresh();
		});
	},
	
	GetListBodyElement:function()
	{
		return this.element('list-body');
	},
	
	DisplayErrorMessage:function(message)
	{
		this.element('messages').show();
		this.element('messages-body').html(application.renderAlertError(
			UI.Icon().Warning() + ' ' +
			message
		));
	},
	
	ClearMessages:function()
	{
		this.element('messages-body').html('');
		this.element('messages').hide();
	},
	
	Validate:function()
	{
		if(this.presets.length > 0) {
			this.ClearMessages();
			return true;
		}
		
		this.DisplayErrorMessage(t('You have to add at least one preset.'));
		return false;
	},
	
	elementID:function(part)
	{
		if(typeof(part)=='undefined') {
			return this.jsID;
		}
		
		return this.jsID+'_'+part;
	},
	
	element:function(part)
	{
		var id = this.elementID(part);
		return $('#'+id);
	},
	
	log:function(message, category)
	{
		application.log(
			'Image presets ['+this.jsID+']',
			message,
			category
		);
	}
};

FormHelper_ImagePresets = Class.extend(FormHelper_ImagePresets);