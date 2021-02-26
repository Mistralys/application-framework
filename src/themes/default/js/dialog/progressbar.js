var Dialog_ProgressBar = 
{
	'bar':null, 
	'barRendered':null,
	
	_init:function()
	{
		this.bar = new ProgressBar();
		this.barRendered = false;
		this.PreventClosing();
		this.DisableFooter();
	},
	
	_RenderBody:function()
	{
		var html = ''+
		'<div id="' + this.elementID('bar-container') + '"></div>';
		
		return html;
	},
	
	_Handle_Shown:function()
	{
		this.PreventClosing();
		
		if(!this.barRendered) {
			this.bar.Render(this.element('bar-container'));
			this.barRendered = true;
		}
	},
	
	MakeCancellable:function(cancelHandler, cancelText)
	{
		this.bar.MakeCancellable(cancelHandler, cancelText);
		return this;
	},
	
	ProgressCompleted:function(text)
	{
		if(this.barRendered) {
			this.bar.Completed(text);
		}
	},
	
	ProgressUpdate:function(percent, processingText, statusText)
	{
		if(this.barRendered) {
			this.bar.Update(percent, processingText, statusText);
		}
	},
	
	Close:function()
	{
		this.AllowClosing();
		this.Hide();
	}
};

Dialog_ProgressBar = Dialog_Basic.extend(Dialog_ProgressBar);