var ErrorLog =
{
	'exceptions':[],
	'elDialogView':null,
	
	AddException:function(id, date, message, details, file, line, variables, hash)
	{
		this.exceptions.push({
			'id':id,
			'date':date,
			'message':message,
			'details':details,
			'file':file,
			'line':line,
			'variables':variables,
			'hash':hash
		});
	},
	
	DialogView:function(id)
	{
		this.RenderDialogView();
		
		exception = this.GetByID(id);
		
		var html = ''+
		DialogHelper.renderAbstract(t('Occurred on %1$s: %2$s', exception.date, exception.message ))+
		'<p><b>'+t('Error details')+'</b></p>'+
		exception.details+
		'<hr/>'+
		'<p><b>'+t('Source file')+'</b></p>'+
		'<p class="monospace">'+
			exception.file+':'+exception.line+
		'</p>'+
		'<hr/>'+
		'<p><b>'+t('Request variables')+'</b></p>'+
		'<p class="monospace">';
			$.each(exception.variables, function(name, val) {
				html += name+' = '+val+'<br/>';
			});
			html += ''+
		'</p>';
		
		this.Element('body').html(html);
		this.elDialogView.modal('show');
	},
	
	GetByID:function(id)
	{
		for(var i=0; i<this.exceptions.length; i++) {
			if(this.exceptions[i].id==id) {
				return this.exceptions[i];
			}
		}
		
		return null;
	},
	
	Element:function(part)
	{
		return $('#'+this.ElementID(part));
	},
	
	ElementID:function(part)
	{
		return 'exception_'+part;
	},
	
	RenderDialogView:function()
	{
		if(this.elDialogView!=null) {
			return;
		}

		this.elDialogView = DialogHelper.createLargeDialog(
			t('Exception details'),
			'<div id="'+this.ElementID('body')+'"></div>',
			DialogHelper.renderButton_close(t('OK'))
		);
	}
};