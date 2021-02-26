/**
 * Implements a basic dialog to create generic dialogs without
 * extended functionality. Offers a simple API to control the
 * dialog's layout.
 * 
 * @package Application
 * @subpackage Dialogs
 * @class
 * @extends Dialog_Basic
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var Dialog_Generic = 
{
	'title':null,
	'content':null,
	
	init:function(title, content)
	{
		this._super();
		
		this.title = title;
		this.content = content;
	},
	
	GetTitle:function()
	{
		return this.title;
	},
	
	_RenderFooter:function()
	{
		
	},
	
	_RenderBody:function()
	{
		var content = this.content;
		
		if(this.GetData('page_details') == true) {
			content += this.RenderPageDetails();
		}
		
		return content;
	},
	
	RenderPageDetails:function()
	{
		var lines = this.GetData('page_info');
		if(lines == null) {
			lines = [];
		}
		
		lines.push(document.location);
		lines.push(application.getAppNameShort() + ' v'+Driver.GetVersion());
		
		return ''+
		'<div class="error-dialog-page-details">'+
			lines.join('<br>')+
		'</div>';
	},
	
	ChangeBody:function(html)
	{
		if(!this.IsReady()) {
			this.content = html;
		}
		
		return this._super(html);
	},
	
   /**
    * Adds information on the current page to the 
    * end of the dialog's content.
    * 
    * @return {Dialog_Generic}
    * @see AddPageInfo()
    */
	EnablePageDetails:function()
	{
		this.SetData('page_details', true);
		return this;
	},
	
   /**
    * Adds page-relevant information to show if
    * the page details are enabled.
    * 
    * @return  {Dialog_Generic}
    * @see EnablePageDetails()
    */
	AddPageInfo:function(text)
	{
		var info = this.GetData('page_info');
		if(info==null) {
			info = [];
		}
		
		info.push(text);
		
		this.SetData('page_info', info);
		
		return this;
	}
};

Dialog_Generic = Dialog_Basic.extend(Dialog_Generic);