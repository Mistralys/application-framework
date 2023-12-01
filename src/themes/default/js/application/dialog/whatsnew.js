/**
 * Handles the dialog for developer settings regarding
 * clientside logging: lets the use choose which types
 * of logging messages should be shown in the browser's
 * console window.
 * 
 * @package Application
 * @subpackage Dialogs
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends Dialog_Basic
 */
var Application_Dialog_Whatsnew = 
{
	_GetTitle:function()
	{
		return t('What\'s new');
	},
	
	_RenderBody:function()
	{
		var html = application.renderSpinner(t('Please wait, loading...'));
		return html;
	},
	
	_PostRender:function()
	{
		var dialog = this;
		
		application.AJAX(
			'GetWhatsnew',
			{},
            function (data) {
                dialog.Handle_LoadSuccess(data);
            },
	        function (errorText, data) {
                dialog.Handle_LoadFailure(errorText);
            }
	    );
	},

	_RenderFooter:function()
	{
		var dialog = this;
		
		this.AddButtonRight(
			UI.Button(t('Show all versions'))
			.SetIcon(UI.Icon().SelectAll())
			.MakeDisabled()
			.Click(function() {
				dialog.ShowAll();
			}),
			'showall'
		);
		
		this.AddButtonClose();
	},
	
	ShowAll:function()
	{
		var dialog = this;
		var html = '';
		$.each(this.data.versions, function(version) {
			html += '<h3 class="whatsnew-version">v' + version + '</h3>'+
			dialog.RenderCategories(version)+
			'<hr/>';
		});
		
		this.ChangeBody(html);
	},
	
	RenderCategories:function(version)
	{
		var categories = this.GetVersion(version);
		
		if(categories.length == 0) {
			return '<p>' + t('No changes were described for v%1$s.', version) + '</p>';
		}
		
		var html = '';
		var amount = 0;
		$.each(categories, function() {
			amount++;
		});
		
		$.each(categories, function(idx, def) {
			if(amount > 1) {
				html += '<h4 class="whatsnew-category">'+def.label+'</h4>';
			}
			
			html += ''+
			'<ul class="whatsnew-items">';
				$.each(def.items, function(idx, item) 
				{
					var text = '';

					if(typeof(item.devCategory) !== "undefined")
					{
						text += t('%1$s:', item.devCategory);
					}

					if(item.issue !== null && item.issue !== '')
					{
						text += ' ' + t('Issue %1$s', item.issue);
					}

					if(item.author !== null && item.author !== '')
					{
						text += ' <i class="muted">~'+item.author+'</i>';
					}

					text += item.text;
					
					html += ''+
					'<li>'+
						text+
					'</li>';
				});
				html += ''+
			'</ul>';
		});		
		
		return html;
	},
	
	'processed':{},
	
	GetVersion:function(version)
	{
		if(typeof(this.processed[version]) !== 'undefined') {
			return this.processed[version];
		}
		
		var cats = this.data.versions[version];
		var dev = [];

		if(typeof(this.data.dev[version]) !== 'undefined') {
			dev = this.data.dev[version];
		}
		
		if(cats == null) {
			cats = [];
		}
		
		if(User.isDeveloper() && dev != null) 
		{
			// add the developer changes as an additional
			// category to the current version changes
			var cat = {
				'label':t('Developer changes'),
				'items':[]
			};
			
			$.each(dev, function(idx, catEntry)
			{
				$.each(catEntry.items, function(idx2, item)
				{
					item.devCategory = catEntry.label;
					cat.items.push(item);
				});
			});
			
			cats.push(cat);
		}
		
		this.processed[version] = cats;
		
		return cats;
	},
	 
	'data':null,
	
	Handle_LoadSuccess:function(data) 
	{
		this.data = data;
		
		this.GetButton('showall').MakeEnabled().MakePrimary();
		
        var html = '' +
        '<p>' + 
        	t('Release notes for %1$s version %2$s:', application.appNameShort, this.GetVersionNumber()) + 
    	'</p>'+
    	this.RenderCategories(this.GetVersionNumber());
        
        this.ChangeBody(html);
    },
    
    GetVersionNumber:function()
    {
    	return this.data.current_version;
    },

    Handle_LoadFailure: function (errorText) 
    {
	    this.ChangeBody('');
	    
	    this.ShowAlertError(
			UI.Icon().Warning() + ' ' + 
    		'<b>' + t('Could not load the application changelog.') + '</b> '+
    		t('Reason given:') + ' ' +
    		errorText
		);
	}
};

Application_Dialog_Whatsnew = Dialog_Basic.extend(Application_Dialog_Whatsnew);