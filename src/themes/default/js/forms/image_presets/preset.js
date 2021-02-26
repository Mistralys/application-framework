/**
 * Container class for single image presets in a collection
 * of image presets. 
 * 
 * @package Maileditor
 * @subpackage Products
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see FormHelper_ImagePresets.AddPreset
 */
var FormHelper_ImagePresets_Preset =
{
   /**
    * @type {FormHelper_ImagePresets}
    */
	'presets':null,
	
   /**
    * @type {Object}
    */
	'data':null,
	
   /**
    * @type {String}
    */
	'id':null,
	
	'jsID':null,
	
   /**
    * Tracks whether the preset has been rendered.
    * @type {Boolean}
    */
	'rendered':null,
	
	'deleting':null,
	'state':null,
		
	init:function(presets, id, name, alias, type, width, height, state)
	{
		if(typeof(state)=='undefined') {
			state = 'normal';
		}
		
		this.presets = presets;
		this.id = id;
		this.jsID = nextJSID();
		this.rendered = false;
		this.deleting = false;
		this.state = state;
		this.data = {
			'name':name,
			'alias':alias,
			'type':type,
			'width':width,
			'height':height
		};
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	IsNew:function()
	{
		if(this.state=='new') {
			return true;
		}
		
		return false;
	},
	
	Refresh:function()
	{
		this.Render();
	},
	
	Render:function()
	{
		if(this.rendered) {
			return;
		}
		
		this.rendered = true;
		
		var preset = this;
		var width = this.data.width;
		var height = this.data.height;
		
		switch(this.data.type)
		{
			case 'fixed_width':
				height = UI.Icon().Minus().MakeMuted().Render();
				break;
				
			case 'fixed_height':
				width = UI.Icon().Minus().MakeMuted().Render();
				break;
				
			case 'unconstrained':
				height = UI.Icon().Minus().MakeMuted().Render();
				width = UI.Icon().Minus().MakeMuted().Render();
				break;
		}
		
		var html = ''+
		'<tr id="'+this.elementID('row')+'">'+
			'<td id="'+this.elementID('cell-name')+'">'+this.data.name+'</td>'+
			'<td id="'+this.elementID('cell-alias')+'" class="monospace">'+this.data.alias+'</td>'+
			'<td id="'+this.elementID('cell-type')+'" class="nowrap">'+this.GetTypePretty()+'</td>'+
			'<td id="'+this.elementID('cell-width')+'" class="align-right">'+width+'</td>'+
			'<td id="'+this.elementID('cell-height')+'" class="align-right">'+height+'</td>'+
			'<td>'+
				'<div class="btn-group" id="'+this.elementID('buttons')+'">'+
					UI.Button()
						.SetIcon(UI.Icon().Delete())
						.MakeMini()
						.MakeDangerous()
						.SetTooltip(t('Delete this preset.') + ' ' + t('Must be confirmed.'))
						.Click(function() {
							preset.Handle_ClickDeletePreset();
						})+
				'</div>'+
			'</td>'+
		'</tr>';
		
		this.presets.GetListBodyElement().append(html);
		
		UI.RefreshTimeout(function() {
			preset.PostRender();
		});
	},
	
	Handle_ClickDeletePreset:function()
	{
		if(this.deleting) {
			this.Handle_ClickCancelDelete();
			return;
		}
		
		this.deleting = true;
		var preset = this;
		
		this.element('buttons').append(
			UI.Button()
				.SetID(this.elementID('btn-delete-confirm'))
				.SetIcon(UI.Icon().OK())
				.MakeMini()
				.MakeWarning()
				.SetTitle(t('Confirm'))
				.Click(function() {
					preset.Handle_ClickConfirmDelete();
				})+
			UI.Button()
				.SetID(this.elementID('btn-delete-cancel'))
				.SetIcon(UI.Icon().Disabled())
				.MakeMini()
				.MakeSuccess()
				.SetTitle(t('Cancel'))
				.Click(function() {
					preset.Handle_ClickCancelDelete();
				})
		);
	},
	
	Handle_ClickConfirmDelete:function()
	{
		this.element('row').remove();
		this.presets.Handle_PresetDeleted(this);
	},
	
	Handle_ClickCancelDelete:function()
	{
		this.element('btn-delete-confirm').remove();
		this.element('btn-delete-cancel').remove();
		this.deleting = false;
	},
	
	PostRender:function()
	{
		
	},
	
	GetTypePretty:function()
	{
		return this.presets.GetPresetTypeLabel(this.data.type);
	},
	
	SetSaved:function()
	{
		this.state = 'normal';
	},
	
	ToCSV:function()
	{
		var items = [];
		items.push(this.GetID());
		$.each(this.data, function(key, value) {
			if(value==null) {
				value = '';
			}
			items.push('"' + value + '"');
		});
		
		return items.join(',');
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
			'Image preset ['+this.jsID+'] ['+this.data.name+']',
			message,
			category
		);
	}
};

FormHelper_ImagePresets_Preset = Class.extend(FormHelper_ImagePresets_Preset);