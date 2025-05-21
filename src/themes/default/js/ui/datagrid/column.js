"use strict";

/**
 * Container for a single column in a datagrid, offering easy access
 * to the column's information. Also provides a simple API for hiding
 * or showing the column.
 *
 * @package UI
 * @subpackage DataGrids
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Datagrid_Column 
{
   /**
    * @param {UI_Datagrid} grid
    * @param {String} key The name of the key for the value of the column
    * @param {String} title The title of the column 
    * @param {String} id
	* @param {String} type
    * @param {Integer} number
    * @param {String} role For example "cell", "actions", "heading"
    */
	constructor(grid, key, title, id, type, number, role)
	{
		this.Type = type;
		this.Grid = grid;
		this.Number = number;
		this.Role = role;
		this.DataKey = key;
		this.Title = title;
		
		this.hidden = false;
		this.align = 'left';
		this.editable = false;
		this.editableClassName = null;
		this.jsID = id;
		this.compact = false;
	}
	
   /**
    * Retrieves the name of the column (the name of the corresponding data key)
    * @return {String}
    */
	GetName()
	{
		return this.DataKey;
	}
	
   /**
    * Sets the role of the column to actions.
    * @return {this}
    */
	RoleActions()
	{
		this.role = 'actions';
		return this;
	}
	
	IsActions()
	{
		return this.role === 'actions';
	}
	
   /**
    * Sets the column's title.
    * @param {String} title
    * @returns {this}
    */
	SetTitle(title)
	{
		this.Title = title;
		return this;
	}
	
   /**
    * Checks whether this column can be hidden. 
    *
    * @return {Boolean}
    */
	IsHideable()
	{
		return this.Role === 'cell';
	}
	
   /**
    * Hides the column. Note: does NOT check if the column is hideable.
    */
	Hide()
	{
		$('#datagrid-'+this.Grid.id+'-table .column-'+this.Number).hide();
		this.hidden = true;
	}
	
   /**
    * Shows the column. It does not need to have been hidden prior to this.
    */
	Show()
	{
		$('#datagrid-'+this.Grid.id+'-table .column-'+this.Number).show();
		this.hidden = false;
	}
	
   /**
    * Checks whether the column is currently hidden.
    *
    * @return {Boolean}
    */
	IsHidden()
	{
		return this.hidden;
	}
	
	IsCell()
	{
		return this.Role === 'cell';
	}

	/**
	 * @return {this}
	 */
	AlignCenter()
	{
		this.align = 'center';
		return this;
	}

	/**
	 * @return {this}
	 */
	AlignRight()
	{
		this.align = 'right';
		return this;
	}

	/**
	 * @return {this}
	 */
	MakeCompact()
	{
		this.compact = true;
		return this;
	}

	/**
	 * @param {UI_DataGrid_Entry} entry
	 * @return {string}
	 */
	Render(entry)
	{
		const entryJSID = nextJSID(); // the column JSID is always the same for the column
		const cellData = entry.GetData();
		let content;
		const classes = [];
		classes.push('role-' + this.Role);
		classes.push('align-' + this.align);
		
		if(this.Type === 'MultiSelect') {
			content = '<input type="checkbox" name="datagrid_items[]" value="' + cellData[this.Grid.GetPrimaryName()] + '"/>';
		} else {
			content = cellData[this.DataKey];
			classes.push('column-' + this.Number);
		}
		
		if(content == null || typeof(content) == 'undefined') {
			content = '';
		}

		const attributes = {};
		if(this.editable) {
			classes.push('editable');
		}
		
		attributes['id'] = entryJSID;
		attributes['class'] = classes.join(' ');
		
		const column = this;
		UI.RefreshTimeout(function() {
			column.PostRender(entry, entryJSID);
		});
		
		return '<td' + UI.CompileAttributes(attributes) + '>' + content + '</td>';
	}

	/**
	 *
	 * @param {UI_DataGrid_Entry} entry
	 * @param {Number} entryJSID
	 */
	PostRender(entry, entryJSID)
	{
		const column = this;
		const el = $('#' + entryJSID);

		el.click(function() {
			column.Handle_Click(el, entry);
		});
	}
	
	RenderHeader()
	{
		const classes = [];
		classes.push('role-' + this.Role);
		classes.push('align-' + this.align);

		const styles = {};
		const attributes = {};
		let content = this.Title;

		if(this.Type === 'MultiSelect') {
			//content = ''; // FIXME
		} else {
			classes.push('column-' + this.Number);
		}

		if(this.compact) {
			styles['width'] = '1%';
		}
		
		attributes['class'] = classes.join(' ');
		attributes['style'] = UI.CompileStyles(styles);
		
		return '<th '+UI.CompileAttributes(attributes)+'>'+content+'</th>';
	}
	
   /**
    * Set server-side: makes the cells in this column editable via click,
    * using an existing handler object that renders the edit controls and
    * handles saving changes.
    * 
    * @param {String} handlerClassName
    * @return {this}
    */
	SetEditable(handlerClassName)
	{
		this.editable = true;
		this.editableClassName = handlerClassName;
		return this;
	}
	
	IsEditable()
	{
		return this.editable;
	}

	/**
	 * @param {jQuery} cell
	 * @param {UI_DataGrid_Entry} entry
	 */
	Handle_Click(cell, entry)
	{
		if(this.IsActions()) {
			return;
		}
		
		if(!this.Grid.HasPrimary()) {
			return;
		}
		
		if(isEmpty(entry)) {
			const primary = String(cell.parent().attr('data-refid'));
			if(isEmpty(primary)) {
				return;
			}
			
			entry = this.Grid.GetEntry(primary);
			if(!entry) {
				console.log('No entry for ['+primary+']!');
				return;
			}
		}
		
		if(this.IsEditable()) {
			const handler = new window[this.editableClassName](this.Grid, this, entry, cell);
			handler.Start();
			return;
		}
		
		entry.Handle_ColumnClicked(this);
	}

	/**
	 * @param {String|null} part
	 * @return {String|string}
	 */
    elementID(part=null)
    {
        if (typeof part === 'undefined' || part === null) {
			return this.jsID;
		}

        return this.jsID + '_' + part;
    }

	/**
	 * @param {String|null} part
	 * @return {jQuery|HTMLElement}
	 */
    element(part=null)
    {
        return $('#' + this.elementID(part));
    }
}
