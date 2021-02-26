/**
 * Editable datagrid column class.
 * 
 * @package UI
 * @subpackage DataGrids
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_DataGrid_Column_Editable =
{
	'jsID':null,
	'grid':null,
	'column':null,
	'entry':null,
	'cell':null,
	'originalContent':null,
	'shown':null,
		
	'ERROR_METHOD_NOT_IMPLEMENTED':558001,
		
   /**
    * @param {UI_DataGrid} grid
    * @param {UI_DataGrid_Column} column
    * @param {UI_DataGrid_Entry} entry 
    * @param {DOMNode} cell The TD element being edited
    */
	init:function(grid, column, entry, cell)
	{
		this.jsID = 'ed'+nextJSID();
		this.grid = grid;
		this.column = column;
		this.entry = entry;
		this.cell = cell;
		this.originalContent = null;
		this.shown = false;
		this.elDisplay = $('#'+this.entry.GetPrimary()+'_display');
	},
	
	Start:function()
	{
		this.Render();
	},
	
	Render:function()
	{
		var editable = this;
		
		// create the div container for the edit controls
		var editControl = this.element('edit');
		if(editControl.length==0) {
			this.cell.append('<div id="'+this.elementID('edit')+'" class="colum-edit-controls"></div>');
			UI.RefreshTimeout(function() {
				editable.Render();
			});
			return;
		}
		
		editControl.html(this.RenderControls());
		
		UI.RefreshTimeout(function() {
			editable.PostRender();
		});

		this.Show();
	},
	
   /**
    * Builds the HTML markup required to edit the value of the
    * target column and returns it. It is generated anew each time.
    * 
    * @return {String}
    */
	RenderControls:function()
	{
		return this._RenderControls();
	},
	
	PostRender:function()
	{
		this.log('Doing the post render.', 'ui');
		
		var editable = this;
		var group = UI.ButtonGroup();
		group.Create(t('OK'))
			.MakeSuccess()
			.SetIcon(UI.Icon().OK())
			.Click(function() {
				editable.Handle_Confirm();
			});
		group.Create(t('Cancel'))
			.SetIcon(UI.Icon().Delete())
			.Click(function() {
				editable.Handle_Cancel();
			});
		
		var cellPos = this.cell.offset();
		var pos = {
			'top':cellPos.top + this.cell.outerHeight(true),
			'left':cellPos.left
		};
		
		$('body').append(
			'<div id="' + this.elementID('buttons') + '" class="datagrid-edit-buttons" style="top:'+pos.top+'px;left:'+pos.left+'px;width:'+this.cell.outerWidth(true)+'px">' +
				'<div class="inner">' +
					group.Render() +
				'</div>' +
			'</div>'
		);
		
		this._PostRender();
	},
	
	Handle_Confirm:function()
	{
		this.log('Edits have been confirmed.', 'event');
		
		this.Hide();
		this.cell.off(); // avoid the user clicking on the cell while we work
		this.originalContent = this.elDisplay.html();
		this.elDisplay.html(application.renderSpinner(t('Processing...')));
		
		this.log('Processing the data.');
		
		this._Handle_Confirm();
	},

   /**
    * When the user confirms the edit, the cell's contents are
    * replaced with a spinner until the new value is determined
    * by the handler. In case something goes wrong, this can be
    * used to restore the original cell contents.
    */
	Revert:function()
	{
		if(this.originalContent != null) {
			this.elDisplay.html(this.originalContent);
		}
	},
	
	Done:function(newContent)
	{
		this.log('Edits are done.', 'event');
		
		var editable = this;
		this.elDisplay.html(newContent);
		this.cell.click(function() { editable.Show(); });
	},
	
	Error:function(errorText)
	{
		this.log('Edits had an error: '+errorText, 'error');
		this.cell.addClass('error');
		
		this.elDisplay.html(
			application.renderAlertError(
				UI.Icon().Warning() + ' ' +
				'<b>' + t('Error') + '</b>',
				false
			)
		);
	},
	
	Handle_Cancel:function()
	{
		this.Hide();
	},
	
   /**
    * Hides the edit controls.
    */
	Hide:function()
	{
		this.log('Hiding the edit controls.', 'ui');
		
		var editable = this;

		this.shown = false;
		this.cell.off();
		this.cell.click(function() { editable.Show(); });
		this.element('buttons').hide();
		this.element('edit').hide();
		this.cell.removeClass('editing');
		this.elDisplay.show();
		
		this._Handle_Hidden();
	},
	
	Show:function()
	{
		if(this.shown) {
			return;
		}
		
		this.log('Showing the edit controls.', 'ui');
		
		this.shown = true;
		this.cell.removeAttr('onclick'); // was added serverside
		this.cell.off();
		this.element('buttons').show();
		this.element('edit').show();
		this.cell.addClass('editing');
		this.elDisplay.hide();
		
		this._Handle_Shown();
	},
	
   /**
    * Renders the html markup required for the edit controls. This
    * markup is automatically inserted into the cell.
    * 
    * MANDATORY: this has to be extended in the implementing class.
    */
	_RenderControls:function()
	{
		throw new ApplicationException(
			'Not implemented',
			'The RenderControls method has to be implemented by the extending class.',
			this.ERROR_METHOD_NOT_IMPLEMENTED
		);
	},
	
   /**
    * Called when the user has confirmed the edits. This should process the new data
    * in any way required, and terminate the operation using either the <code>Done()</code>
    * or <code>Error()</code> as applicable.
    * 
    * MANDATORY: this has to be extended in the implementing class.
    */
	_Handle_Confirm:function()
	{
		throw new ApplicationException(
			'Not implemented',
			'The RenderControls method has to be implemented by the extending class.',
			this.ERROR_METHOD_NOT_IMPLEMENTED
		);
	},
	
	_PostRender:function()
	{
		
	},
	
	_Handle_Shown:function()
	{
		
	},
	
	_Handle_Hidden:function()
	{
		
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
    },
    
    log:function(message, category)
    {
    	application.log(
			'DataGrid Editable [' + this.jsID + ']',
			message,
			category
    	);
    }
};

UI_DataGrid_Column_Editable = Class.extend(UI_DataGrid_Column_Editable);