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
var UI_Datagrid_Column = 
{
   /**
    * @property {UI_Datagrid}
    */
	'Grid':null,
	
   /**
    * The column's number in the grid, sequential.
    * @property {Integer}
    */
	'Number':null,
	
   /**
    * The column's role, used to determine whether it can be hidden.
    * @property {String}
    */
	'Role':null,
	
	'Type':null,
	
   /**
    * The data key corresponding to this column.
    * @property {String}
    */
	'DataKey':null,
	
	'Label':null,
	
	'hidden':null,
	'align':null,
	'compact':null,
	'editable':null,
	'editableClassName':null,

   /**
    * Constructor. 
    * 
    * @param {UI_Datagrid} grid
    * @param {String} key The name of the key for the value of the column
    * @param {String} title The title of the column 
    * @param {String} id
    * @param {Integer} number
    * @param {String} role For example "cell", "actions", "heading"
    */
	init:function(grid, key, title, id, type, number, role)
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
	},
	
   /**
    * Retrieves the name of the column (the name of the corresponding data key)
    * @return {String}
    */
	GetName:function()
	{
		return this.DataKey;
	},
	
   /**
    * Sets the role of the column to actions.
    * @return {UI_DataGrid_Column}
    */
	RoleActions:function()
	{
		this.role = 'actions';
		return this;
	},
	
	IsActions:function()
	{
		if(this.role == 'actions') {
			return true;
		}
		
		return false;
	},
	
   /**
    * Sets the column's title.
    * @param {String} title
    * @returns {UI_Datagrid_Column}
    */
	SetTitle:function(title)
	{
		this.Title = title;
		return this;
	},
	
   /**
    * Checks whether this column can be hidden. 
    *
    * @return {Boolean}
    */
	IsHideable:function()
	{
		if(this.Role=='cell') {
			return true;
		}
		
		return false;
	},
	
   /**
    * Hides the column. Note: does NOT check if the column is hideable.

    */
	Hide:function()
	{
		$('#datagrid-'+this.Grid.id+'-table .column-'+this.Number).hide();
		this.hidden = true;
	},
	
   /**
    * Shows the column. It does not need to have been hidden prior to this.

    */
	Show:function()
	{
		$('#datagrid-'+this.Grid.id+'-table .column-'+this.Number).show();
		this.hidden = false;
	},
	
   /**
    * Checks whether the column is currently hidden.
    *
    * @return {Boolean}
    */
	IsHidden:function()
	{
		return this.hidden;
	},
	
	IsCell:function()
	{
		if(this.Role=='cell') {
			return true;
		}
		
		return false;
	},
	
	AlignCenter:function()
	{
		this.align = 'center';
		return this;
	},
	
	AlignRight:function()
	{
		this.align = 'right';
		return this;
	},
	
	MakeCompact:function()
	{
		this.compact = true;
		return this;
	},
	
	Render:function(entry)
	{
		var entryJSID = nextJSID(); // the column JSID is always the same for the column
		var cellData = entry.GetData();
		var content = '';
		var classes = [];
		classes.push('role-' + this.Role);
		classes.push('align-' + this.align);
		
		if(this.Type=='MultiSelect') {
			content = '<input type="checkbox" name="datagrid_items[]" value="' + cellData[this.Grid.GetPrimaryName()] + '"/>';
		} else {
			content = cellData[this.DataKey];
			classes.push('column-' + this.Number);
		}
		
		if(content == null || typeof(content) == 'undefined') {
			content = '';
		}

		var atts = {};
		if(this.editable) {
			classes.push('editable');
		}
		
		atts['id'] = entryJSID;
		atts['class'] = classes.join(' ');
		
		var column = this;
		UI.RefreshTimeout(function() {
			column.PostRender(entry, entryJSID);
		});
		
		return '<td' + UI.CompileAttributes(atts) + '>' + content + '</td>';
	},
	
	PostRender:function(entry, entryJSID)
	{
		var column = this;
		var el = $('#'+entryJSID);
		
		el.click(function() {
			column.Handle_Click(el, entry);
		});
	},
	
	RenderHeader:function()
	{
		var classes = [];
		classes.push('role-' + this.Role);
		classes.push('align-' + this.align);

		var styles = {};
		var atts = {};
		
		if(this.Type=='MultiSelect') {
			content = ''; // FIXME
		} else {
			classes.push('column-' + this.Number);
		}

		if(this.compact) {
			styles['width'] = '1%';
		}
		
		atts['class'] = classes.join(' ');
		atts['style'] = UI.CompileStyles(styles);
		
		return '<th '+UI.CompileAttributes(atts)+'>'+this.Title+'</th>';
	},
	
   /**
    * Set server-side: makes the cells in this column editable via click,
    * using an existing handler object that renders the edit controls and
    * handles saving changes.
    * 
    * @param {String} handlerClassName
    * @return {UI_DataGrid_Column}
    */
	SetEditable:function(handlerClassName)
	{
		this.editable = true;
		this.editableClassName = handlerClassName;
		return this;
	},
	
	IsEditable:function()
	{
		return this.editable;
	},
	
	Handle_Click:function(cell, entry)
	{
		if(this.IsActions()) {
			return;
		}
		
		if(!this.Grid.HasPrimary()) {
			return;
		}
		
		if(isEmpty(entry)) {
			var primary = cell.parent().attr('data-refid');
			if(typeof(primary)=='undefined' || primary==null) {
				return;
			}
			
			var entry = this.Grid.GetEntry(primary);
			if(!entry) {
				console.log('No entry for ['+primary+']!');
				return;
			}
		}
		
		if(this.IsEditable()) {
			var handler = new window[this.editableClassName](this.Grid, this, entry, cell); 
			handler.Start();
			return;
		}
		
		entry.Handle_ColumnClicked(this);
	},
	
    elementID: function (part) 
    {
        if (typeof(part) == 'undefined') {
            return this.jsID;
        }

        return this.jsID + '_' + part;
    },

    element: function (part) 
    {
        var id = this.elementID(part);
        return $('#' + id);
    }

};

UI_Datagrid_Column = Class.extend(UI_Datagrid_Column);