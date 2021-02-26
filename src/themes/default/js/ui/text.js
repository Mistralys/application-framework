var UI_Text =
{
	'text':null,
	'layout':null,
	'layouts':['muted', 'text-warning', 'text-error', 'text-info', 'text-success'],
	'tag':null,
	'icon':null,
	
	init:function(text)
	{
		this._super();

		if(isEmpty(text)) {
			text = '';
		}
		
		this.text = text;
		this.tag = 'span';
		this.icon = null;
	},
		
	SetText:function(text)
	{
		this.text = text;
		
		if(this.IsRendered()) {
			this.element().html(this.text+'');
		}
		
		return this;
	},
	
	_Render:function()
	{
		this.AddClass('ui-text');
		
		var text = '<span id="'+this.elementID('icon')+'" style="display:none"></span>'+this.text;
		
		return '<'+this.tag+' ' + this.RenderAttributes()+'>' + text + '</'+this.tag+'>';
	},
	
	_PostRender:function()
	{
		var text = this;
		
		this.element().click(function() {
			text.Handle_Click();
		});
		
		if(this.icon != null) {
			this.element('icon').html(this.icon+' ').show();
		}
	},
	
	_GetTypeName:function()
	{
		return 'HTML Text';
	},
	
	SetTag:function(tagName)
	{
		this.tag = tagName;
		return this;
	},
	
	MakeMonospace:function()
	{
		return this.AddClass('monospace');
	},
	
	MakeNowrap:function()
	{
		return this.AddClass('nowrap');
	},
	
	MakeInformation:function()
	{
		return this.MakeLayout('text-info');
	},
	
	MakeError:function()
	{
		return this.MakeLayout('text-error');
	},
	
	MakeSuccess:function()
	{
		return this.MakeLayout('text-success');
	},
	
	MakeWarning:function()
	{
		return this.MakeLayout('text-warning');
	},
	
	MakeMuted:function()
	{
		return this.MakeLayout('muted');
	},
	
	MakeLink:function()
	{
		this.CursorPointer();
		return this.MakeLayout('text-link');
	},
	
	Link:function(urlOrParams, target)
	{
		var url = urlOrParams;
		if(typeof(urlOrParams) != 'string') {
			url = application.buildURL(urlOrParams);
		}
		
		this.MakeLink();
		this.SetTag('a');
		this.SetAttribute('href', url);
		
		if(!isEmpty(target)) {
			this.SetAttribute('target', target);
		}
		
		return this;
	},
	
	MakeNormal:function()
	{
		return this.MakeLayout(null);
	},
	
	MakeLayout:function(layout)
	{
		this.layout = layout;
		
		var text = this;
		$.each(this.layouts, function(idx, layout) {
			text.RemoveClass(layout);
		});
		
		if(!isEmpty(layout)) {
			this.AddClass(layout);
		}
		
		return this;
	},
	
   /**
    * Fades out the text.
    * 
    * @return {UI_Text}
    */
	FadeOut:function()
	{
		this.element().fadeOut();
		return this;
	},
	
   /**
    * Shows the text, and fades it out after a delay.
    * 
    * @param {Integer} [delay] The delay in miliseconds. Defaults to 2000 (2 seconds).
    * @return UI_Text
    */
	ShowAndFade:function(delay)
	{
		if(isEmpty(delay)) {
			delay = 2000;
		}
		
		this.Show();
		
		var text = this;
		setTimeout(
			function() {
				text.FadeOut();
			},
			delay
		);
		
		return this;
	},
	
	Handle_Click:function()
	{
		this.TriggerEvent('Click');
	},
	
	Click:function(handler)
	{
		this.CursorPointer();
		return this.AddEventHandler('Click', handler);
	},
	
	CursorPointer:function()
	{
		return this.SetCursor('pointer');
	},
	
	CursorHelp:function()
	{
		return this.SetCursor('help');
	},
	
	SetCursor:function(cursor)
	{
		this.SetStyle('cursor', 'pointer');
		return this;
	},
	
	SetIcon:function(icon)
	{
		this.icon = icon;
		
		if(this.IsRendered()) {
			this.element('icon').html(this.icon+' ').show();
		}
		
		return this;
	}
};

UI_Text = Application_RenderableHTML.extend(UI_Text);