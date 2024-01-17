var Application_Dialog_LookupItems =
{
	'ERROR_LOOKUP_FAILED':88001,
	'ERROR_LOADING_ITEMS_FAILED':88002,

	'form':null,
	'lookupItems':null,
	'preselect':null,
	'focusElement':null,

	_init:function()
	{
		this.form = null;
		this.lookupItems = null;
		this.preselect = null;
		this.focusElement = null;

		this.SetIcon(UI.Icon().Search());
	},

	_GetTitle:function()
	{
		return t('%1$s item lookup', application.appNameShort);
	},

	_RenderAbstract:function()
	{
		return t('Quickly find records using the item lookup:') + ' ' +
			t('The types of record you can search for are shown in the form below.');
	},

	_Handle_Shown:function()
	{
		if(this.lookupItems == null) {
			this.LoadLookupItems();
			return;
		}

		if(this.form == null)
		{
			this.CreateForm();
			this.ChangeBody(
				this.form.Render() +
				this.RenderHints() +
				'<hr>' +
				'<div id="'+this.elementID('results')+'">'+
					t('Search results will be shown here.') +
				'</div>'
			);

			if(this.preselect != null)
			{
				var form = this.form;
				var tokens = this.preselect.split(':');
				var elementType = tokens[0];
				var idsList = tokens[1];

				UI.RefreshTimeout(function() {
					var el = form.GetElementByName('terms_'+elementType);
					if(el) {
						el.SetValue(idsList);
						form.Submit();
					}
				});
			}
		}

		this.focusElement.Focus();
	},

	RenderHints:function()
	{
		return ''+
		'<p>' +
			'<small>'+
				'<strong>' + t('Search hints:') + '</strong> ' +
				t('The search is case insensitive.') + ' ' +
				t('Separator characters like underscores (_) and dashes (-) are ignored.') + ' ' +
				t('Multiple search terms can be separated with commas.') + ' ' +
				t('Items match if all search terms are found.') + ' ' +
			'</small>' +
		'</p>';
	},

	_RenderBody:function()
	{
		return application.renderSpinner(t('Please wait, loading data...'));
	},

	_RenderFooter:function()
	{
		var dialog = this;

		this.AddButtonPrimary(
			t('Look up now'),
			function() {
				dialog.form.Submit();
			}
		);

		this.AddButtonCancel();
	},

	/**
	 * Sets element IDs to preselect in the dialog.
	 * Expexts a string formatted this way:
	 *
	 * <code>ElementType:search terms</code>
	 *
	 * The first part being the element type, and
	 * the second the search terms to pre-fill the
	 * according form element with.
	 */
	SetPreselect:function(preselect)
	{
		if(!isEmpty(preselect)) {
			this.preselect = preselect;
		}
	},

	LoadLookupItems:function()
	{
		var dialog = this;

		application.createAJAX('GetLookupItems')
			.Error(t('Could not load the available lookup items.'), this.ERROR_LOADING_ITEMS_FAILED)
			.Success(function(responsePayload) {
				dialog.Handle_LoadLookupItemsSuccess(responsePayload);
			})
			.Send();
	},

	Handle_LoadLookupItemsSuccess:function(data)
	{
		this.lookupItems = data;
		this.Show();
	},

	CreateForm:function(preselect)
	{
		var form = FormHelper.createForm('item-lookup');
		var first = null;

		$.each(this.lookupItems, function(idx, itemDef)
		{
			var el = form.AddText('terms_'+itemDef.id, itemDef.field_label)
				.AddClass('input-xxlarge')
				.SetHelpText(itemDef.field_description);

			if(first==null) {
				first = el;
			}
		});

		this.focusElement = first;

		var dialog = this;
		form.Submit(function(values) {
			dialog.Handle_LookupSubmit(values);
		})

		this.form = form;
	},

	Handle_LookupSubmit:function(values)
	{
		var elResults = this.element('results');
		var payload = values;
		var dialog = this;

		elResults.html(application.renderSpinner(t('Lookup running, please wait...')));

		application.createAJAX('LookupItems')
			.SetPayload(payload)
			.Failure(function() {
				elResults.html(
					'<p>'+
						'<strong>' + UI.Text(t('The lookup failed, the server reported an error.')).MakeError() + '</strong>'+
					'</p>' +
					'<p>' +
						t('Please try again, and if the problem persists, contact the %1$s team.', application.appNameShort) +
					'</p>' +
					'<p>'+
						UI.Button(t('Try again'))
							.SetIcon(UI.Icon().Refresh())
							.Click(function() {
								dialog.form.Submit();
							})+
					'</p>'
				);
			})
			.Success(function(responsePayload) {
				dialog.Handle_LookupSuccess(responsePayload);
			})
			.Send();
	},

	Handle_LookupSuccess:function(data)
	{
		var items = [];
		var html = '';
		$.each(this.lookupItems, function(idx, itemDef)
		{
			var id = itemDef.id;
			if(typeof(data[id]) == 'undefined') {
				return;
			}

			var message;
			if(data[id].length==0) {
				message = t('No matching items found.');
			} if(data[id].length==1) {
			message = t('1 item found.');
		} else {
			message = t('%1$s items found.', data[id].length);
		}

			html += ''+
				'<b>' + itemDef.field_label + '</b> - ' + message + '<br>';

			if(data[id].length > 0)
			{
				html += ''+
					'<ul class="unstyled">';
				$.each(data[id], function(idx, result){
					html += '<li><a href="'+result.url+'" target="_blank">'+result.label+'</a></li>';
				});
				html += ''+
					'</ul>';
			}

			html += '<br>';
		});

		this.element('results').html(html);
	}
};

Application_Dialog_LookupItems = Dialog_Basic.extend(Application_Dialog_LookupItems);
