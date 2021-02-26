/**
 * Configuration object used as parameter for making the
 * row elements in a data grid droppable, using the droppable()
 * method from jQuery UI.
 * 
 * Note: this does not extend Class to stay compatible with
 * the expected object format for jQuery UI.
 * 
 * @package Application
 * @subpackage DataGrids
 * @class UI_DataGrid_Droppable_Configuration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
UI_DataGrid_Droppable_Configuration = function(grid) 
{
	var handler = grid.GetDropHandler();
	handler.SetGrid(grid);
	
	var manager = 
	{
		'grid':grid,
		'handler':handler,
		
		handleDrop:function(event, ui)
		{
			this.handler.Dropped(ui.draggable);
			return true;
		},
		
		handleAccept:function(element)
		{
			return this.handler.IsDropAllowed($(element));
		}
	};

	this.activeClass = 'is-drag-target';
    this.hoverClass = 'can-be-dropped';
    this.tolerance = 'pointer';
    this.drop = function (event, ui) { return manager.handleDrop(event, ui); };
    this.accept = function (element) { return manager.handleAccept(element); };
};
