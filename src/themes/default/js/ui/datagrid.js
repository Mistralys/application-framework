/**  
 * Datagrid class: handles the functionality of a datagrid in the
 * UI, from selecting items in the list to hiding/showing columns.
 * 
 * @package UI
 * @subpackage DataGrids
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends Application_BaseRenderable
 */
var UI_Datagrid = 
{
	'ERROR_STRAY_ROW_WHILE_SORTING':19501,
	'ERROR_PRIMARY_KEY_VALUE_MISSING_IN_RECORD':19502,
	'ERROR_PRIMARY_KEY_NAME_REQUIRED':19503,
		
	'id':null,
	'objectName':null,
	'classes':null,
	'title':null,
	'fullViewTitle':null, // set server-side
	'selectionActive':null,
	'columnControls':null,
	'maxColumnsShown':null,
	'started':null,
	'columns':null,
	'columnOffset':null,
	'instances':[],
	'entriesSortable':null,
	'entriesDroppable':null,
	'sortHandler':null,
	'dropHandler':null,
	'entries':null,
	'eventHandlers':null,
	'selectAllActive':false,
	
	// Set serverside
	'BaseURL':null, 
	'TotalPages':null, 
	'PrimaryName':null, 
	'TotalEntries':null,
	'TotalEntriesUnfiltered':null,
	
   /**
    * Constructor. 
    * 
    * @param {String} id
    * @param {String} [objectName] The name of the global variable that holds this instance. Created automatically if not specified.
    */
	init:function(id, objectName)
	{
		this._super();
		
		// compatibility between server-side generated grids and
		// clientside generated ones.
		if(isEmpty(objectName)) {
			objectName = 'csgrid' + nextJSID();
			window[objectName] = this;
		}
		
		this.id = id;
		this.objectName = objectName;
		this.title = null;
		this.fullViewTitle = null;
		this.classes = [];
		this.selectionActive = false;
		this.selectAllActive = false;
		this.columnControls = false;
		this.maxColumnsShown = 0;
		this.started = false;
		this.columns = [];
		this.columnOffset = 0;
		this.entriesSortable = false;
		this.entriesDroppable = false;
		this.sortHandler = null;
		this.dropHandler = null;
		this.entries = [];
		this.eventHandlers = {};
		
		this.BaseURL = null;
		
		this.instances.push(this);
	},
	
   /**
    * Retrieves the name of the variable holding the instance of this datagrid.
    * @return {String}
    */
	GetObjectName:function()
	{
		return this.objectName;
	},
	
   /**
    * Shows a confirmation dialog for the specified datagrid
    * action, with the provided confirmation message. The grid
    * only gets submitted with the selected action if the user
    * confirms it.
    *
    * @param {String} actionName
    * @param {String} confirmMessage
    * @param {String} confirmType
    * @param {String} actionName
    * @param {String} confirmMessage
    * @param {String} confirmType
    */
	ConfirmSubmit:function(actionName, confirmMessage, confirmType)
	{
		var datagrid = this;
		var dialog = application.dialogConfirmation(
			confirmMessage,
			function() {
				datagrid.Submit(actionName);
			}
		);
		
		if(confirmType=='danger') {
			dialog.MakeDangerous();
		}
	},
	
   /**
    * Submits the datagrid's form with the specified action.
    * The actions are determined serverside when multi-actions
    * are enabled.
    *
    */
	Submit:function(actionName)
	{
		this.GetFormElement('action').val(actionName);
		this.GetFormElement().submit();
	},

	GetFormID:function(part)
	{
		var id = 'datagrid-'+this.id;
		if(typeof(part)!=='undefined') {
			id += '-'+part;
		}

		return id;
	},
	
	GetFormElement:function(part)
	{
		return $('#' + this.GetFormID(part));
	},
	
   /**
    * Toggles the items selection: if none are selected,
    * selects all items and vice versa.
    *
    */
	ToggleSelection:function()
	{
		if(this.selectAllActive) {
			return;
		}
		
		this.log('Toggling the entries selection.', 'ui');
		
		if(!this.selectionActive) {
			this.SelectAll();
			this.selectionActive = true;
		} else {
			this.DeselectAll();
			this.selectionActive = false;
		}
	},
	
	ToggleSelectAll:function()
	{
		var gridID = this.GetFormID();
		
		var checkbox = $('#'+gridID+' .selectall-checkbox');
		var link = $('#'+gridID+' .selectall-link');
		var activeEl = $('#'+gridID+' .selectall-active');
		var inactiveEl = $('#'+gridID+' .selectall-inactive');
		
		if(this.selectAllActive) 
		{
			this.selectAllActive = false;
			activeEl.hide();
			inactiveEl.show();
			checkbox.prop('checked', false);
			this.DeselectAll();
		}
		else 
		{
			this.SelectAll();
			activeEl.show();
			inactiveEl.hide();
			checkbox.prop('checked', true);
			this.selectAllActive = true;
			
			if(this.TotalEntries > 1000) {
				application.dialogMessage(
					application.renderAlertWarning(
						UI.Icon().Warning() + ' ' +
						'<b>' + t('You selected a large amount of entries.') + '</b>' 
					)+
					'<p>'+
						t(
							'%1$sYou may proceed%2$s, but keep in mind that processing %3$s entries may take a long time.',
							'<b>',
							'</b>',
							'<b>'+this.TotalEntries+'</b>'
						) + 
						' ' +
						t('The application performance may also be impacted by working on such a large selection.') +
					'</p>'
				);
			}
		}
	},
	
	Handle_SelectionChanged:function(checkboxEl)
	{
		if(this.selectAllActive) {
			this.ToggleSelectAll();
		}
	},
	
	IsSelectAllActive:function()
	{
		return this.selectAllActive;
	},
	
	ChangePerPage:function()
	{
		if(this.IsSelectAllActive()) {
			this.ToggleSelectAll();
		} else {
			this.DeselectAll();
		}
		
		this.GetFormElement().submit();
	},
	
   /**
    * Deselects all entries available in the datagrid. Note:
    * works only if the datagrid has the multiselect function
    * enabled.
    *
    * @return {UI_Datagrid}
    */
	DeselectAll:function()
	{
		if(this.selectAllActive) {
			return;
		}

		this.log('Deselecting all entries.', 'ui');
		
		$('#'+this.GetFormID()+' input[name="datagrid_items[]"]').each(
			function(idx, value) {
				$(this).prop('checked', false);
			}
		);
	},

   /**
    * Selects all entries available in the datagrid. Note:
    * works only if the datagrid has the multiselect function
    * enabled.
    *
    * @return {UI_Datagrid}
    */
	SelectAll:function()
	{
		if(this.selectAllActive) {
			return;
		}

		this.log('Selecting all entries.', 'ui');
		
		$('#'+this.GetFormID()+' input[name="datagrid_items[]"]').each(
			function(idx, value) {
				$(this).prop('checked', true);
			}
		);
		
		return this;
	},
	
   /**
    * Enables the controls with which columns above the specified
    * colun count get hidden and can be navigated with a dedicated
    * column gimmick.
    *
    * @return {UI_Datagrid}
    */
	EnableColumnControls:function(maxColumns)
	{
		this.columnControls = true;
		this.maxColumnsShown = maxColumns;
		return this;
	},
	
   /**
    * Starts the datagrid functions. This is called automatically
    * when the page is ready, and does not need to be called manually.
    *
    */
	Start:function()
	{
		if(this.started) {
			return;
		}
		
		this.started = true;
		
		if(this.IsColumnControlsEnabled()) {
			this.StartColumnControls();
		}
		
		if(this.IsEntriesSortable()) {
			this.StartSortableEntries();
		}
		
		if(this.IsEntriesDroppable()) {
			this.StartDroppableEntries();
		}
	},
	
   /**
    * Checks whether the column navigation controls should be shown.
    * They have to be enabled manually, but even if enabled they will
    * not be shown if the amount of columns to show is higher than the
    * columns that can be navigated.
    *
    * @return {Boolean}
    */
	IsColumnControlsEnabled:function()
	{
		if(!this.columnControls) {
			return false;
		}
		
		if(this.maxColumnsShown >= this.CountHideableColumns()) {
			//return false;
		}
		
		return true;
	},
	
   /**
    * When column controls are enabled, this sets them up by first
    * rendering the required markup, and then doing the initial hiding
    * of columns.
    */
	StartColumnControls:function()
	{
		// avoid showing the controls if there are no entries in the list
		if(this.entries.length==0) {
			this.log('Skipping rending the column controls, the grid has no entries.', 'ui');
			return;
		}
		
		this.RenderColumnControls();

		var max = parseInt(this.GetSetting('maxColumnsShown', this.maxColumnsShown));
		if(!isNaN(max) && max != this.maxColumnsShown) {
			this.maxColumnsShown = max;
			this.Handle_ChangeMaxColumns();
		}
		
		var off = parseInt(this.GetSetting('columnOffset', this.columnOffset));
		if(!isNaN(off) & off != this.columnOffset) {
			this.columnOffset = off;
			this.Handle_ChangeColumnOffset();
		}
		
		this.UpdateColumns();
	},
	
   /**
    * Renders the required markup for the column navigation controls,
    * and injects it into the DOM.
    */
	RenderColumnControls:function()
	{
		var datagrid = this;
		
		var html = ''+
		'<div class="datagrid-hidden-hint" id="'+this.GetFormID('hint')+'" style="display:hidden;" title="'+t('Use the navigation controls to the right to browse through hidden columns.')+'">'+
			application.renderLabelInfo('<b>'+t('Hint').toUpperCase()+'</b>')+' '+
			'<span id="'+this.GetFormID('hint_amount')+'"></span>'+
		'</div>'+
		'<div class="form-inline datagrid-column-controls">'+
			'<div class="btn-group">'+
				UI.Button('')
					.MakeSmall()
					.SetID(this.GetFormID('previous'))
					.SetIcon(UI.Icon().Previous())
					.SetTooltip(t('Navigate one column left'))
					.Click(function() {
						datagrid.Handle_PreviousColumn();
					})+
				UI.Button('')
					.MakeSmall()
					.SetID(this.GetFormID('next'))
					.SetIcon(UI.Icon().Next())
					.SetTooltip(t('Navigate one column right'))
					.Click(function() {
						datagrid.Handle_NextColumn();
					})+
			'</div> '+
			'<label>'+t('Show columns:')+'</label> '+
			'<div class="btn-group">'+
				UI.Button(
					'<span id="'+this.GetFormID('f_maxcols')+'">'+
						this.maxColumnsShown+
					'</span>'+
					'/'+this.CountHideableColumns()
				)
					.MakeSmall()
					.MakeDisabled()
					.Click(function() {
						$(this).blur(); // so the user does not get the impression this is clickable
					})+
				UI.Button('')
					.MakeSmall()
					.SetID(this.GetFormID('plus'))
					.SetIcon(UI.Icon().Plus())
					.SetTooltip(t('Make an additional column visible'))
					.Click(function() {
						datagrid.Handle_IncreaseMaxColumns();
					})+
				UI.Button('')
					.MakeSmall()
					.SetID(this.GetFormID('minus'))
					.SetIcon(UI.Icon().Minus())
					.SetTooltip(t('Hide an additional column'))
					.Click(function() {
						datagrid.Handle_DecreaseMaxColumns();
					})+
			'</div> ';
			if(this.instances.length > 1) {
				html += ''+
				'<div class="btn-group">'+
					'<label class="checkbox" id="'+this.GetFormID('multiset_wrapper')+'" title="'+t('Applies the column settings to all lists on the page.')+'">'+
						'<input type="checkbox" id="'+this.GetFormID('multiset')+'"/> '+
						t('Apply to all')+' '+
					'</label>'+
				'</div>';
			}
			html += ''+
			'<div class="btn-group" style="margin-left:20px;">'+
				UI.Button('')
					.MakeSmall()
					.SetIcon(UI.Icon().Maximize())
					.SetTooltip(t('Opens the list in a new tab with all columns'))
					.Click(function() {
						datagrid.Handle_Maximize();
					});
				// the option to open all lists is only available if there
				// are several lists in the page.
				if(this.instances.length > 1) {
					html += ''+
					'<button class="btn btn-small dropdown-toggle" data-toggle="dropdown">'+
						'<span class="caret"></span>'+
					'</button>'+
					'<ul class="dropdown-menu">'+
						'<li>'+
							'<a href="javascript:void(0);" onclick="grid'+this.id+'.Handle_Maximize()">'+
								t('Open list in a new tab')+
							'</a>'+
						'</li>'+
						'<li>'+
							'<a href="javascript:void(0);" onclick="grid'+this.id+'.Handle_MaximizeAll()">'+
								t('Open all lists in a new tab')+
							'</a>'+
						'</li>'+
					'</ul>';
				}
				html += ''+
			'</div>'+
		'</div>'+
		'<div style="clear:both;"></div>';
		
		$('#datagrid-'+this.id+'-wrapper').prepend(html);
		
		UI.MakeTooltip('#'+this.GetFormID('multiset_wrapper'));
		UI.MakeTooltip('#'+this.GetFormID('hint'));
	},
	
   /**
    * Sets a hidden variable in the data grid's hidden form variables. Replaces or
    * creates variables as needed.
    * 
    * @param {String} name
    * @param {String} value
    * @returns {UI_Datagrid}
    */
	SetHiddenVar:function(name, value)
	{
		var container = $('#datagrid-'+this.id+' .datagrid-hiddenvars');
		
		var found = false;
		$.each(container.find('input[type=hidden]'), function(idx, element) {
			var el = $(element);
			if(el.attr('name') == name) {
				el.val(value);
				found = true;
				return false;
			}
		});
		
		if(!found) {
			container.append('<input type="hidden" name="'+name+'" value="'+value+'">');
		}
		
		return this;
	},
	
   /**
    * Displays the specified datagrids in a new tab. Uses only the body
    * of the tables, and hides action columns to keep only the important
    * parts.
    *
    * @param {Array:UI_Datagrid} datagrids
    */
	Maximize:function(datagrids)
	{
		var grid = this;

		application.showLoader(t('Please wait, generating...'));
		
		// we let the HTML scaffold be built serverside, so we
		// don't have to know the location of the required CSS
		// and the like according to the current theme.
		application.createAJAX('GetGridFullViewHTML')
			.SetPayload({
				'grids':this.GetGrids(datagrids)
			})
			.Success(function(data) {
				console.log(data.html);
				grid.Maximize_DisplayPage(data.html);
			})
			.Send();
	},

	GetGrids(datagrids)
	{
		// to be able to copy the whole table with all hidden cells visible,
		// we temporarily make them all visible, and hide any action cells.
		for(var i=0; i<datagrids.length; i++) {
			var datagrid = datagrids[i];
			$('#'+datagrid.GetFormID('table')+' .role-cell').show();
			$('#'+datagrid.GetFormID('table')+' .role-actions').hide();
		}

		var grids = [];

		for(var i=0; i<datagrids.length; i++)
		{
			var datagrid = datagrids[i];

			grids.push({
				'title':datagrid.GetFullViewTitle(),
				'html':$('#'+datagrid.GetFormID('table')).html()
			});
		}

		// restore hidden columns
		for(var i=0; i<datagrids.length; i++) {
			datagrid = datagrids[i];
			$('#'+datagrid.GetFormID('table')+' .role-actions').show();
			datagrid.UpdateColumns();
		}

		return grids;
	},
	
	Maximize_DisplayPage(html)
	{
		application.hideLoader();

		var w = window.open();
		w.document.open();
		w.document.write(html);
		w.document.close();
	},
	
   /**
    * Retrieves the title for the datagrid when it is in full view mode
    * (which is only available if column controls are enabled).
    *
    * @return {String|NULL}
    */
	GetFullViewTitle:function()
	{
		if(this.fullViewTitle != null) {
			return this.fullViewTitle;
		}
		
		return this.title;
	},
	
   /**
    * Maximizes this datagrid by opening it in a new tab, with only
    * the table body without action columns.
    *
    * @see Maximize
    * @see Handle_MaximizeAll
    */
	Handle_Maximize:function()
	{
		var datagrids = [];
		datagrids.push(this);
		
		this.Maximize(datagrids);
	},
	
   /**
    * Like Handle_Maximize, but brings together all datagrids present
    * in the same page.
    *
    * @see Maximize
    * @see Handle_Maximize
    */
	Handle_MaximizeAll:function()
	{
		this.Maximize(this.instances);
	},
	
   /**
    * Checks whether the "Apply to all" setting is active for the list.
    *
    * @return {Boolean}
    */
	IsApplyToAll:function()
	{
		return this.GetFormElement('multiset').prop('checked');
	},
	
	Handle_PreviousColumn:function()
	{
		this.columnOffset--;
		this.Handle_ChangeColumnOffset();
	},
	
	Handle_NextColumn:function()
	{
		this.columnOffset++;
		this.Handle_ChangeColumnOffset();
	},
	
	Handle_ChangeColumnOffset:function()
	{
		this.log('Column offset has changed, current: '+this.columnOffset+'.');
		
		if(this.columnOffset < 0) {
			this.log('Column offset is smaller than 0, adjusting to 0.');
			this.columnOffset = 0;
		}
		
		var maxOffset = this.GetMaxColumnOffset();
		if(this.columnOffset > maxOffset) {
			this.log('Column offset is higher than the max offset, adjusting to '+maxOffset);
			this.columnOffset = maxOffset;
		}
		
		this.log('Set offset to '+this.columnOffset);
		
		this.UpdateColumns();
		
		if(this.IsApplyToAll()) {
			for(var i=0; i<this.instances.length; i++) {
				var instance = this.instances[i];
				if(instance.id != this.id) {
					instance.ApplySettings(this.maxColumnsShown, this.columnOffset);
				}
			}
		}
	},
	
   /**
    * Determines the maximum navigation offset value for navigating
    * columns.
    *
    * @return {Integer}
    */
	GetMaxColumnOffset:function()
	{
		var total = this.CountHideableColumns();
		var maxOffset = total-this.maxColumnsShown;
		if(maxOffset < 0) {
			maxOffset = 0;
		}
		
		return maxOffset;
	},
	
   /**
    * Counts the amount of "cell" columns that can be navigated through.
    *
    * @return {Integer}
    */
	CountHideableColumns:function()
	{
		var total = 0;
		for(var i=0; i<this.columns.length; i++) {
			var column = this.columns[i];
			if(column.IsHideable()) {
				total++;
			}
		}
		
		return total;
	},
	
	Handle_IncreaseMaxColumns:function()
	{
		this.maxColumnsShown++;
		this.Handle_ChangeMaxColumns();
	},
	
	Handle_DecreaseMaxColumns:function()
	{
		this.maxColumnsShown--;
		this.Handle_ChangeMaxColumns();
	},
	
	Handle_ChangeMaxColumns:function()
	{
		var el = this.GetFormElement('f_maxcols');
		var max = this.maxColumnsShown;
		if(max < 1) {
			max = 1;
		}
		
		if(max > this.CountHideableColumns()) {
			max = this.CountHideableColumns();
		}
		
		el.text(max);
		
		this.maxColumnsShown = max;
		
		this.UpdateColumns();
		
		// if the user selected the option to apply the settings to all
		// datagrids present in the page, we go through the instances 
		// collection and tell each to apply these settings.
		if(this.IsApplyToAll()) {
			for(var i=0; i<this.instances.length; i++) {
				var instance = this.instances[i];
				if(instance.id != this.id) {
					instance.ApplySettings(this.maxColumnsShown, this.columnOffset);
				}
			}
		}
	},
	
	SaveSettings:function()
	{
		this.SetSetting('maxColumnsShown', this.maxColumnsShown);
		this.SetSetting('columnOffset', this.columnOffset);
	},
	
	SetSetting:function(name, value)
	{
		application.setPref('datagrid-'+this.id+'-'+name, value);
	},
	
	GetSetting:function(name, defaultValue)
	{
		return application.getPref('datagrid-'+this.id+'-'+name, defaultValue);
	},
	
   /**
    * Used when the apply to all setting is active, to apply the same
    * settings to all datagrids in the page.
    * 
    * @param {Integer} maxColumnsShown
    * @param {Integer} columnOffset
    * @see Handle_ChangeMaxColumns()
    * @see Handle_ChangeColumnOffset()
    */
	ApplySettings:function(maxColumnsShown, columnOffset)
	{
		this.maxColumnsShown = maxColumnsShown;
		this.columnOffset = columnOffset;
		this.Handle_ChangeMaxColumns();
	},
	
   /**
    * Updates the column display according to the current settings:
    * hides and shows columns as needed.
    *
    */
	UpdateColumns:function()
	{
		this.log('Updating the visible columns.', 'ui');
		
		// the start and end offset of columns to show is determined
		// by the position the user chose to show.
		var startOffset = this.columnOffset;
		
		// the user may change the amount of columns shown when he/she already 
		// navigated to the end of the columns, so we check here if the offset
		// is still within bounds.
		if(startOffset > this.GetMaxColumnOffset()) {
			startOffset = this.GetMaxColumnOffset();
			this.columnOffset = startOffset;
			this.log('Start offset was too high, adjusting to '+this.GetMaxColumnOffset(), 'ui');
		}
		
		var endOffset = startOffset+this.maxColumnsShown;
		
		var position = 0;
		for(var i=0; i<this.columns.length; i++) {
			var column = this.columns[i];
			
			// only columns with the "cell" role are considered hideable.
			// this is because we don't want to hide action columns, for ex.
			if(!column.IsHideable()) {
				continue;
			}
			
			// hide all columns that are not within the span of
			// columns we want to show, and show all others.
			if(position < startOffset) {
				console.log('Position '+position+': Hidden');
				column.Hide();
			} else if(position >= endOffset) {
				console.log('Position '+position+': Hidden');
				column.Hide();
			} else {
				console.log('Position '+position+': Shown');
				column.Show();
			}
			
			position++;
		}
		
		if(this.columnOffset >= this.GetMaxColumnOffset()) {
			this.GetFormElement('next').addClass('disabled');
		} else {
			this.GetFormElement('next').removeClass('disabled');
		}
		
		if(this.columnOffset == 0) {
			this.GetFormElement('previous').addClass('disabled');
		} else {
			this.GetFormElement('previous').removeClass('disabled');
		}
		
		var hiddenCount = this.CountHiddenColumns();
		if(hiddenCount == 1) {
			this.GetFormElement('hint').show();
			this.GetFormElement('hint_amount').text(t('1 column is hidden.'));
		} else if(hiddenCount > 1) {
			this.GetFormElement('hint').show();
			this.GetFormElement('hint_amount').text(t('%1$s columns are hidden.', hiddenCount));
		} else {
			this.GetFormElement('hint').hide();
		}
		
		if(this.maxColumnsShown==this.CountHideableColumns()) {
			this.GetFormElement('plus').addClass('disabled');
		} else {
			this.GetFormElement('plus').removeClass('disabled');
		}
		
		if(this.maxColumnsShown==1) {
			this.GetFormElement('minus').addClass('disabled');
		} else {
			this.GetFormElement('minus').removeClass('disabled');
		}

		this.SaveSettings();
	},
	
	CountHiddenColumns:function()
	{
		var hidden = 0;
		for(var i=0; i<this.columns.length; i++) {
			var column = this.columns[i];
			if(column.IsHidden()) {
				hidden++;
			}
		}
		
		return hidden;
	},
	
   /**
    * Adds/registers a column with the data grid. This is done 
    * automatically serverside so the datagrid knows which columns
    * are available.
    *
    * @param {String} key The name of the data key holding the values
    * @param {String} [title=''] The title of the column 
    * @param {String} [id] The unique ID of the column
    * @param {String} [type='Regular'] The column type, e.g. "Regular" or "MultiSelect"
    * @param {Integer} [number] The column index, starting at 1
    * @param {String} [role='cell'] The column's role, e.g. "cell", "actions", etc.
    * @return {UI_Datagrid_Column}
    */
	AddColumn:function(key, title, id, type, number, role)
	{
		if(isEmpty(id)) { id = nextJSID(); }
		if(isEmpty(type)) { type = 'Regular'; }
		if(isEmpty(role)) { role = 'cell'; }
		if(isEmpty(number)) { number = this.entries.length; }
		if(isEmpty(title)) { title = ''; }
		
		var column = new UI_Datagrid_Column(this, key, title, id, type, number*1, role);
		this.columns.push(column);
		return column;
	},
	
   /**
    * Retrieves an indexed array containing all columns in
    * the grid, ordered in the order they are shown in the UI.
    * 
    * @return {Array}
    */
	GetColumns:function()
	{
		return this.columns;
	},
	
	GetColumnByName:function()
	{
		var found = null;
		$.each(this.columns, function(idx, column) {
			if(column.GetName() == name) {
				found = column;
				return false;
			}
		});
		
		return found;
	},
	
	SetTitle:function(title)
	{
		this.title = title;
	},

	/**
	 * @param Event e
	 * @returns {boolean}
	 */
	Handle_Submit:function(e)
	{
		return true;
	},

	/**
	 * @param Event e
	 */
	CheckJumpToCustom:function (e)
	{
		if(e.keyCode !== KeyCodes.Enter) {
			return true;
		}

		e.preventDefault();
		e.stopPropagation();
		this.JumpToCustomPage();

		return false;
	},

   /**
    * When the advanced page navigation is shown, the user can enter a
    * custom page number to jump to. This takes that number and redirects
    * to the target page.
    * 
    */
	JumpToCustomPage:function()
	{
		var pageNr = this.GetFormElement('custompage').val();
		if(pageNr <= 0) {
			pageNr = 1;
		}
		
		if(pageNr > this.TotalPages) {
			pageNr = this.TotalPages;
		}
		
		application.redirect(this.BaseURL.replace('_PGNR_', pageNr));
	},
	
   /**
    * Tells the data grid that its entries (rows) should be sortable.
    * This is called automatically serverside.
    *  
    * @param {Object} handlerObj The object that will handle sorting events. Must extend the UI_DataGrid_Sortable class.
    * @return boolean Whether the feature could be activated
    */
	MakeSortable:function(handlerObj)
	{
		var valid = handlerObj instanceof UI_DataGrid_Sortable;
		if(!valid) {
			this.log('Cannot make the entries sortable: Specified handler is not an instance of UI_DataGrid_Sortable.', 'error');
			return false;
		}
		
		this.log('Enabled sortable entries.', 'ui');
		
		this.entriesSortable = true;
		this.sortHandler = handlerObj;
		
		return true;
	},
	
	MakeDroppable:function(handlerObj)
	{
		var valid = handlerObj instanceof UI_DataGrid_Droppable;
		if(!valid) {
			this.log('Cannot make the entries droppable: Specified handler is not an instance of UI_DataGrid_Droppable.', 'error');
			return false;
		}
		
		this.log('Enabled droppable entries.', 'ui');
		
		this.entriesDroppable = true;
		this.dropHandler = handlerObj;
		
		return true;
	},
	
	GetPrimaryName:function()
	{
		return this.PrimaryName;
	},

	GetDropHandler:function()
	{
		return this.dropHandler;
	},
	
   /**
    * Retrieves the entries sorting handler object, as
    * set with the {@link MakeSortable()} method. 
    * 
    * @return {UI_DataGrid_Sortable}
    */
	GetSortHandler:function()
	{
		return this.sortHandler;
	},
	
   /**
    * Whether the entries of the grid are sortable.
    * @return boolean
    */
	IsEntriesSortable:function()
	{
		return this.entriesSortable;
	},
	
	IsEntriesDroppable:function()
	{
		return this.entriesDroppable;
	},
	
	StartSortableEntries:function()
	{
		var conf = new UI_DataGrid_Sortable_Configuration(this);
		
		this.GetBodyElement().sortable(conf);
	},
	
	StartDroppableEntries:function()
	{
		var conf = new UI_DataGrid_Droppable_Configuration(this);
		
		this.GetBodyElement().droppable(conf);
		$('#'+this.GetFormID('dropper')).droppable(conf);
	},
	
	GetBodyElement:function()
	{
		return $(this.GetBodySelector());
	},
	
	GetBodySelector:function()
	{
		return '#'+this.GetFormID('table') + ' TBODY';
	},
	
	ShowTable:function()
	{
		if(this.GetBodyElement().children().length > 0) {
			this.GetFormElement('table').show();
			this.GetFormElement('empty').hide();
			this.GetFormElement('dropper').hide();
		} else {
			this.GetFormElement('table').show();
			this.GetFormElement('empty').hide();
			this.GetFormElement('dropper').show();
		}
	},
	
   /**
    * Appends an entry at the end of the list. Note that this does
    * not handle saving the new entry: this logic has to be implemented
    * separately.
    * 
    * @param {Object} cellData The data for all columns in the row. Requires all keys to be set.
    * @return {UI_DataGrid_Entry}
    */
	AppendEntry:function(cellData)
	{
		var entry = this.CreateNewEntry(cellData);
		if(!entry) {
			return false;
		}
		
		if(this.IsRendered()) {
			var html = entry.Render();
			this.GetBodyElement().append(html);
	
			// display the table since we may have started from an empty table
			this.ShowTable();
		}

		this.Handle_EntriesModified();
		
		return entry;
	},
	
   /**
    * Removes an existing entry from the grid.
    * 
    * @param {UI_Datagrid_Entry} targetEntry
    */
	RemoveEntry:function(targetEntry)
	{
		targetEntry.Remove(); // remove the row from the DOM
		
		var keep = [];
		$.each(this.entries, function(idx, entry) {
			if(entry.GetID() != targetEntry.GetID()) {
				keep.push(entry);
			}
		});
		
		this.entries = keep;

		this.Handle_EntriesModified();
	},
	
	PrependEntry:function(cellData)
	{
		var entry = this.CreateNewEntry(cellData);
		if(!entry) {
			return false;
		}
		
		if(this.IsRendered()) {
			var html = entry.Render();
			this.GetBodyElement().prepend(html);
	
			// display the table since we may have started from an empty table
			this.ShowTable();
		}
		
		this.Handle_EntriesModified();
		
		return entry;
	},
	
	InsertEntryBefore:function(primary, cellData)
	{
		var entry = this.CreateNewEntry(cellData);
		if(!entry) {
			return false;
		}
		
		if(this.IsRendered()) {
			var html = entry.Render();
			
			var el = $('#'+this.GetFormID('table') + ' tr[data-refid="'+primary+'"]');
			el.before(html);
	
			// display the table since we may have started from an empty table
			this.ShowTable();
		}
		
		this.Handle_EntriesModified();
		
		return entry;
	},
	
	Handle_EntriesModified:function()
	{
		this.RefreshCount();
		this.Sort();
	},
	
	RegisterEntry:function(cellData)
	{
		var entry = new UI_DataGrid_Entry(this, cellData);
		this.entries.push(entry);
		
		return entry;
	},
	
   /**
    * Creates and adds a new datagrid entry.
    * 
    * Note: if a primary key has been set, the data
    * will be verified to include a value for the key.
    * 
    * @param {Object} cellData
    * @returns {UI_DataGrid_Entry|false}
    */
	CreateNewEntry:function(cellData)
	{
		if(!this.ValidateCellData(cellData)) {
			return false;
		}
		
		return this.RegisterEntry(cellData);
	},
	
	ValidateCellData:function(cellData)
	{
		this.log('Validating cell data:', 'data');
		this.log(cellData, 'data');
		
		// first off, check whether the primary key value is present
		if(this.HasPrimary() && typeof(cellData[this.PrimaryName])=='undefined') {
			throw new ApplicationException(
				'Missing primary key in record',
				'The primary key ['+this.PrimaryName+'] is missing.',
				this.ERROR_PRIMARY_KEY_VALUE_MISSING_IN_RECORD
			);
		}
		
		// and now go through all data columns to check if all values are present
		var grid = this;
		var valid = true;
		$.each(this.columns, function(idx, column) {
			if(!column.IsCell()) {
				return;
			}
			
			if(typeof(cellData[column.DataKey])=='undefined') {
				grid.log('INVALID: The data key ['+column.DataKey+'] is missing.', 'error');
				valid = false;
			}
		});
		
		return valid;
	},
	
	GetEntry:function(primaryValue)
	{
		for(var i=0; i<this.entries.length; i++) {
			var entry = this.entries[i];
			if(entry.GetPrimary()==primaryValue) {
				return entry;
			}
		}
		
		return null;
	},
	
   /**
    * Retrieves all entries available in the grid.
    * 
    * @return {UI_Datagrid_Entry[]}
    */
	GetEntries:function()
	{
		return this.entries;
	},
	
   /**
    * Sets the name of the primary key in the data sets.
    * @param {String} name
    * @returns {UI_Datagrid}
    */
	SetPrimaryName:function(name)
	{
		this.PrimaryName = name;
		return this;
	},
	
   /**
    * Checks whether the datagrid has a primary key set.
    * @returns {Boolean}
    */
	HasPrimary:function()
	{
		if(this.PrimaryName!=null) {
			return true;
		}
		
		return false;
	},
	
	GetSelectedEntries:function()
	{
		var grid = this;
		var entries = [];
		var checkboxes = $('#'+this.GetFormID('table')+' input[name="datagrid_items[]"]:checked');
		$.each(checkboxes, function(idx, checkbox) {
			var entry = grid.GetEntry($(checkbox).attr('value'));
			if(entry != null) {
				entries.push(entry);
			}
		});
		
		return entries;
	},
	
   /**
    * Retrieves the primary keys of all currently selected list entries.
    * 
    * @return string[]
    */
	GetSelectedPrimaries:function()
	{
		var entries = this.GetSelectedEntries();
		var ids = [];
		$.each(entries, function(idx, entry) {
			ids.push(entry.GetPrimary());
		});
		
		return ids;
	},
	
	SetOrderBy:function(columnName, orderDir)
	{
		this.GetFormElement('orderby').val(columnName);
		this.GetFormElement('orderdir').val(orderDir);
		this.GetFormElement().submit();
	},
	
	log:function(message, category)
	{
		application.log(
			'DataGrid ['+this.id+']',
			message,
			category
		);
	},
	
   /**
    * Renders the datagrid to HTML. Note that this is only
    * used clientside. Serverside the markup is generated
    * entirely there.
    * 
    * @return {String}
    */
	_Render:function()
	{
		if(this.columns.length == 0) {
			application.log('DataGrid', 'No columns added', 'error');
			return '';
		}
		
		this.AddClass('table');
		this.SetStyle('display', 'table');
		this.SetAttribute('id', 'datagrid-'+this.id+'-table');
		
		var html = ''+
		'<div class="datagrid" id="datagrid-'+this.id+'-wrapper">'+
			'<div id="datagrid-'+this.id+'-empty" style="display:none">'+
				application.renderAlertInfo(UI.Icon().Information() + ' ' + t('No elements found.'), false)+
			'</div>'+
			'<table'+this.RenderAttributes()+'>'+
				'<thead>'+
					'<tr class="column-headers">';
						$.each(this.columns, function(idx, column) {
							html += column.RenderHeader();
						});
						html += ''+
					'</tr>'+
				'</thead>'+
				'<tfoot>'+
					'<tr class="actions">'+
						'<td colspan="'+this.columns.length+'">'+
							'<div class="pull-right">'+
								'<span class="muted" id="'+this.elementID('entries_count')+'">'+t('%1$s entries total.', this.entries.length)+'</span>'+
							'</div>'+
						'</td>'+
					'</tr>'+
				'</tfoot>'+
				'<tbody>';
					$.each(this.entries, function(idx, entry) {
						html += entry.Render();
					});
					html += ''+ 
				'</tbody>'+
			'</table>'+
		'</div>';
		
		return html;
	},
	
	_PostRender:function()
	{
		if(this.entries.length == 0) {
			$('#datagrid-'+this.id+'-empty').show();
			$('#datagrid-'+this.id+'-table').hide();
		} else {
			$('#datagrid-'+this.id+'-empty').hide();
			$('#datagrid-'+this.id+'-table').show();
		}
	},
	
	EnableCompactMode:function()
	{
		return this.AddClass('table-condensed');
	},
	
	EnableHover:function()
	{
		return this.AddClass('table-hover');
	},
	
   /**
    * @protected
    */
	_GetTypeName:function()
	{
		return 'DataGrid';
	},
	
   /**
    * @protected
    */
	Handle_RowClicked:function(entry, column)
	{
		return this.TriggerEvent('RowClicked', entry, column);
	},
	
   /**
    * Adds an event handling function for the RowClicked event:
    * whenever the user clicks anywhere in a row of the grid. 
    * 
    * NOTE: Nested clickable elements must prevent event propagation
    * to avoid triggering this as needed. Action columns are
    * automatically ignored, editable columns also.
    * 
    * The handler gets two parameters:
    * 
    * - The UI_DataGrid_Entry instance
    * - The UI_DataGrid_Column instance
    * 
    * <code>this</code> points to the UI_DataGrid object instance.
    * 
    * @param {Function} handler
    * @return {UI_Datagrid}
    */
	RowClicked:function(handler)
	{
		return this.AddEventHandler('RowClicked', handler);
	},
	
   /**
    * @protected
    */
	AddEventHandler:function(eventName, handler)
	{
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			this.eventHandlers[eventName] = [];
		}
		
		this.eventHandlers[eventName].push(handler);
		return this;
	},
	
   /**
    * Checks whether the grid has an event handler set for the specified type.
    * 
    * @protected 
    * @param {String} name
    * @returns {Boolean}
    */
	HasEventHandler:function(eventName)
	{
		return typeof(this.eventHandlers[eventName]) != 'undefined';
	},
	
	TriggerEvent:function()
	{
		var eventName = arguments[0];
		if(typeof(this.eventHandlers[eventName]) == 'undefined') {
			return;
		}
		
		var args = Array.prototype.slice.call(arguments);
		args.shift(); // remove the event name
		
		var grid = this;
		$.each(this.eventHandlers[eventName], function(idx, handler) {
			handler.apply(grid, args);
		}); 
	},
	
	DialogActionNotSelectAllEnabled:function()
	{
		application.dialogMessage(
			application.renderAlertInfo(
				UI.Icon().Information() + ' ' +
				'<b>' + t('This operation cannot be applied to all entries in the list.') + '</b>'
			)+' '+
			'<p>'+
				t('Please select only items in the current page of the list.') + ' ' +
				t('If need be, increase the amount of items per page.') + ' ' + 
				t('The upper limit for this operation is the maximum items per page you can choose for the list.')+
			'</p>'
		);
	},
	
   /**
    * Triggers the grid's clientside sorting, or sets a callback 
    * function to use for the sorting.
    * 
    * Note: This only works with clientside grids.
    * 
    * @param {Function} handler
    * @return {UI_DataGrid}
    */
	Sort:function(handler)
	{
		if(!isEmpty(handler)) {
			return this.AddEventHandler('sort', handler);
		}
		
		if(!this.HasEventHandler('sort')) {
			return;
		}

		this.log('Sorting ['+this.entries.length+'] entries');
		
		// start by re-ordering the internal entries collection 
		var entries = this.entries;
		$.each(this.eventHandlers['sort'], function(idx, handler) {
			entries.sort(handler);
		});

		// reorder the table rows
		if(this.IsRendered()) 
		{
			var previous = null;
			$.each(entries, function(idx, entry) {
				if(previous != null) {
					entry.GetRowElement().insertAfter(previous.GetRowElement());
				}
				
				previous = entry;
			});
		}
			
		return this;
	},
	
	RequirePrimary:function(operationLabel)
	{
		if(this.HasPrimary()) {
			return;
		}

		throw new ApplicationException(
			'No primary key defined',
			'A primary key needs to be set for operation ['+operationLabel+'].',
			this.ERROR_PRIMARY_KEY_NAME_REQUIRED
		);
	},
	
   /**
    * Retrieves an entry by its DOM row.
    * 
    * @protected
    * @return {UI_Datagrid_Entry|NULL}
    */
	GetEntryByRow:function(row)
	{
		this.RequirePrimary('Retrieve entry instance by row');
		
		var result = null;
		row = $(row);
		
		$.each(this.entries, function(idx, entry) {
			if(entry.IsRowElement(row)) {
				result = entry;
				return false;
			}
		});

		if(result != null) {
			return result;
		}
			
		throw new ApplicationException(
			'Stray row found', 
			'The row element ['+el.attr('id')+'] does not match any entry instance.',
			this.ERROR_STRAY_ROW_WHILE_SORTING
		);
	},
	
   /**
    * Refreshes the amount of entries shown in the footer.
    * @protected
    */
	RefreshCount:function()
	{
		this.element('entries_count').html(t('%1$s entries total.', this.entries.length));
	}
};

UI_Datagrid = Application_RenderableHTML.extend(UI_Datagrid);
