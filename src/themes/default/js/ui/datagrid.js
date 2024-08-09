"use strict";

/**
 * Datagrid class: handles the functionality of a datagrid in the
 * UI, from selecting items in the list to hiding/showing columns.
 * 
 * @package User Interface
 * @subpackage DataGrids
 * @extends UI_Renderable_HTML
 */
class UI_Datagrid extends UI_Renderable_HTML
{
	static instances = [];

   /**
    * @param {String} id
    * @param {String} [objectName] The name of the global variable that holds this instance. Created automatically if not specified.
    */
	constructor(id, objectName)
	{
		super();

		this.ERROR_STRAY_ROW_WHILE_SORTING = 19501;
		this.ERROR_PRIMARY_KEY_VALUE_MISSING_IN_RECORD = 19502;
		this.ERROR_PRIMARY_KEY_NAME_REQUIRED = 19503;

		// compatibility between server-side generated grids and
		// clientside generated ones.
		if(isEmpty(objectName)) {
			objectName = 'csgrid' + nextJSID();
			window[objectName] = this;
		}

		this.id = id;
		this.objectName = objectName;
		this.title = null;
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

		/**
		 * @type {UI_DataGrid_Entry[]}
		 */
		this.entries = [];

		this.eventHandlers = {};

		// Set server-side
		this.REQUEST_PARAM_CONFIGURE_GRID = '';
		this.fullViewTitle = null;
		this.BaseURL = '';
		this.RefreshURL = '';
		this.ConfiguratorSectionID = '';
		this.TotalPages = 0;
		this.CurrentPage = 0;
		this.PrimaryName = '';
		this.TotalEntries = 0;
		this.TotalEntriesUnfiltered = 0;
		this.TotalUserHiddenColumns = 0;

		UI_Datagrid.instances.push(this);
	}
	
   /**
    * Retrieves the name of the variable holding the instance of this datagrid.
    * @return {String}
    */
	GetObjectName()
	{
		return this.objectName;
	}
	
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
	ConfirmSubmit(actionName, confirmMessage, confirmType)
	{
		const datagrid = this;
		const dialog = application.dialogConfirmation(
			confirmMessage,
			function () {
				datagrid.Submit(actionName);
			}
		);

		if(confirmType === 'danger') {
			dialog.MakeDangerous();
		}
	}
	
   /**
    * Submits the datagrid's form with the specified action.
    * The actions are determined serverside when multi-actions
    * are enabled.
    *
    */
	Submit(actionName)
	{
		this.GetFormElement('action').val(actionName);
		this.GetFormElement().submit();
	}

	/**
	 * @param {String|null} part
	 * @return {string}
	 */
	GetFormID(part = null)
	{
		let id = 'datagrid-' + this.id;

		if(typeof part !=='undefined' && part !== null) {
			id += '-'+part;
		}

		return id;
	}

	/**
	 * @param {String|null} part
	 * @return {jQuery}
	 */
	GetFormElement(part = null)
	{
		return $('#' + this.GetFormID(part));
	}
	
   /**
    * Toggles the item selection: if none are selected,
    * selects all items and vice versa.
    *
    */
	ToggleSelection()
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
	}
	
	ToggleSelectAll()
	{
		const gridID = this.GetFormID();

		const checkbox = $('#' + gridID + ' .selectall-checkbox');
		const activeEl = $('#' + gridID + ' .selectall-active');
		const inactiveEl = $('#' + gridID + ' .selectall-inactive');

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
	}

	/**
	 * @param {jQuery} checkboxEl
	 */
	Handle_SelectionChanged(checkboxEl)
	{
		if(this.selectAllActive) {
			this.ToggleSelectAll();
		}
	}

	/**
	 * @return {boolean}
	 */
	IsSelectAllActive()
	{
		return this.selectAllActive;
	}
	
	ChangePerPage()
	{
		if(this.IsSelectAllActive()) {
			this.ToggleSelectAll();
		} else {
			this.DeselectAll();
		}
		
		this.GetFormElement().submit();
	}
	
   /**
    * Deselects all entries available in the datagrid. Note:
    * works only if the datagrid has the multiselect function
    * enabled.
    *
    * @return {this}
    */
	DeselectAll()
	{
		if(this.selectAllActive) {
			return this;
		}

		this.log('Deselecting all entries.', 'ui');
		
		$('#'+this.GetFormID()+' input[name="datagrid_items[]"]').each(
			function() {
				$(this).prop('checked', false);
			}
		);

		return this;
	}

   /**
    * Selects all entries available in the datagrid. Note:
    * works only if the datagrid has the multiselect function
    * enabled.
    *
    * @return {this}
    */
	SelectAll()
	{
		if(this.selectAllActive) {
			return this;
		}

		this.log('Selecting all entries.', 'ui');
		
		$('#'+this.GetFormID()+' input[name="datagrid_items[]"]').each(
			function() {
				$(this).prop('checked', true);
			}
		);
		
		return this;
	}
	
   /**
    * Enables the controls with which columns above the specified
    * colun count get hidden and can be navigated with a dedicated
    * column gimmick.
    *
    * @return {this}
    */
	EnableColumnControls(maxColumns)
	{
		this.columnControls = true;
		this.maxColumnsShown = maxColumns;
		return this;
	}
	
   /**
    * Starts the datagrid functions. This is called automatically
    * when the page is ready, and does not need to be called manually.
    *
	* @return {this}
    */
	Start()
	{
		if(this.started) {
			return this;
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

		return this;
	}
	
   /**
    * Checks whether the column navigation controls should be shown.
    * They have to be enabled manually, but even if enabled, they will
    * not be shown if the number of columns to show is higher than the
    * columns that can be navigated.
    *
    * @return {Boolean}
    */
	IsColumnControlsEnabled()
	{
		return this.columnControls;
	}
	
   /**
    * When column controls are enabled, this sets them up by first
    * rendering the required markup, and then doing the initial hiding
    * of columns.
    */
	StartColumnControls()
	{
		// avoid showing the controls if there are no entries in the list
		if(this.entries.length === 0) {
			this.log('Skipping rending the column controls, the grid has no entries.', 'ui');
			return;
		}
		
		this.RenderColumnControls();

		const max = parseInt(this.GetSetting('maxColumnsShown', this.maxColumnsShown));
		if(!isNaN(max) && max !== this.maxColumnsShown) {
			this.maxColumnsShown = max;
			this.Handle_ChangeMaxColumns();
		}

		const off = parseInt(this.GetSetting('columnOffset', this.columnOffset));
		if(!isNaN(off) && off !== this.columnOffset) {
			this.columnOffset = off;
			this.Handle_ChangeColumnOffset();
		}
		
		this.UpdateColumns();
	}
	
   /**
    * Renders the required markup for the column navigation controls,
    * and injects it into the DOM.
    */
	RenderColumnControls()
	{
		const datagrid = this;

		let html = ''+
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
			if(UI_Datagrid.instances.length > 1) {
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
					.SetIcon(UI.Icon().Settings())
					.SetTooltip(t('Opens the list configuration screen to choose and reorder columns.'))
					.Click(function() {
						datagrid.Handle_Configure();
					})+
				UI.Button('')
					.MakeSmall()
					.SetIcon(UI.Icon().Maximize())
					.SetTooltip(t('Opens the list in a new tab with all columns'))
					.Click(function() {
						datagrid.Handle_Maximize();
					});
				// the option to open all lists is only available if there
				// are several lists in the page.
				if(UI_Datagrid.instances.length > 1) {
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
	}
	
   /**
    * Sets a hidden variable in the data grid's hidden form variables. Replaces or
    * creates variables as needed.
    * 
    * @param {String} name
    * @param {String} value
    * @returns {this}
    */
	SetHiddenVar(name, value)
	{
		const container = $('#datagrid-' + this.id + ' .datagrid-hiddenvars');

		let found = false;
		$.each(container.find('input[type=hidden]'), function(idx, element) {
			let el = $(element);
			if(el.attr('name') === name) {
				el.val(value);
				found = true;
				return false;
			}
		});
		
		if(!found) {
			container.append('<input type="hidden" name="'+name+'" value="'+value+'">');
		}
		
		return this;
	}
	
   /**
    * Displays the specified datagrids in a new tab. Uses only the body
    * of the tables, and hides action columns to keep only the important
    * parts.
    *
    * @param {UI_Datagrid[]} datagrids
    */
	Maximize(datagrids)
	{
		const grid = this;

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
	}

	GetGrids(datagrids)
	{
		let i;
		let datagrid;

		// to be able to copy the whole table with all hidden cells visible,
		// we temporarily make them all visible, and hide any action cells.
		for(i=0; i<datagrids.length; i++) {
			datagrid = datagrids[i];
			$('#'+datagrid.GetFormID('table')+' .role-cell').show();
			$('#'+datagrid.GetFormID('table')+' .role-actions').hide();
		}

		const grids = [];

		for(i=0; i<datagrids.length; i++)
		{
			datagrid = datagrids[i];

			grids.push({
				'title':datagrid.GetFullViewTitle(),
				'html':$('#'+datagrid.GetFormID('table')).html()
			});
		}

		// restore hidden columns
		for(i=0; i<datagrids.length; i++) {
			datagrid = datagrids[i];
			$('#'+datagrid.GetFormID('table')+' .role-actions').show();
			datagrid.UpdateColumns();
		}

		return grids;
	}

	/**
	 * @param {String} html
	 */
	Maximize_DisplayPage(html)
	{
		application.hideLoader();

		const w = window.open();
		w.document.open();
		w.document.write(html);
		w.document.close();
	}
	
   /**
    * Retrieves the title for the datagrid when it is in full view mode
    * (which is only available if column controls are enabled).
    *
    * @return {String|null}
    */
	GetFullViewTitle()
	{
		if(this.fullViewTitle !== null) {
			return this.fullViewTitle;
		}
		
		return this.title;
	}
	
   /**
    * Maximizes this datagrid by opening it in a new tab, with only
    * the table body without action columns.
    *
    * @see Maximize
    * @see Handle_MaximizeAll
    */
	Handle_Maximize()
	{
		const datagrids = [];

		datagrids.push(this);
		
		this.Maximize(datagrids);
	}

	Handle_Configure()
	{
		application.redirect(sprintf(
			'%s&%s=yes#%s',
			this.RefreshURL,
			this.REQUEST_PARAM_CONFIGURE_GRID,
			this.ConfiguratorSectionID
		));
	}
	
   /**
    * Like Handle_Maximize, but brings together all datagrids present
    * in the same page.
    *
    * @see Maximize
    * @see Handle_Maximize
    */
	Handle_MaximizeAll()
	{
		this.Maximize(UI_Datagrid.instances);
	}
	
   /**
    * Checks whether the "Apply to all" setting is active for the list.
    *
    * @return {Boolean}
    */
	IsApplyToAll()
	{
		return this.GetFormElement('multiset').prop('checked');
	}
	
	Handle_PreviousColumn()
	{
		this.columnOffset--;
		this.Handle_ChangeColumnOffset();
	}
	
	Handle_NextColumn()
	{
		this.columnOffset++;
		this.Handle_ChangeColumnOffset();
	}
	
	Handle_ChangeColumnOffset()
	{
		this.log('Column offset has changed, current: '+this.columnOffset+'.');
		
		if(this.columnOffset < 0) {
			this.log('Column offset is smaller than 0, adjusting to 0.');
			this.columnOffset = 0;
		}

		const maxOffset = this.GetMaxColumnOffset();
		if(this.columnOffset > maxOffset) {
			this.log('Column offset is higher than the max offset, adjusting to '+maxOffset);
			this.columnOffset = maxOffset;
		}
		
		this.log('Set offset to '+this.columnOffset);
		
		this.UpdateColumns();
		
		if(!this.IsApplyToAll()) {
			return;
		}

		for(let i=0; i < UI_Datagrid.instances.length; i++) {
			let instance = UI_Datagrid.instances[i];
			if(instance.id !== this.id) {
				instance.ApplySettings(this.maxColumnsShown, this.columnOffset);
			}
		}
	}
	
   /**
    * Determines the maximum navigation offset value for navigating
    * columns.
    *
    * @return {Number}
    */
	GetMaxColumnOffset()
	{
		const total = this.CountHideableColumns();
		let maxOffset = total - this.maxColumnsShown;
		if(maxOffset < 0) {
			maxOffset = 0;
		}
		
		return maxOffset;
	}
	
   /**
    * Counts the number of "cell" columns that can be navigated through.
    *
    * @return {Integer}
    */
	CountHideableColumns()
	{
		let total = 0;
		for(let i=0; i<this.columns.length; i++) {
			let column = this.columns[i];
			if(column.IsHideable()) {
				total++;
			}
		}
		
		return total;
	}
	
	Handle_IncreaseMaxColumns()
	{
		this.maxColumnsShown++;
		this.Handle_ChangeMaxColumns();
	}
	
	Handle_DecreaseMaxColumns()
	{
		this.maxColumnsShown--;
		this.Handle_ChangeMaxColumns();
	}
	
	Handle_ChangeMaxColumns()
	{
		const el = this.GetFormElement('f_maxcols');
		let max = this.maxColumnsShown;
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
		// datagrids present in the page, we go through the instance
		// collection and tell each to apply these settings.
		if(this.IsApplyToAll()) {
			for(let i=0; i<UI_Datagrid.instances.length; i++) {
				let instance = UI_Datagrid.instances[i];
				if(instance.id !== this.id) {
					instance.ApplySettings(this.maxColumnsShown, this.columnOffset);
				}
			}
		}
	}
	
	SaveSettings()
	{
		this.SetSetting('maxColumnsShown', this.maxColumnsShown);
		this.SetSetting('columnOffset', this.columnOffset);
	}
	
	SetSetting(name, value)
	{
		application.setPref('datagrid-'+this.id+'-'+name, value);
	}
	
	GetSetting(name, defaultValue)
	{
		return application.getPref('datagrid-'+this.id+'-'+name, defaultValue);
	}
	
   /**
    * Used when the "Apply to all" setting is active, to apply the same
    * settings to all data grids in the page.
    * 
    * @param {Integer} maxColumnsShown
    * @param {Integer} columnOffset
    * @see Handle_ChangeMaxColumns()
    * @see Handle_ChangeColumnOffset()
    */
	ApplySettings(maxColumnsShown, columnOffset)
	{
		this.maxColumnsShown = maxColumnsShown;
		this.columnOffset = columnOffset;
		this.Handle_ChangeMaxColumns();
	}
	
   /**
    * Updates the column display according to the current settings:
    * hides and shows columns as needed.
    *
    */
	UpdateColumns()
	{
		this.log('Updating the visible columns.', 'ui');
		
		// the start and end offset of columns to show is determined
		// by the position the user chose to show.
		let startOffset = this.columnOffset;
		
		// the user may change the number of columns shown when he/she already
		// navigated to the end of the columns, so we check here if the offset
		// is still within bounds.
		if(startOffset > this.GetMaxColumnOffset()) {
			startOffset = this.GetMaxColumnOffset();
			this.columnOffset = startOffset;
			this.log('Start offset was too high, adjusting to '+this.GetMaxColumnOffset(), 'ui');
		}
		
		let endOffset = startOffset+this.maxColumnsShown;
		
		let position = 0;
		for(let i=0; i<this.columns.length; i++) {
			let column = this.columns[i];
			
			// only columns with the "cell" role are considered hideable.
			// this is because we don't want to hide action columns for ex.
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
		
		if(this.columnOffset === 0) {
			this.GetFormElement('previous').addClass('disabled');
		} else {
			this.GetFormElement('previous').removeClass('disabled');
		}

		this.UpdateHiddenColumnsHint();
		
		if(this.maxColumnsShown === this.CountHideableColumns()) {
			this.GetFormElement('plus').addClass('disabled');
		} else {
			this.GetFormElement('plus').removeClass('disabled');
		}
		
		if(this.maxColumnsShown === 1) {
			this.GetFormElement('minus').addClass('disabled');
		} else {
			this.GetFormElement('minus').removeClass('disabled');
		}

		this.SaveSettings();
	}

	UpdateHiddenColumnsHint()
	{
		const hiddenCount = this.CountHiddenColumns();
		let messages = [];
		const elHint = this.GetFormElement('hint');
		const elAmount = this.GetFormElement('hint_amount');

		if(hiddenCount === 1) {
			messages.push(t('1 column is not shown.'));
		} else if(hiddenCount > 1) {
			messages.push(t('%1$s columns are not shown.', hiddenCount));
		}

		if(this.TotalUserHiddenColumns === 1) {
			messages.push(t('1 column is disabled.'));
		} else if(this.TotalUserHiddenColumns > 1) {
			messages.push(t('%1$s columns are disabled.', this.TotalUserHiddenColumns));
		}

		if(messages.length > 0) {
			elAmount.html(messages.join(' '));
			elHint.show();
			return;
		}

		elAmount.text('');
		elHint.hide();
	}
	
	CountHiddenColumns()
	{
		let hidden = 0;
		for(let i=0; i<this.columns.length; i++) {
			let column = this.columns[i];
			if(column.IsHidden()) {
				hidden++;
			}
		}
		
		return hidden;
	}
	
   /**
    * Adds/registers a column with the data grid. This is done 
    * automatically serverside so the datagrid knows which columns
    * are available.
    *
    * @param {String} key The name of the data key holding the values
    * @param {String} [title=''] The title of the column
    * @param {String|Number} [id] The unique ID of the column
    * @param {String} [type='Regular'] The column type, e.g. "Regular" or "MultiSelect"
    * @param {Integer} [number] The column index, starting at 1
    * @param {String} [role='cell'] The column's role, e.g. "cell", "actions", etc.
    * @return {UI_Datagrid_Column}
    */
	AddColumn(key, title, id, type, number, role)
	{
		if(isEmpty(id)) { id = nextJSID(); }
		if(isEmpty(type)) { type = 'Regular'; }
		if(isEmpty(role)) { role = 'cell'; }
		if(isEmpty(number)) { number = this.entries.length; }
		if(isEmpty(title)) { title = ''; }
		
		const column = new UI_Datagrid_Column(
			this,
			key,
			title,
			String(id),
			type,
			number*1,
			role
		);

		this.columns.push(column);

		return column;
	}
	
   /**
    * Retrieves an indexed array containing all columns in
    * the grid, ordered in the order they are shown in the UI.
    * 
    * @return {UI_Datagrid_Column[]}
    */
	GetColumns()
	{
		return this.columns;
	}

	/**
	 * @return {UI_Datagrid_Column|null}
	 */
	GetColumnByName()
	{
		let found = null;
		$.each(
			this.columns,
			/**
			 * @param {Number} idx
			 * @param {UI_Datagrid_Column} column
			 * @return {boolean}
			 */
			function(idx, column) {
			if(column.GetName() === name) {
				found = column;
				return false;
			}
		});
		
		return found;
	}

	/**
	 * @param {String} title
	 * @return {this}
	 */
	SetTitle(title)
	{
		this.title = title;
		return this;
	}

	/**
	 * @param {Event} e
	 * @returns {boolean}
	 */
	Handle_Submit(e)
	{
		return true;
	}

	/**
	 * @param {Event} e
	 */
	CheckJumpToCustom (e)
	{
		if(e.keyCode !== KeyCodes.Enter) {
			return true;
		}

		e.preventDefault();
		e.stopPropagation();
		this.JumpToCustomPage();

		return false;
	}

   /**
    * When the advanced page navigation is shown, the user can enter a
    * custom page number to jump to. This takes that number and redirects
    * to the target page.
    * 
    */
	JumpToCustomPage()
	{
		let pageNr = this.GetFormElement('custompage').val();
		if(pageNr <= 0) {
			pageNr = 1;
		}
		
		if(pageNr > this.TotalPages) {
			pageNr = this.TotalPages;
		}
		
		application.redirect(this.BaseURL.replace('_PGNR_', pageNr));
	}
	
   /**
    * Tells the data grid that its entries (rows) should be sortable.
    * This is called automatically serverside.
    *  
    * @param {UI_DataGrid_Sortable|*} handlerObj The object that will handle sorting events. Must extend the UI_DataGrid_Sortable class.
    * @return boolean Whether the feature could be activated
    */
	MakeSortable(handlerObj)
	{
		if(!handlerObj instanceof UI_DataGrid_Sortable) {
			this.log('Cannot make the entries sortable: Specified handler is not an instance of UI_DataGrid_Sortable.', 'error');
			return false;
		}
		
		this.log('Enabled sortable entries.', 'ui');
		
		this.entriesSortable = true;
		this.sortHandler = handlerObj;
		
		return true;
	}

	/**
	 * @param {UI_DataGrid_Droppable|*} handlerObj
	 * @return {boolean}
	 */
	MakeDroppable(handlerObj)
	{
		if(!handlerObj instanceof UI_DataGrid_Droppable) {
			this.log('Cannot make the entries droppable: Specified handler is not an instance of UI_DataGrid_Droppable.', 'error');
			return false;
		}
		
		this.log('Enabled droppable entries.', 'ui');
		
		this.entriesDroppable = true;
		this.dropHandler = handlerObj;
		
		return true;
	}
	
	GetPrimaryName()
	{
		return this.PrimaryName;
	}

	GetDropHandler()
	{
		return this.dropHandler;
	}
	
   /**
    * Retrieves the entries sorting a handler object, as
    * specified with the {@link MakeSortable()} method.
    * 
    * @return {UI_DataGrid_Sortable|null}
    */
	GetSortHandler()
	{
		return this.sortHandler;
	}
	
   /**
    * Whether the entries of the grid are sortable.
    * @return boolean
    */
	IsEntriesSortable()
	{
		return this.entriesSortable;
	}
	
	IsEntriesDroppable()
	{
		return this.entriesDroppable;
	}
	
	StartSortableEntries()
	{
		this.GetBodyElement().sortable(new UI_DataGrid_Sortable_Configuration(this));
	}
	
	StartDroppableEntries()
	{
		const conf = new UI_DataGrid_Droppable_Configuration(this);

		this.GetBodyElement().droppable(conf);

		$('#'+this.GetFormID('dropper')).droppable(conf);
	}

	/**
	 * @return {jQuery|HTMLElement}
	 */
	GetBodyElement()
	{
		return $(this.GetBodySelector());
	}
	
	GetBodySelector()
	{
		return '#'+this.GetFormID('table') + ' TBODY';
	}
	
	ShowTable()
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
	}
	
   /**
    * Appends an entry at the end of the list. Note that this does
    * not handle saving the new entry: this logic has to be implemented
    * separately.
    * 
    * @param {Object} cellData The data for all columns in the row. Requires all keys to be set.
    * @return {UI_DataGrid_Entry|false} The created entry, or false if the data was invalid.
    */
	AppendEntry(cellData)
	{
		const entry = this.CreateNewEntry(cellData);
		if(!entry) {
			return false;
		}
		
		if(this.IsRendered()) {
			this.GetBodyElement().append(entry.Render());
	
			// display the table since we may have started from an empty table
			this.ShowTable();
		}

		this.Handle_EntriesModified();
		
		return entry;
	}
	
   /**
    * Removes an existing entry from the grid.
    * 
    * @param {UI_DataGrid_Entry} targetEntry
    */
	RemoveEntry(targetEntry)
	{
		targetEntry.Remove(); // remove the row from the DOM
		
		var keep = [];
		$.each(this.entries, function(idx, entry) {
			if(entry.GetID() !== targetEntry.GetID()) {
				keep.push(entry);
			}
		});
		
		this.entries = keep;

		this.Handle_EntriesModified();
	}

	/**
	 *
	 * @param {object} cellData
	 * @return {UI_DataGrid_Entry|false} The created entry, or false if the data was invalid.
	 */
	PrependEntry(cellData)
	{
		const entry = this.CreateNewEntry(cellData);
		if(!entry instanceof UI_DataGrid_Entry) {
			return false;
		}
		
		if(this.IsRendered()) {
			this.GetBodyElement().prepend(entry.Render());
	
			// display the table since we may have started from an empty table
			this.ShowTable();
		}
		
		this.Handle_EntriesModified();
		
		return entry;
	}

	/**
	 * @param {Number|String} primary
	 * @param {Object} cellData
	 * @return {UI_DataGrid_Entry|false} The created entry, or false if the data was invalid.
	 */
	InsertEntryBefore(primary, cellData)
	{
		const entry = this.CreateNewEntry(cellData);
		if(!entry instanceof UI_DataGrid_Entry) {
			return false;
		}
		
		if(this.IsRendered()) {
			const el = $('#' + this.GetFormID('table') + ' tr[data-refid="' + primary + '"]');

			el.before(entry.Render());
	
			// display the table since we may have started from an empty table
			this.ShowTable();
		}
		
		this.Handle_EntriesModified();
		
		return entry;
	}
	
	Handle_EntriesModified()
	{
		this.RefreshCount();
		this.Sort();
	}

	/**
	 * @param {Object} cellData
	 * @return {UI_DataGrid_Entry}
	 */
	RegisterEntry(cellData)
	{
		const entry = new UI_DataGrid_Entry(this, cellData);
		this.entries.push(entry);
		
		return entry;
	}
	
   /**
    * Creates and adds a new datagrid entry.
    * 
    * Note: if a primary key has been set, the data
    * will be verified to include a value for the key.
    * 
    * @param {Object} cellData
    * @returns {UI_DataGrid_Entry|false}
    */
	CreateNewEntry(cellData)
	{
		if(!this.ValidateCellData(cellData)) {
			return false;
		}
		
		return this.RegisterEntry(cellData);
	}

	/**
	 * @param {Object} cellData
	 * @return {boolean}
	 */
	ValidateCellData(cellData)
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
		const grid = this;
		let valid = true;
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
	}

	/**
	 *
	 * @param {Number|String} primaryValue
	 * @return {UI_DataGrid_Entry|null}
	 */
	GetEntry(primaryValue)
	{
		for(let i=0; i < this.entries.length; i++) {
			let entry = this.entries[i];
			if(String(entry.GetPrimary()) === String(primaryValue)) {
				return entry;
			}
		}
		
		return null;
	}
	
   /**
    * Retrieves all entries available in the grid.
    * 
    * @return {UI_DataGrid_Entry[]}
    */
	GetEntries()
	{
		return this.entries;
	}
	
   /**
    * Sets the name of the primary key in the data sets.
    * @param {String} name
    * @returns {this}
    */
	SetPrimaryName(name)
	{
		this.PrimaryName = name;
		return this;
	}
	
   /**
    * Checks whether the datagrid has a primary key set.
    * @returns {Boolean}
    */
	HasPrimary()
	{
		return this.PrimaryName != null;
	}

	/**
	 * @return {UI_DataGrid_Entry[]}
	 */
	GetSelectedEntries()
	{
		const grid = this;
		const entries = [];
		const checkboxes = $('#' + this.GetFormID('table') + ' input[name="datagrid_items[]"]:checked');

		$.each(checkboxes, function(idx, checkbox) {
			let entry = grid.GetEntry($(checkbox).attr('value'));
			if(entry != null) {
				entries.push(entry);
			}
		});
		
		return entries;
	}
	
   /**
    * Retrieves the primary keys of all currently selected list entries.
    * 
    * @return {String[]}
    */
	GetSelectedPrimaries()
	{
		const entries = this.GetSelectedEntries();
		const ids = [];

		$.each(entries, function(idx, entry) {
			ids.push(String(entry.GetPrimary()));
		});
		
		return ids;
	}

	/**
	 * @param {String} columnName
	 * @param {String} orderDir
	 * @return {this}
	 */
	SetOrderBy(columnName, orderDir)
	{
		this.GetFormElement('orderby').val(columnName);
		this.GetFormElement('orderdir').val(orderDir);
		this.GetFormElement().submit();

		return this;
	}
	
	log(message, category)
	{
		application.log(
			'DataGrid ['+this.id+']',
			message,
			category
		);
	}
	
   /**
    * Renders the datagrid to HTML. Note that this is only
    * used clientside. Serverside the markup is generated
    * entirely there.
    * 
    * @return {String}
    */
	_Render()
	{
		if(this.columns.length === 0) {
			application.log('DataGrid', 'No columns added', 'error');
			return '';
		}
		
		this.AddClass('table');
		this.SetStyle('display', 'table');
		this.SetAttribute('id', 'datagrid-'+this.id+'-table');
		
		let html = ''+
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
	}
	
	_PostRender()
	{
		if(this.entries.length === 0) {
			$('#datagrid-'+this.id+'-empty').show();
			$('#datagrid-'+this.id+'-table').hide();
		} else {
			$('#datagrid-'+this.id+'-empty').hide();
			$('#datagrid-'+this.id+'-table').show();
		}
	}
	
	EnableCompactMode()
	{
		return this.AddClass('table-condensed');
	}
	
	EnableHover()
	{
		return this.AddClass('table-hover');
	}
	
   /**
    * @protected
    */
	_GetTypeName()
	{
		return 'DataGrid';
	}
	
   /**
    * @protected
    */
	Handle_RowClicked(entry, column)
	{
		return this.TriggerEvent('RowClicked', entry, column);
	}
	
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
	RowClicked(handler)
	{
		return this.AddEventHandler('RowClicked', handler);
	}
	
	DialogActionNotSelectAllEnabled()
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
	}
	
   /**
    * Triggers the grid's clientside sorting, or sets a callback 
    * function to use for the sorting.
    * 
    * Note: This only works with clientside grids.
    * 
    * @param {Function} handler
    * @return {this}
    */
	Sort(handler)
	{
		if(!isEmpty(handler)) {
			return this.AddEventHandler('sort', handler);
		}
		
		if(!this.HasEventHandler('sort')) {
			return this;
		}

		this.log('Sorting ['+this.entries.length+'] entries');
		
		// start by re-ordering the internal entries collection 
		const entries = this.entries;
		$.each(this.eventHandlers['sort'], function(idx, handler) {
			entries.sort(handler);
		});

		// reorder the table rows
		if(this.IsRendered()) 
		{
			let previous = null;
			$.each(entries, function(idx, entry) {
				if(previous != null) {
					entry.GetRowElement().insertAfter(previous.GetRowElement());
				}
				
				previous = entry;
			});
		}
			
		return this;
	}
	
	RequirePrimary(operationLabel)
	{
		if(this.HasPrimary()) {
			return;
		}

		throw new ApplicationException(
			'No primary key defined',
			'A primary key needs to be set for operation ['+operationLabel+'].',
			this.ERROR_PRIMARY_KEY_NAME_REQUIRED
		);
	}
	
   /**
    * Retrieves an entry by its DOM row.
    * 
    * @protected
    * @return {UI_DataGrid_Entry|null}
    */
	GetEntryByRow(row)
	{
		this.RequirePrimary('Retrieve entry instance by row');
		
		let result = null;
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
	}
	
   /**
    * Refreshes the number of entries shown in the footer.
    * @protected
    */
	RefreshCount()
	{
		this.element('entries_count').html(t('%1$s entries total.', this.entries.length));
	}
}
