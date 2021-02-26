/**
 * HTML Link helper class, used to dynamically create and render
 * links with an easy to use API. 
 *
 * @package UI
 * @class 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var UI_Link = 
{
	'label':null,
	'icon':null,
	
	init:function(label)
	{
		this._super();
		
		this.label = label;
		this.icon = null;
	},
	
   /**
    * @protected
    */
	_Render:function()
	{
		if(this.HasEventHandler('click')) {
			this.Link('javascript:void(0)');
		}
		
		return '<a '+this.RenderAttributes()+'>'+this.RenderLabel()+'</a>';
	},
	
   /**
    * @protected
    */
	_PostRender:function()
	{
		var link = this;
		
		this.element().click(function() {
			link.TriggerEvent('click');
		});
	},
	
   /**
    * Sets a click handler for the link. This supersedes
    * any link URL that may have been set.
    * 
    * @param {Function} handler
    * @returns {UI_Link}
    */
	Click:function(handler)
	{
		return this.AddEventHandler('click', handler);
	},
	
   /**
    * Sets the URL to use for the link. 
    * @param {String}|{Object} urlOrParams
    * @returns {UI_Link}
    */
	Link:function(urlOrParams)
	{
		var url = urlOrParams;
		if(typeof(urlOrParams) != 'string') {
			url = application.buildURL(urlOrParams);
		} 
		
		return this.SetAttribute('href', url);
	},

   /**
    * Makes the link external by settings its target attribute to _blank.
    * @returns {UI_Link}
    */
	MakeExternal:function()
	{
		return this.SetTarget('_blank');
	},
	
   /**
    * Sets the target of the link.
    * 
    * @param {String} targetName
    * @returns {UI_Link}
    */
	SetTarget:function(targetName)
	{
		return this.SetAttribute('target', '_blank');
	},
	
   /**
    * Sets the icon to use in the link.
    * 
    * @param {UI_Icon} icon
    * @returns {UI_Link}
    */
	SetIcon:function(icon)
	{
		this.icon = icon;
		
		if(this.IsRendered()) {
			this.element().html(this.RenderLabel());
		}
		
		return this;
	},
	
   /**
    * Renders the label, with icon if present.
    * @protected
    * @returns {String}
    */
	RenderLabel:function()
	{
		var label = this.label;
		if(!isEmpty(this.icon)) {
			label = this.icon.Render() + ' ' + label;
		}
		
		return label;
	},
	
   /**
    * Sets the label of the link. This can be used once the
    * link has been rendered to replace the label.
    * 
    * @param {String} label
    * @returns {UI_Link}
    */
	SetLabel:function(label)
	{
		this.label = label;
		
		if(this.IsRendered()) {
			this.element().html(this.RenderLabel());
		}
		
		return this;
	},
	
	_GetTypeName:function()
	{
		return 'HTML Link';
	}
};

UI_Link = Application_RenderableHTML.extend(UI_Link);