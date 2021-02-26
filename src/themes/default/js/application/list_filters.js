var Application_ListFilters = 
{
	'id':null,
	'jsID':null,
	'definitions':null,
	'presets':null,
	'elDialogSave':null,
	'elDialogLoad':null,
	'expandedCookieName':null,
		
	init:function(id, jsID)
	{
		this.id = id;
		this.jsID = jsID;
		this.definitions = {};
		this.presets = {};
		this.elDialogSave = null;
		this.elDialogLoad = null;
		this.expandedCookieName = 'filters_'+this.id+'_expanded';
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	GetDefinitions:function()
	{
		return this.definitions;
	},
	
	GetPresets:function()
	{
		return this.presets;
	},
		
	RegisterSetting:function(name, label, elementID)
	{
		this.definitions[name] = {
			'label':label,
			'elementID':elementID
		};
	},
	
	RegisterPreset:function(id, label, settings)
	{
		this.presets[id] = {
			'label':label,
			'settings':settings
		};
	},
	
	DialogLoad:function()
	{
		if(this.elDialogLoad == null) {
			this.elDialogLoad = new Application_ListFilters_Dialog_Load(this);
		}
		
		this.elDialogLoad.Show();
	},
	
	DialogSave:function()
	{
		if(this.elDialogSave == null) {
			this.elDialogSave = new Application_ListFilters_Dialog_Save(this);
		}
		
		this.elDialogSave.Show();
	},
	
	Handle_PresetAdded:function(id, label, settings)
	{
		this.RegisterPreset(id, label, settings);
		
		if(this.elDialogLoad != null) {
			this.elDialogLoad.Handle_PresetAdded();
		}
	},
	
	Handle_PresetDeleted:function(target_id)
	{
		var keep = {};
		$.each(this.presets, function(id, def) {
			if(id != target_id) {
				keep[id] = def;
			}
		});
		
		this.presets = keep;
		
		if(this.elDialogLoad != null) {
			this.elDialogLoad.Handle_PresetDeleted();
		}
	},
	
	Reset:function()
	{
		var el = this.GetFormElement();
		if(el) {
			el.find('[name="reset"]').click();
		}
	},
	
	Submit:function()
	{
		var el = this.GetFormElement();
		if(el) {
			el.find('[name="apply"]').click();
		}
	},
	
	SetHiddenVar:function(name, value)
	{
		var el = this.GetFormElement();
		if(el) {
			el.find('[name="'+name+'"]').val(value);
		}
	},
	
	GetFormElement:function()
	{
		var el = FormHelper.getFormElement(this.id);
		if(el.length == 1) {
			return el;
		}
		
		return null;
	},
	
	DialogSearchExamples:function()
	{
		var html = ''+
		'<p>'+
			t('Guidelines for using the fulltext search:') + 
		'</p>' +
		'<ul>' +
			'<li>' +
				t('Separate keywords by spaces.') +
			'</li>' +
			'<li>' +
				t('The search is case insensitive.') +
			'</li>' +
			'<li>' +
				t(
					'Use %1$s and %2$s to connect keywords logically.',
					'<code>'+t('AND')+'</code>',
					'<code>'+t('OR')+'</code>'
				) + 
			'</li>' +
			'<li>' +
				t(
					'By default, keywords are connected with %1$s.',
					'<code>'+t('AND')+'</code>'
				) +
			'</li>' +
			'<li>' +
				t(
					'Use %1$s to exclude keywords.',
					'<code>'+t('NOT')+'</code>'
				) +
			'</li>' +
			'<li>' +
				t(
					'Search for phrases with spaces by enclosing them in double quotes.'
				) +
		'</li>' +
		'</ul>'+
		'<p>'+
			'<b>' + t('Examples:') + '</b>' +
		'</p>';
		
		var examples = [];
		examples.push(['iphone galaxy', t('Searches for all entries containing both keywords.')]);
		examples.push(['iphone '+t('OR')+' galaxy', t('Searches for all entries containing one or all of the keywords.')]);
		examples.push(['NOT galaxy', t('Searches for all entries except those containing the keyword.')]);
		examples.push(['iphone NOT galaxy', t('Searches for all matches excluding those with the second keyword.')]);
		examples.push(['NOT iphone NOT galaxy NOT huawei', t('Excludes multiple keywords.')]);
		examples.push(['"16 GB" OR "32 GB"', t('Quotes allow searching for exact phrases.')]);
		
		html += ''+
		'<ul>';
			$.each(examples, function(idx, entry) {
				html += '<li><span class="monospace">' + entry[0] + '</span> &raquo; <i>'+entry[1]+'</i></li>';
			});
			html += ''+
		'</ul>';
		
		var dialog = application.createGenericDialog(
			t('Using fulltext search'), 
			html
		)
		.SetIcon(UI.Icon().Help())
		.Show();
	},
	
	DialogDateExamples:function()
	{
		var shortcuts = [t('TODAY'), t('YESTERDAY')];
		
		var html = ''+
		'<p>'+
			t('Guidelines for using the date search:') + 
		'</p>' +
		'<ul>' +
			'<li>' +
				t(
					'Use %1$s and %2$s to connect dates logically.',
					'<code>'+t('FROM')+'</code>',
					'<code>'+t('TO')+'</code>'
				) + 
			'</li>' +
			'<li>' +
				t('Use any of the predefined shortcuts:') + ' '+
				'<code>' + shortcuts.join('</code>, <code>') + '</code>' +
			'</li>' +
		'</ul>'+
		'<p>'+
			'<b>' + t('Examples:') + '</b>' +
		'</p>';
		
		var d = new Date();
		var today = d.getFullYear() + '.' + zerofill(d.getMonth(), 2) + '.' + zerofill(d.getDate(), 2);
		
		var d = new Date();
		d.setDate(d.getDate()-5);
		var yesterday = d.getFullYear() + '.' + zerofill(d.getMonth(), 2) + '.' + zerofill(d.getDate(), 2);
		
		var examples = [];
		examples.push([today, t('Finds all entries on this specific day.')]);
		examples.push([today+' 14:00', t('Finds all entries modified at this exact time.')]);
		examples.push([t('FROM')+' '+yesterday, t('Finds all entries from the specified date to now.')]);
		examples.push([t('TO')+' '+yesterday, t('Finds all entries prior and up to this date.')]);
		examples.push([t('FROM')+' '+yesterday+' '+t('TO')+' '+today, t('Finds all entries between these two dates.')]);
		examples.push([t('TODAY'), t('Finds all entries modified today.')]);
		examples.push([t('YESTERDAY'), t('Finds all entries modified yesterday.')]);
		examples.push([t('FROM')+' '+t('TODAY')+' 09:00', t('Finds all entries modified today, starting at this time.')]);
		examples.push([t('FROM')+' '+t('YESTERDAY')+' 09:00 '+t('TO')+' '+t('TODAY')+' 16:00', t('Combining keywords is also possible.')]);
		
		html += ''+
		'<ul>';
			$.each(examples, function(idx, entry) {
				html += '<li><span class="monospace">' + entry[0] + '</span> &raquo; <i>'+entry[1]+'</i></li>';
			});
			html += ''+
		'</ul>';
		
		var dialog = application.createGenericDialog(
			t('Using the date search'), 
			html
		)
		.SetIcon(UI.Icon().Help())
		.Show();
	},
	
	Start:function()
	{
		var settings = this;
		
		$('#'+this.jsID+' .more-expand').click(function() {
			settings.ExpandMore();
		});
		
		$('#'+this.jsID+' .more-collapse').click(function() {
			settings.CollapseMore();
		});
		
		if(application.getCookie(this.expandedCookieName) == 'yes') {
			this.ExpandMore();
		}
	},
	
	ExpandMore:function()
	{
		$('#'+this.jsID+' .more-collapse').show();
		$('#'+this.jsID+' .more-expand').hide();
		$('#'+this.jsID+' .more-elements').show();
		
		application.setCookie(this.expandedCookieName, 'yes');
	},
	
	CollapseMore:function()
	{
		$('#'+this.jsID+' .more-collapse').hide();
		$('#'+this.jsID+' .more-expand').show();
		$('#'+this.jsID+' .more-elements').hide();
		
		application.setCookie(this.expandedCookieName, 'no');
	}
};

Application_ListFilters = Class.extend(Application_ListFilters);