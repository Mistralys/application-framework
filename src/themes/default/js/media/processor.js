var Media_Processor =
{
	'documents':[],
	'percentPerStep':0,
	'redirect':{},
	'progressBar':null,
		
	AddDocument:function(document_id, config_id)
	{
		this.documents.push({
			'document_id':document_id,
			'config_id':config_id
		});
	},
	
	Start:function()
	{
		// this can happen if the user reloads the page and
		// all documents have completed in the background.
		if(this.documents.length==0) {
			this.Handle_ProcessComplete();
			return;
		}
		
		this.percentPerStep = 100/this.documents.length;
		this.progressBar = new ProgressBar();
		
		this.RenderUI();

		// start with the first task to launch the process
		this.ProcessDocument(0);
	},
	
	RenderUI:function()
	{
		var html = ''+
		'<div id="media-progressbar"></div>'+
		'<p>'+
			UI.Icon().Information().MakeInformation()+' '+
			t('Preparing media files, please wait.')+' '+
			t('You will be redirected automatically once this is done.')+
		'</p>';

		$('#prepare-media').html(html);

		this.progressBar.Render($('#media-progressbar'));
	},
	
	ProcessDocument:function(index)
	{
		var document = this.documents[index];
		var payload = {
			'document_id':document.document_id,
			'config_id':document.config_id
		};

		application.AJAX(
			'ProcessMediaDocument', 
			payload, 
			function(data) {
				Media_Processor.Handle_ProcessSuccess(data, index);
			}, 
			function(errorText, data) {
				Media_Processor.Handle_ProcessFailure(errorText, data, index);
			}
		);
	},
	
	Handle_ProcessSuccess:function(data, index)
	{
		var totalPercent = (index+1) * this.percentPerStep;

		this.progressBar.Update(totalPercent);
		
		var maxIndex = this.documents.length-1;
		if(index==maxIndex) {
			this.Handle_ProcessComplete();
			return;
		}
		
		index++;
		
		this.ProcessDocument(index);
	},
	
	Handle_ProcessFailure:function(errorText, data, index)
	{
		console.log(data);
		
		$('#prepare-media').html(
			'<div class="alert alert-error">'+
				UI.Icon().Warning().MakeDangerous()+' '+
				'<b>'+t('Error:')+'</b> '+
				t('Failed to process a media file.')+' '+
				t('Please contact a system administrator to look into the problem.')+' '+
				t('A log of the error has been created.')+
			'</div>'+
			'<p>'+
				UI.Icon().Information().MakeInformation() + ' ' +
				'<b>' + t('Note:') + '</b> ' +
				t('To try again, simply reload this page.') +
			'</p>'
		);
	},
	
	Handle_ProcessComplete:function()
	{
		this.progressBar.Completed(
			UI.Icon().OK()+' '+t('Completed.')+' '+
			t('Redirecting...')+' '+UI.Icon().Spinner()
		);
		
		application.redirect(this.redirect);
	}
};