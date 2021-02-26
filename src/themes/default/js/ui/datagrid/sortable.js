/**
 * Base class for event and configuration handler classes
 * when using the sortable entries feature of a data grid. 
 * Extend this class and implement the required methods in 
 * your sortable handling class.
 * 
 * See the serverside <code>UI_DataGrid::makeEntriesSortable()</code>
 * method to see how to configure a datagrid's entries to
 * be sortable. 
 * 
 * @package UI
 * @subpackage DataGrids
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_DataGrid_Sortable =
{
	'grid':null,
	
	init:function()
	{
		this.grid = null;
	},
		
   /**
    * Checks whether the target row may be moved to the new 
    * position, between the previous and next rows as specified.
    * 
    * Note that the previous or next rows may have a length of
    * 0 if the row has been moved to the top or the bottom of the
    * table respectively.
    * 
    * @param {DOMNode} row The row being moved
    * @param {DOMNode} previousRow The row that would be above the row after the move
    * @param {DOMNode} nextRow The row that would be below the row after the move
    * @return {Boolean} Whether to allow the move. If false, the move will be aborted and the row returned to its original position
    */
	IsMoveAllowed:function(row, previousRow, nextRow)
	{
		return true;
	},
	
   /**
    * Called when a row has been moved to a new position successfully.
    * By default this does nothing; up to the handler class to extend
    * this and implement its logic.
    * 
    * @param {String} id The ID of the item that was moved 
    * @param {Array} ids The IDs of all items in the table with the new order
    * @param {DOMNode} row The row that was moved
    * @param {Array} rows A list of all rows in the table with the new order
    */
	Save:function(id, ids, row, rows)
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

UI_DataGrid_Sortable = Class.extend(UI_DataGrid_Sortable);