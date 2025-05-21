"use strict";

class UI_DataGrid_Entry
{
	/**
	 *
	 * @param {UI_Datagrid} grid
	 * @param {Object} cellData
	 */
	constructor(grid, cellData)
	{
		this.id = 'row' + nextJSID();
		this.grid = grid;
		this.cellData = cellData;

		/**
		 * @type {jQuery|null}
		 */
		this.rowElement = null;

		/**
		 * @type {String,*}
		 */
		this.tags = {};
	}
	
	GetID()
	{
		return this.id;
	}

	/**
	 * @return {String}
	 */
	GetPrimary()
	{
		return String(this.cellData[this.grid.PrimaryName]);
	}

	/**
	 * @param {String} name
	 * @param {String|Number|null} defaultValue
	 * @return {String|Number|null}
	 */
	Get(name, defaultValue = null)
	{
		if(typeof this.cellData[name] !== 'undefined') {
			return this.cellData[name];
		}
		
		if(typeof defaultValue === 'undefined') {
			defaultValue = null;
		}
		
		return defaultValue;
	}
	
	GetData()
	{
		return this.cellData;
	}
	
	Render()
	{
		const entry = this;
		const columns = this.grid.GetColumns();
		const cellData = this.cellData;
		const classes = [];
		const attributes = {
			'data-refid': cellData[this.grid.PrimaryName],
			'id': this.id
		};

		if(this.grid.IsEntriesSortable()) {
			classes.push('row-sortable');
		}
		
		if(classes.length > 0) {
			attributes['class'] = classes.join(' ');
		}
		
		let html = ''+
		'<tr' + UI.CompileAttributes(attributes) + '>';
			$.each(columns, function(idx, column) {
				html += column.Render(entry);
			});
			html += ''+
		'</tr>';

		UI.RefreshTimeout(function() {
			entry.PostRender();
		});
			
		return html;
	}
	
	PostRender()
	{
		
	}
	
	Handle_ColumnClicked(column)
	{
		this.grid.Handle_RowClicked(this, column);
	}


	/**
	 * @return {jQuery}
	 */
	GetRowElement()
	{
		if(this.rowElement !== null) {
			return this.rowElement;
		}

		this.rowElement = $('#'+this.id);

		if(this.rowElement.length === 0) {
			const selector = '#' + this.grid.GetFormID('table') + ' TR[data-refid=' + this.GetPrimary() + ']';
			this.rowElement = $(selector);
		}

		return this.rowElement;
	}
	
   /**
    * Checks whether the specified table row element is 
    * the row of this entry.
    * 
    * @param {HTMLElement|jQuery} rowElement
    * @returns {Boolean}
    */
	IsRowElement(rowElement)
	{
		return String($(rowElement).attr('data-refid')) === this.GetPrimary();
	}

	/**
	 * @return {this}
	 */
	Remove()
	{
		this.GetRowElement().remove();
		return this;
	}
	
   /**
    * Adds a class to the entry's <code>TR</code> tag.
    * 
    * @param {String} className
    * @return {this}
    */
	AddClass(className)
	{
		this.GetRowElement().addClass(className);
		return this;
	}
	
   /**
    * Marks the row as immovable. This is only relevant when the
    * list has its sortable functionality enabled. This is typically
    * called by the custom sorting handler class that has to be created
    * for a sortable list. 
    * 
    * @return {this}
    */
	MakeImmovable()
	{
		return this.AddClass('row-immovable');
	}
	
   /**
    * Highlights the row using the <code>row-highlighted</code> class.
    * @return {this}
    */
	MakeHighlighted()
	{
		return this.AddClass('row-highlighted');
	}
	
   /**
    * Unchecks the entry's checkbox. Has no effect if it was not checked,
    * or if the data grid does not have multi actions enabled.
    * 
    * @return {this}
    */
	Deselect()
	{
		return this.SetSelected(false);
	}
	
   /**
    * Ticks the entry's checkbox. Has no effect if it was not checked,
    * or if the data grid does not have multi actions enabled.
    * 
    * @return {this}
    */
	Select()
	{
		return this.SetSelected(true);
	}
	
   /**
    * Sets whether the entry's checkbox should be ticked or not. Same
    * as <code>Deselect()</code> and <code>Select()</code>, except you
    * have to specify the state.
    * 
    * @param {Boolean} selected
    * @return {this}
    */
	SetSelected(selected)
	{
		this.GetRowElement().find('input[name="datagrid_items[]"]').prop('checked', selected);
		return this;
	}
	
   /**
    * Sets the value of a tag: this is used solely to store
    * arbitrary data along with the datagrid entry, like, for
    * example, an object instance related to the entry.
    * 
    * @param {String} name
    * @param {*} value
    * @return {this}
    */
	SetTag(name, value)
	{
		this.tags[name] = value;
		return this;
	}
	
   /**
    * Retrieves the value of a previously set tag.
    *
	* @param {String} name
    * @return {*}
    */
	GetTag(name)
	{
		if(typeof this.tags[name] !== 'undefined') {
			return this.tags[name];
		}
		
		return null;
	}
	
	Pulsate()
	{
		UI.PulsateElement(this.GetRowElement());
	}
}
