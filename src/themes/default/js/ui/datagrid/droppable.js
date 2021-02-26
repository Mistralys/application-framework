/**
 * Base class for event and configuration handler classes
 * when using the sortable entries feature of a data grid. 
 * Extend this class and implement the required methods in 
 * your sortable handling class.
 * 
 * See the serverside <code>UI_DataGrid::makeEntriesDroppable()</code>
 * method to see how to configure a datagrid's entries to
 * be sortable.
 * 
 * @package UI
 * @subpackage DataGrids
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_DataGrid_Droppable =
{
	'grid':null,
	
	init:function()
	{
		this.grid = null;
	},
		
   /**
    * Checks whether the target element may be dropped into the table.
    * 
    * @param {DOMNode} row The element being dropped
    * @return {Boolean} Whether to allow the drop. If false, the drop will be aborted.
    */
	IsDropAllowed:function(element)
	{
		return true;
	},
	
   /**
    * Called when a valid element has been dropped.
    * By default this does nothing; up to the handler class to extend
    * this and implement its logic.
    * 
    * @param {DOMNode} element The element that was dropped
    */
	Dropped:function(element)
	{
	},
	
   /**
    * Sets the data grid object instance for the grid being
    * handled by this class. This is set automatically by the
    * grid when setting up droppables.
    */
	SetGrid:function(grid)
	{
		this.grid = grid;
	}
};

UI_DataGrid_Droppable = Class.extend(UI_DataGrid_Droppable);