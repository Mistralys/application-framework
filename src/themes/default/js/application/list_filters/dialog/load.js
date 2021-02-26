/**
 * Handles the dialog to load an existing list filter.
 * 
 * @package Application
 * @subpackage DataGrids
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class
 * @extend Dialog_Basic
 */
var Application_ListFilters_Dialog_Load = 
{
	'filters':null,
	'refresh':null,
	'working':null,
		
	init:function(filters)
	{
		this._super();
		
		this.filters = filters;
		this.refresh = false;
		this.working = false;
	},
		
	_RenderBody:function()
	{
		return this.RenderList();
	},
	
	Handle_PresetAdded:function()
	{
		this.refresh = true;
	},
	
	RenderList:function()
	{
		var defs = this.filters.GetDefinitions();
		var presets = this.filters.GetPresets();
		var dialog = this;
		
		// count presets
		var total = 0;
		$.each(presets, function(id, preset) {
			total++;
		});
		
		if(total == 0) {
			return application.renderAlertInfo(t('No filters found.'), false);
		}
		
		html = ''+
		'<table class="table table-hover">'+
			'<thead>'+
				'<tr>'+
					'<th width="1%"></th>'+
					'<th>'+t('Label')+'</th>';
						$.each(defs, function(name, def) 
						{
							html += '' +
							'<th>' + def.label + '</th>';
						});
						html += '' +
					'<th></th>'+
				'</tr>'+
			'</thead>'+
			'<tbody>';
				$.each(presets, function(id, preset) 
				{
					var jsID = nextJSID();
					var btnConfirmID = jsID+'-btn-delete';
					html += '' +
					'<tr>' +
						'<td>' +
							UI.Button()
								.SetIcon(UI.Icon().Filter())
								.SetTooltip(t('Apply this filter'))
								.MakeMini()
								.MakePrimary()
								.Click(function() {
									dialog.Handle_ApplyPreset(id, preset);
								}) +
						'</td>' +
						'<td>' + preset.label + '</td>';
						$.each(defs, function(name, def) {
							html += '' +
							'<td>' + dialog.MakeReadable(name, def, preset.settings[name]) + '</td>';
						});
						html +=
						'<td class="align-right">'+
							UI.Button(t('Confirm'))
								.SetID(btnConfirmID)
								.MakeMini()
								.MakeDangerous()
								.SetStyle('visibility', 'hidden')
								.SetLoadingText(t('Deleting...'))
								.Click(function() {
									dialog.Handle_DeletePreset(id, btnConfirmID);
								}) + ' ' +
							UI.Button()
								.SetIcon(UI.Icon().Delete())
								.MakeWarning()
								.SetTooltip(
									t('Delete this filter.') + ' ' + 
									t('Note:') + ' ' + t('Must be confirmed.')
								)
								.MakeMini()
								.Click(function() {
									if(dialog.working) {
										return;
									}
									
									var btn = UI.GetButton(jsID+'-btn-delete');
									if(btn.GetStyle('visibility')=='hidden') {
										btn.SetStyle('visibility', 'visible');
									} else {
										btn.SetStyle('visibility', 'hidden');
									}
								}) +
						'</td>'+
					'</tr>';
				});
				html += ''+
			'</tbody>'+
		'</table>';
				
		return html;
	},
	
	Handle_ApplyPreset:function(id, preset)	
	{
		var defs = this.filters.GetDefinitions();
		
		$.each(defs, function(setting, def) {
			var el = $('#'+def.elementID);
			var value = preset['settings'][setting];
			FormHelper.setElementValue(el, value);
		});
		
		this.Hide();
		
		application.showLoader(t('Please wait, applying filter...'));
		application.submitForm(this.filters.GetID());
	},
	
   /**
    * Called when the user clicks the confirm delete preset button.
    * 
    * @param {Integer} id The ID of the filter
    * @param {String} btnConfirmID The ID of the confirm delete button
    */
	Handle_DeletePreset:function(id, btnConfirmID)
	{
		// avoid double-clicks or any operations while we're already doing something
		if(this.working) {
			return;
		}
		
		this.working = true;
		
		// Show the user something's happening
		UI.GetButton(btnConfirmID).Loading();
		
		var payload = {
			'settings_id':this.filters.GetID(),
			'filter_id':id
		};
		
		var dialog = this;
		application.AJAX(
			'DeleteListFilter', 
			payload, 
			function(data) {
				dialog.Handle_DeletePresetSuccess(data, btnConfirmID);
			}, 
			function(errorText, data) {
				dialog.Handle_DeletePresetFailure(errorText, data, btnConfirmID);
			}
		);
	},
	
	Handle_DeletePresetSuccess:function(data, btnConfirmID)
	{
		this.working = false;
		
		UI.GetButton(btnConfirmID).Reset();
		
		this.filters.Handle_PresetDeleted(data.filter_id);
	},
	
	Handle_DeletePresetFailure:function(errorText, data, btnConfirmID)
	{
		this.working = false;
		
		UI.GetButton(btnConfirmID).Reset();
		
		this.ShowAlertError(
			UI.Icon().Warning() + ' ' +
			t('Could not delete the filter.') + ' ' +
			t('Reason given:') + ' ' +
			errorText
		);
	},
	
   /**
    * Called when a preset has been deleted from the list
    * filters main collection.
    */
	Handle_PresetDeleted:function()
	{
		this.refresh = true;
		this.Refresh();
	},
	
	Handle_AddNew:function()
	{
		this.Hide();
		this.filters.DialogSave();
	},
	
	_RenderFooter:function()
	{
		var dialog = this;
		
		this.AddButtonRight(
			UI.Button(t('Save current...'))
			.SetIcon(UI.Icon().Add())
			.Click(function() {
				dialog.Handle_AddNew();
			})
		);
		
		this.AddButtonClose();
	},
	
	GetTitle:function()
	{
		return UI.Icon().Filter() + ' ' + t('Use an existing filter');
	},
	
	_Handle_Shown:function()
	{
		this.Refresh();
	},
	
	Refresh:function()
	{
		if(this.refresh) {
			this.ChangeBody(this.RenderList());
			this.refresh = false;
		}
	},
	
   /**
    * Converts a setting value to a readable value by checking the
    * type of form field in the filter form, and fetching the correct
    * label, like the matching option label in case of select elements.
    * 
    * FIXME: This should be moved to the form helper and made generic.
    * 
    * @param {String} setting Name of the setting
    * @param {Object} def Definition of the setting 
    * @param {Mixed} value
    * @returns string
    */
	MakeReadable:function(setting, def, value)
	{
		if(value==null || value=='' || (typeof(value)=='object' && typeof(value['length']) != 'undefined' && value.length==0)) {
			return '<span class="muted">(' + t('Empty value') + ')</span>';
		}
		
		var el = $('#'+def.elementID);
		
		if(FormHelper.isElementSelect(el)) {
			// multiple values
			if(typeof(value)=='object') {
				var display_value = value.pop();
				var display = display_value;
				var option = el.find('option[value="'+display_value+'"]');
				if(option.length > 0) {
					display = option.html();
				}
				
				if(value.length == 1) {
					display += ' + ' + t('1 more');
				} else if(value.length > 1) {
					display += ' + ' + t('%1$s more', value.length);
				}
				
				// required because value is a reference value, and
				// would otherwise modify the settings object.
				value.push(display_value);
				
				return display;	
			} else {
				var option = el.find('option[value="'+value+'"]');
				if(option.length > 0) {
					return option.html();
				}
				return value;
			}
		}
		
		return value;
	}
};

Application_ListFilters_Dialog_Load = Dialog_Basic.extend(Application_ListFilters_Dialog_Load);