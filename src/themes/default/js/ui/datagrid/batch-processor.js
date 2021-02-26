var UI_DataGrid_BatchProcessor = 
{
	'ids':null,
	'params':null,
	'batchSize':null,
	'progressBar':null,
	
	init:function()
	{
		this.ids = [];
		this.params = null;
		this.batchSize = 100;
	},
	
	SetIDs:function(ids)
	{
		this.ids = ids;
		return this;
	},
	
	SetParams:function(params)
	{
		this.params = params;
		return this;
	},
	
	SetBatchSize:function(size)
	{
		this.batchSize = size;
		return this;
	},
	
	Start:function()
	{
		var instance = this;

		var params = this.params;
		params['datagrid_batch_processing'] = 'yes';
		
		var proc = new ProgressBar_BatchProcessor(null)
		.SetAJAXURL(application.buildURL())
		.MakeProgressBar($('#batch-progressbar'))
		.MakeCancellable(UI.Text(t('Back to the list')).Link(this.GetScreenParams()).SetIcon(UI.Icon().Back()))
		.SetBatchSize(this.batchSize)
		.SetItemsPayloadKey('datagrid_items')
		.SetPayload(this.params)
		.StatusTextUpdated(function(procObj, batchNumber, percent, processed) {
			return t('Batch %1$s/%2$s, %3$s entries processed.', batchNumber, procObj.GetTotalBatches(), processed);
		})
		.Success(function() {
			instance.Handle_Completed();
		});

		proc.AddItems(this.ids);
		
		proc.Start();
	},
	
	Handle_Completed:function()
	{
		application.redirect(this.GetScreenParams(), t('Please wait, refreshing...'));
	},
	
	GetScreenParams:function()
	{
		var params = {};
		params['datagrid_batch_processing'] = 'yes';
		params['datagrid_batch_complete'] = this.params['datagrid_action'];
		
		// remove the datagrid params for the redirect
		$.each(this.params, function(name, val) {
			// a datagrid action parameter array
			if(name.substring(0, 7) == 'action_') {
				$.each(val, function(valName, valValue) {
					params[name + '['+valName+']'] = valValue;
				});
				return;
			}
			
			if(name.substring(0, 8) != 'datagrid') {
				params[name] = val;
			}
		});
		
		return params;
	}
};

UI_DataGrid_BatchProcessor = Class.extend(UI_DataGrid_BatchProcessor);