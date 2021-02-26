/**
 * Configuration object used as parameter for making the
 * row elements in a data grid sortable, using the sortable()
 * method from jQuery UI.
 * 
 * Note: this does not extend Class to stay compatible with
 * the expected object format for jQuery UI.
 * 
 * @package Application
 * @subpackage DataGrids
 * @class UI_DataGrid_Sortable_Configuration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
UI_DataGrid_Sortable_Configuration = function(grid) 
{
	var handler = grid.GetSortHandler();
	handler.SetGrid(grid);
	
	var manager = 
	{
		'grid':grid,
		'handler':handler,
		
		handleBeforeStop:function(event, ui)
		{
			var info = this.getPlaceholderInfo(ui, true);
			if(!this.isMoveAllowed(info)) {
				return false;
			}
			
			return true;
		},
		
		handleStart:function(event, ui)
		{
			var info = this.getPlaceholderInfo(ui);
			
			// store the initial position when we start dragging
			this.initialPos = {
				'top':info.prev.prev().attr('data-refid'), 
				'bottom':info.next.attr('data-refid')
			};
		},
		
		isMoveAllowed:function(info)
		{
			return this.handler.IsMoveAllowed(
				info.row, 
				info.prev, 
				info.next
			);
		},
		
		handleStop:function(event, ui)
		{
			var info = this.getPlaceholderInfo(ui, true);
			
			if(this.isInitialPosition(info)) {
				return;
			}
			
			info.row.addClass('row-moved');
			
			var row = info.row;
			var rows = $('#'+this.grid.GetFormID()+' .row-sortable');
			var id = row.attr('data-refid');
			var ids = [];
			$.each(rows, function(idx, item){
				ids.push($(item).attr('data-refid'));
			});
			
			this.handler.Save(id, ids, row, rows);
		},
		
		handleChange:function(event, ui)
		{
			var helper = $(ui.helper);
			var info = this.getPlaceholderInfo(ui);

			info.row.removeClass('initial-position');
			helper.removeClass('move-not-allowed');
			info.row.removeClass('move-not-allowed');
			
			if(!this.isMoveAllowed(info)) 
			{
				helper.addClass('move-not-allowed');
				helper.find('.datagrid-sort-helper').html(UI.Icon().Warning() + ' ' + t('May not be dropped here.'));
				info.row.addClass('move-not-allowed');
			} 
			else if(this.isInitialPosition(info)) 
			{
				info.row.addClass('initial-position');
				helper.find('.datagrid-sort-helper').html(t('Original position:') + ' ' + t('Cancel reordering.'));
			} 
			else 
			{
				helper.find('.datagrid-sort-helper').html(t('Drop here to reorder.'));
			}
		},
		
		isInitialPosition:function(info)
		{
			if(info.prev.attr('data-refid') == this.initialPos.top || info.next.attr('data-refid') == this.initialPos.bottom) {
				return true;
			}
			
			return false;
		},
		
		handleHelper:function()
		{
			return $(
				'<tr class="datagrid-row-sorting">'+
					'<td colspan="'+this.grid.columns+'">'+
						'<div class="datagrid-sort-helper">'+
							t('Drop here to reorder.')+
						'</div>'+
					'</td>'+
				'</tr>'
			);
		},
		
		'lastInfo':null,
		
		getPlaceholderInfo:function(ui, beforeStop)
		{
			if(typeof(beforeStop)=='undefined') {
				beforeStop = false;
			}
			
			if(beforeStop) {
				return this.lastInfo;
			}
			
			var row = $(ui.placeholder);
			var next = row.next();
			var prev = row.prev();
			
			if(next.hasClass('ui-sortable-helper')) {
				next = next.next();
			}
			
			if(prev.hasClass('ui-sortable-helper')) {
				prev = prev.prev();
			}
			
			var info = {
				'id':$(ui.item).attr('data-refid'),
				'row':row,
				'next':next,
				'prev':prev 
			};
			
			this.lastInfo = info;
			
			return info;
		}
	};

	this.cursor = 'ns-resize';
	this.placeholder = 'datagrid-row-sort-target';
	this.forceHelperSize = true;
	this.axis = 'y';
	this.cancel = '.row-immovable';
	
	this.helper = function(event, row) { return manager.handleHelper(event, row); };
	this.start = function(event, ui) { return manager.handleStart(event, ui); };
	this.stop = function(event, ui) { return manager.handleStop(event, ui); };
	this.change = function(event, ui) {	return manager.handleChange(event, ui); };
	this.beforeStop =function(event, ui) { return manager.handleBeforeStop(event, ui); }; 
};
