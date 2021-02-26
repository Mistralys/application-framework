/**
 * Handles the detail dialog for a single changelog entry, 
 * which is used to display additional information like the
 * value before and after.
 * 
 * @package Application
 * @subpackage Changelog
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var Changelog_Dialog_Entry = 
{
	_GetTitle:function()
	{
		return t('Changelog entry #%1$s', this.entry.id);
	},
	
	_RenderAbstract:function()
	{
		return this.entry.text;
	},
	
	_RenderBody:function()
	{
		var html = ''+
		'<table class="table">'+
			'<tbody>'+
				'<tr>'+
					'<th width="1%">' + t('Before') + '</th>' + 
					'<td>' + this.RenderValue(this.entry.before) + '</td>' + 
				'</tr>' + 
				'<tr>' +
					'<th>' + t('After') + '</th>' + 
					'<td>' + this.RenderValue(this.entry.after) + '</td>' +
				'</tr>' +
			'</tbody>' +
		'</table>';
		
		return html;
	},
	
	'entry':null,
	
	SetEntry:function(entry)
	{
		this.entry = entry;
	},
	
	RenderValue:function(value)
	{
		if(isBoolean(value)) {
			var bool = string2bool(value);
			if(value==true) {
				value = 'true';
			} else if(value==false) {
				value = 'false';
			}
			
			if(bool==true) {
				return UI.Icon().OK().MakeSuccess() + ' ' + 
				t('Yes') + ' ' +
				'<span class="muted">' +
					'(<span class="monospace">' +  value + '</span>)' +
				'</span>';
			} else {
				return UI.Icon().Delete().MakeDangerous() + ' ' + 
				t('No') + ' ' +
				'<span class="muted">'+
					'(<span class="monospace">' +  value + '</span>)' +
				'</span>';
			}
		}
		
		if(value==null || value.length==0) {
			return '<span class="muted">(' + t('Empty value') + ')</span>';
		}
		
		return value;
	}
};

Changelog_Dialog_Entry = Dialog_Basic.extend(Changelog_Dialog_Entry);