var UI_DataGrid_Entry = 
{
	'id':null,
	'cellData':null,
	'rowElement':null,
	'tags':null,
	
	init:function(grid, cellData)
	{
		this.id = 'row' + nextJSID();
		this.grid = grid;
		this.cellData = cellData;
		this.rowElement = null;
		this.tags = {};
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	GetPrimary:function()
	{
		return this.cellData[this.grid.PrimaryName];
	},
	
	Get:function(name, defaultValue)
	{
		if(typeof(this.cellData[name]) != 'undefined') {
			return this.cellData[name];
		}
		
		if(typeof(defaultValue)=='undefined') {
			defaultValue = null;
		}
		
		return defaultValue;
	},
	
	GetData:function()
	{
		return this.cellData;
	},
	
	Render:function()
	{
		var entry = this;
		var columns = this.grid.GetColumns();
		var cellData = this.cellData;
		var classes = [];
		var atts = {
			'data-refid':cellData[this.grid.PrimaryName],
			'id':this.id
		};
		
		if(this.grid.IsEntriesSortable()) {
			classes.push('row-sortable');
		}
		
		if(classes.length > 0) {
			atts['class'] = classes.join(' ');
		}
		
		var html = ''+
		'<tr' + UI.CompileAttributes(atts) + '>';
			$.each(columns, function(idx, column) {
				html += column.Render(entry);
			});
			html += ''+
		'</tr>';

		UI.RefreshTimeout(function() {
			entry.PostRender();
		});
			
		return html;
	},
	
	PostRender:function()
	{
		
	},
	
	Handle_ColumnClicked:function(column)
	{
		this.grid.Handle_RowClicked(this, column);
	},
	
	GetRowElement:function()
	{
		if(this.rowElement==null) 
		{
			this.rowElement = $('#'+this.id);
			
			if(this.rowElement.length==0) {
				var selector = '#' + this.grid.GetFormID('table')+' TR[data-refid='+this.GetPrimary()+']';
				this.rowElement = $(selector);
			}
		}
		
		return this.rowElement;
	},
	
   /**
    * Checks whether the specified table row element is 
    * the row of this entry.
    * 
    * @param {DOMElement} rowElement
    * @returns {Boolean}
    */
	IsRowElement:function(rowElement)
	{
		var refid = $(rowElement).attr('data-refid');
		
		return refid == this.GetPrimary();
	},
	
	Remove:function()
	{
		this.GetRowElement().remove();
	},
	
   /**
    * Adds a class to the entry's <code>TR</code> tag.
    * 
    * @param {String} className
    * @return {UI_DataGrid_Entry}
    */
	AddClass:function(className)
	{
		this.GetRowElement().addClass(className);
		return this;
	},
	
   /**
    * Marks the row as immovable. This is only relevant when the
    * list has its sortable functionality enabled. This is typically
    * called by the custom sorting handler class that has to be created
    * for a sortable list. 
    * 
    * @return {UI_DataGrid_Entry}
    */
	MakeImmovable:function()
	{
		return this.AddClass('row-immovable');
	},
	
   /**
    * Highlights the row using the <code>row-highlighted</code> class.
    * @return {UI_DataGrid_Entry} 
    */
	MakeHighlighted:function()
	{
		return this.AddClass('row-highlighted');
	},
	
   /**
    * Unticks the entry's checkbox. Has no effect if it was not checked,
    * or if the data grid does not have multi actions enabled.
    * 
    * @return {UI_DataGrid_Entry}
    */
	Deselect:function()
	{
		return this.SetSelected(false);
	},
	
   /**
    * Ticks the entry's checkbox. Has no effect if it was not checked,
    * or if the data grid does not have multi actions enabled.
    * 
    * @return {UI_DataGrid_Entry}
    */
	Select:function()
	{
		return this.SetSelected(true);
	},
	
   /**
    * Sets whether the entry's checkbox should be ticked or not. Same
    * as <code>Deselect()</code> and <code>Select()</code>, except you
    * have to specify the state.
    * 
    * @param {Boolean} selected
    * @return {UI_DataGrid_Entry}
    */
	SetSelected:function(selected)
	{
		this.GetRowElement().find('input[name="datagrid_items[]"]').prop('checked', selected);
		return this;
	},
	
   /**
    * Sets the value of a tag: this is used solely to store
    * arbitrary data along with the datagrid entry, like for
    * example an object instance related to the entry.
    * 
    * @param {String} name
    * @param {Mixed} value
    * @return {UI_DataGrid_Entry}
    */
	SetTag:function(name, value)
	{
		this.tags[name] = value;
		return this;
	},
	
   /**
    * Retrieves the value of a previously set tag.
    * 
    * @return {Mixed}
    */
	GetTag:function(name)
	{
		if(typeof(this.tags[name]) != 'undefined') {
			return this.tags[name];
		}
		
		return null;
	},
	
	Pulsate:function()
	{
		UI.PulsateElement(this.GetRowElement());
	}
};

UI_DataGrid_Entry = Class.extend(UI_DataGrid_Entry);