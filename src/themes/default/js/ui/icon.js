/**
 * UI Icon handling class: offers an easy to use API
 * to create icons for common application tasks.
 *
 * @package UI
 * @subpackage Bootstrap
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @class 
 */
var UI_Icon = 
{
	'type':null,
	'prefix':null,
	'classes':[],
	'tooltip':null,
	'id':null,
	'attributes':{},
	'styles':[],
	'cursor':null,
	'postRenderAttempts':null,
	'maxPostRenderAttempts':null,
	'eventHandlers':[],
	'layout':null,

   /**
    * @constructs
    * @constructor
    */
	init:function()
	{
		this.type = null;
		this.prefix = 'fa';
		this.classes = [];
		this.tooltip = {
			'text':null,
			'placement':'top'
		};
		this.id = nextJSID();
		this.attributes = {};
		this.styles = [];
		this.cursor = null;
		this.postRenderAttempts = 0;
		this.maxPostRenderAttempts = 3;
		this.eventHandlers = {'click':null, 'doubleClick':null};
		this.layout = null;
	},

	DialogReferenceSheet:function()
    {
        application.showLoader(t('Please wait, loading icons list...'));

        var icon = this;

        application.createAJAX('GetIconsReference')
            .Success(function (data) {
                icon.Handle_IconsLoaded(data);
            })
            .Send();
    },

    Handle_IconsLoaded:function(data)
    {
		var html = ''+
		'<table class="table table-compact">'+
			'<thead>'+
				'<tr>'+
					'<th class="align-right">'+t('Name')+'</th>'+
					'<th style="width:1%">'+t('Icon')+'</th>'+
					'<th>'+t('Class name')+'</th>'+
                    '<th>'+t('Source')+'</th>'+
				'</tr>'+
			'</thead>'+
			'<tbody>';
				$.each(data, function(id, def)
                {
					var icon = UI.Icon().SetType(def.icon, def.type);

                    if(isEmpty(def.type)) {
                        def.type = 'fa';
                    }
					
					html += ''+
					'<tr>'+
						'<td class="align-right">'+icon.GetTypeMethod(id)+'</td>'+
						'<td>'+icon+'</td>'+
						'<td style="white-space:nowrap"><code>'+def.type+'-'+def.icon+'</code></td>'+
                        '<td>'+def.sourceLabel+'</td>'+
					'</tr>';
				});
			'</tbody>'+
		'</table>';
			
		application.dialogMessage(html, t('Icons reference sheet'));
	},
	
	GetTypeMethod:function(type)
	{
		return type.toLowerCase().
		replace(/_/g, ' ').
		replace(/[^\s]+/g, function(word) {
			return word.replace(/^./, function(first) {
			    return first.toUpperCase();
		  });
		}).
		replace(/ /g, '')
	},
	
	/* START METHODS */

    // region: Icon methods
    
    Actioncode:function() { return this.SetType('rocket'); },
    Activate:function() { return this.SetType('sun', 'far'); },
    Activity:function() { return this.SetType('bullhorn'); },
    Add:function() { return this.SetType('plus-circle'); },
    ApiClients:function() { return this.SetType('project-diagram', 'fas'); },
    ApiKeys:function() { return this.SetType('key', 'fas'); },
    AttentionRequired:function() { return this.SetType('exclamation-triangle'); },
    Audience:function() { return this.SetType('podcast'); },
    Back:function() { return this.SetType('arrow-circle-left'); },
    BackToCurrent:function() { return this.SetType('level-down-alt', 'fas'); },
    Backup:function() { return this.SetType('recycle'); },
    Box:function() { return this.SetType('archive'); },
    Browse:function() { return this.SetType('folder-open'); },
    Bugreport:function() { return this.SetType('bug'); },
    Build:function() { return this.SetType('magic'); },
    Business:function() { return this.SetType('university'); },
    Button:function() { return this.SetType('external-link-square-alt'); },
    Cache:function() { return this.SetType('memory', 'fas'); },
    Calendar:function() { return this.SetType('calendar'); },
    Campaigns:function() { return this.SetType('flag'); },
    Cancel:function() { return this.SetType('ban'); },
    CaretDown:function() { return this.SetType('caret-down'); },
    CaretUp:function() { return this.SetType('caret-up'); },
    Category:function() { return this.SetType('bars'); },
    ChangeOrder:function() { return this.SetType('bars'); },
    Changelog:function() { return this.SetType('edit'); },
    Check:function() { return this.SetType('check-double'); },
    Code:function() { return this.SetType('code'); },
    Collapse:function() { return this.SetType('minus-circle'); },
    CollapseLeft:function() { return this.SetType('caret-square-left'); },
    CollapseRight:function() { return this.SetType('caret-square-right'); },
    Colors:function() { return this.SetType('palette'); },
    Combination:function() { return this.SetType('object-group'); },
    Combine:function() { return this.SetType('link'); },
    CommandDeck:function() { return this.SetType('dice-d20', 'fas'); },
    Commands:function() { return this.SetType('terminal'); },
    Comment:function() { return this.SetType('comment'); },
    Comtypes:function() { return this.SetType('broadcast-tower'); },
    ContentTypes:function() { return this.SetType('elementor', 'fab'); },
    Convert:function() { return this.SetType('cogs'); },
    Copy:function() { return this.SetType('copy'); },
    Countdown:function() { return this.SetType('clock', 'far'); },
    Countries:function() { return this.SetType('flag', 'far'); },
    Css:function() { return this.SetType('css3'); },
    Csv:function() { return this.SetType('file-alt', 'far'); },
    CustomVariables:function() { return this.SetType('project-diagram'); },
    Deactivate:function() { return this.SetType('moon', 'far'); },
    Deactivated:function() { return this.SetType('moon', 'far'); },
    Delete:function() { return this.SetType('times'); },
    DeleteSign:function() { return this.SetType('times-circle', 'far'); },
    Deleted:function() { return this.SetType('times'); },
    DeselectAll:function() { return this.SetType('minus-square', 'far'); },
    Destroy:function() { return this.SetType('exclamation-triangle'); },
    Developer:function() { return this.SetType('asterisk'); },
    Disabled:function() { return this.SetType('ban'); },
    Discard:function() { return this.SetType('trash-alt', 'far'); },
    Disconnect:function() { return this.SetType('unlink'); },
    Download:function() { return this.SetType('download'); },
    Draft:function() { return this.SetType('puzzle-piece'); },
    Drag:function() { return this.SetType('bars'); },
    Dropdown:function() { return this.SetType('caret-down'); },
    Edit:function() { return this.SetType('pencil-alt', 'fas'); },
    Editor:function() { return this.SetType('cubes'); },
    Email:function() { return this.SetType('at'); },
    Enabled:function() { return this.SetType('check-circle', 'fas'); },
    Expand:function() { return this.SetType('plus-circle'); },
    ExpandLeft:function() { return this.SetType('caret-square-left'); },
    ExpandRight:function() { return this.SetType('caret-square-right'); },
    Export:function() { return this.SetType('bolt', 'fas'); },
    ExportArchive:function() { return this.SetType('archive'); },
    Featuretables:function() { return this.SetType('server'); },
    Feedback:function() { return this.SetType('thumbs-up', 'far'); },
    File:function() { return this.SetType('file-alt'); },
    Filter:function() { return this.SetType('filter'); },
    First:function() { return this.SetType('step-backward'); },
    Flat:function() { return this.SetType('th'); },
    Forward:function() { return this.SetType('arrow-circle-right'); },
    Generate:function() { return this.SetType('bolt', 'fas'); },
    Global:function() { return this.SetType('globe-europe'); },
    GlobalContent:function() { return this.SetType('cube'); },
    Grouped:function() { return this.SetType('layer-group'); },
    Help:function() { return this.SetType('question-circle'); },
    Hide:function() { return this.SetType('eye-slash'); },
    Home:function() { return this.SetType('home'); },
    Html:function() { return this.SetType('code'); },
    Id:function() { return this.SetType('key'); },
    Image:function() { return this.SetType('image'); },
    Import:function() { return this.SetType('briefcase'); },
    Inactive:function() { return this.SetType('moon', 'far'); },
    Information:function() { return this.SetType('info-circle'); },
    ItemActive:function() { return this.SetType('circle'); },
    ItemInactive:function() { return this.SetType('circle', 'far'); },
    JumpTo:function() { return this.SetType('arrow-alt-circle-right', 'far'); },
    JumpUp:function() { return this.SetType('arrow-up'); },
    Keyword:function() { return this.SetType('bookmark'); },
    Last:function() { return this.SetType('step-forward'); },
    Link:function() { return this.SetType('link'); },
    List:function() { return this.SetType('server'); },
    Load:function() { return this.SetType('folder-open', 'far'); },
    Locked:function() { return this.SetType('lock'); },
    LogIn:function() { return this.SetType('sign-in-alt', 'fas'); },
    LogOut:function() { return this.SetType('power-off'); },
    Lookup:function() { return this.SetType('ellipsis-h'); },
    MailHeaderTitle:function() { return this.SetType('heading', 'fas'); },
    MailHeaders:function() { return this.SetType('crosshairs'); },
    MailTests:function() { return this.SetType('envelope-open-text'); },
    Mails:function() { return this.SetType('envelope'); },
    Maximize:function() { return this.SetType('expand'); },
    Media:function() { return this.SetType('image'); },
    Menu:function() { return this.SetType('bars'); },
    Merge:function() { return this.SetType('level-down-alt', 'fas'); },
    Message:function() { return this.SetType('comment-alt', 'far'); },
    Minus:function() { return this.SetType('minus'); },
    Money:function() { return this.SetType('money-check-alt'); },
    Move:function() { return this.SetType('arrows-alt'); },
    MoveLeftRight:function() { return this.SetType('arrows-alt-h', 'fas'); },
    MoveTo:function() { return this.SetType('sign-out-alt', 'fas'); },
    MoveUpDown:function() { return this.SetType('arrows-alt-v', 'fas'); },
    News:function() { return this.SetType('newspaper', 'far'); },
    Next:function() { return this.SetType('chevron-right'); },
    No:function() { return this.SetType('times'); },
    NotAvailable:function() { return this.SetType('ban'); },
    NotRequired:function() { return this.SetType('minus'); },
    Notepad:function() { return this.SetType('sticky-note', 'far'); },
    Off:function() { return this.SetType('power-off'); },
    Ok:function() { return this.SetType('check'); },
    Oms:function() { return this.SetType('telegram', 'fab'); },
    On:function() { return this.SetType('dot-circle', 'far'); },
    Options:function() { return this.SetType('dot-circle', 'far'); },
    Page:function() { return this.SetType('file'); },
    Pagemodel:function() { return this.SetType('newspaper', 'far'); },
    Pause:function() { return this.SetType('pause'); },
    Pin:function() { return this.SetType('thumbtack'); },
    Play:function() { return this.SetType('play'); },
    Plus:function() { return this.SetType('plus'); },
    PositionAny:function() { return this.SetType('sort', 'fas'); },
    PositionBottom:function() { return this.SetType('arrow-circle-down'); },
    PositionTop:function() { return this.SetType('arrow-circle-up'); },
    Presets:function() { return this.SetType('server'); },
    Preview:function() { return this.SetType('file-code', 'far'); },
    Previous:function() { return this.SetType('chevron-left'); },
    Price:function() { return this.SetType('money-check-alt'); },
    Print:function() { return this.SetType('print'); },
    Printer:function() { return this.SetType('print'); },
    Product:function() { return this.SetType('shopping-basket'); },
    Proms:function() { return this.SetType('database'); },
    Proofing:function() { return this.SetType('check-square', 'far'); },
    Properties:function() { return this.SetType('cogs', 'fas'); },
    Publish:function() { return this.SetType('sign-out-alt', 'fas'); },
    Published:function() { return this.SetType('check'); },
    Rating:function() { return this.SetType('star'); },
    RecordType:function() { return this.SetType('bezier-curve'); },
    Refresh:function() { return this.SetType('sync', 'fas'); },
    Required:function() { return this.SetType('exclamation-circle'); },
    Reset:function() { return this.SetType('minus-square'); },
    Restore:function() { return this.SetType('share'); },
    Revert:function() { return this.SetType('history'); },
    Review:function() { return this.SetType('user-edit'); },
    Save:function() { return this.SetType('save'); },
    Search:function() { return this.SetType('search'); },
    SelectAll:function() { return this.SetType('plus-square', 'far'); },
    Selected:function() { return this.SetType('list'); },
    Send:function() { return this.SetType('envelope'); },
    Settings:function() { return this.SetType('wrench'); },
    Shop:function() { return this.SetType('shopping-cart'); },
    Snowflake:function() { return this.SetType('snowflake', 'far'); },
    Sort:function() { return this.SetType('sort'); },
    SortAsc:function() { return this.SetType('angle-up'); },
    SortDesc:function() { return this.SetType('angle-down'); },
    Sorting:function() { return this.SetType('sort-amount-down', 'fas'); },
    Status:function() { return this.SetType('shield-alt'); },
    Stop:function() { return this.SetType('pause'); },
    Structural:function() { return this.SetType('cubes'); },
    Suggest:function() { return this.SetType('lightbulb'); },
    Switch:function() { return this.SetType('retweet'); },
    SwitchCampaign:function() { return this.SetType('exchange-alt', 'fas'); },
    SwitchMode:function() { return this.SetType('compass'); },
    Table:function() { return this.SetType('table'); },
    Tags:function() { return this.SetType('tag', 'fas'); },
    TariffMatrix:function() { return this.SetType('table'); },
    Task:function() { return this.SetType('tasks'); },
    Template:function() { return this.SetType('file-alt', 'far'); },
    Tenant:function() { return this.SetType('award'); },
    Text:function() { return this.SetType('font'); },
    Time:function() { return this.SetType('clock'); },
    TimeTracker:function() { return this.SetType('biohazard', 'fas'); },
    Toggle:function() { return this.SetType('retweet'); },
    Tools:function() { return this.SetType('tools'); },
    Translation:function() { return this.SetType('globe'); },
    Transmission:function() { return this.SetType('satellite-dish'); },
    Tree:function() { return this.SetType('sitemap', 'fas'); },
    Uncombine:function() { return this.SetType('unlink'); },
    Uncombined:function() { return this.SetType('object-ungroup'); },
    Undelete:function() { return this.SetType('reply'); },
    Unlock:function() { return this.SetType('unlock'); },
    Unlocked:function() { return this.SetType('unlock'); },
    Upload:function() { return this.SetType('upload'); },
    User:function() { return this.SetType('user'); },
    Users:function() { return this.SetType('users'); },
    Utils:function() { return this.SetType('first-aid', 'fas'); },
    Validate:function() { return this.SetType('check-circle'); },
    Variables:function() { return this.SetType('code-branch'); },
    Variations:function() { return this.SetType('sitemap'); },
    View:function() { return this.SetType('eye'); },
    Waiting:function() { return this.SetType('clock', 'far'); },
    Warning:function() { return this.SetType('exclamation-triangle', 'fas'); },
    Whitelist:function() { return this.SetType('star', 'far'); },
    Wordwrap:function() { return this.SetType('terminal', 'fas'); },
    Workflow:function() { return this.SetType('sitemap'); },
    Xml:function() { return this.SetType('code'); },
    Yes:function() { return this.SetType('check'); },

    // endregion

    /* END METHODS */
	
	Spinner:function() 
	{
		this.SetType('spinner');
		this.MakeSpinner();
		return this;
	},
	
   /**
    * Legacy alias method.
    * @see Ok()
    */
	OK:function() 
	{ 
		return this.Ok(); 
	},
	
   /**
    * Legacy alias method.
    * @see Id()
    */
	ID:function()
	{
		return this.Id();
	},

    /**
     *
     * @param {String} type
     * @param {String|null} prefix
     * @return {UI_Icon}
     * @constructor
     */
	SetType:function(type, prefix)
	{
		if(type.length === 0) {
			type = 'exclamation-triangle';
		}

        if(isEmpty(prefix)) {
            prefix = 'fa';
        }
		
		this.type = type;
		this.prefix = prefix;
		
		return this;
	},
	
   /**
    * Adds a style that will be added to the icon
    * tag's style attribute. Do not add the ending
    * semicolon, it is added automatically.
    * 
    * @param {String} style For example "display:inline-block"
    * @return {UI_Icon}
    */
	AddStyle:function(style)
	{
		if(!this.HasStyle(style)) {
			this.styles.push(style);
		}
		
		return this;
	},
	
	HasStyle:function(style)
	{
		for(var i=0; i<this.styles.length; i++) {
			if(this.styles[i] == style) {
				return true;
			}
		}
		
		return false;
	},

   /**
    * Adds a class name that will be added to the 
    * icon tag's class attribute.
    * 
    * @param {String} className
    * @return {UI_Icon}
    */
	AddClass:function(className)
	{
		for(var i=0; i<this.classes.length; i++) {
			if(this.classes[i]==className) {
				return this;
			}
		}
		
		this.classes.push(className);
		return this;
	},
	
   /**
    * Sets the display:none style for the icon to hide it.
    * 
    * @return {UI_Icon}
    */
	MakeHidden:function()
	{
		return this.AddStyle('display', 'none');
	},
	
   /**
    * Makes the icon spin (rotate).
    * 
    * @returns {UI_Icon}
    */
	MakeSpinner:function()
	{
		return this.AddClass('fa-spin');
	},
	
   /**
    * Styles the icon as dangerous text.
    * 
    * @returns {UI_Icon}
    */
	MakeDangerous:function()
	{
		this.layout = 'text-error';
		return this;
	},
	
   /**
    * Styles the icon as muted text.
    * 
    * @returns {UI_Icon}
    */
	MakeMuted:function()
	{
		this.layout = 'muted';
		return this;
	},
	
   /**
    * Styles the icon as success text.
    * 
    * @returns {UI_Icon}
    */
	MakeSuccess:function()
	{
		this.layout = 'text-success';
		return this;
	},
	
   /**
    * Styles the icon as warning text.
    * 
    * @returns {UI_Icon}
    */
	MakeWarning:function()
	{
		this.layout = 'text-warning';
		return this;
	},
	
   /**
    * Styles the icon as information text.
    * 
    * @returns {UI_Icon}
    */
	MakeInformation:function()
	{
		this.layout = 'text-info';
		return this;
	},
	
   /**
    * Sets the text for the dynamic tooltip popup.
    * 
    * @returns {UI_Icon}
    */
	SetTooltip:function(text)
	{
		this.tooltip.text = text;
		return this;
	},

   /**
    * Alias for {@see SetTooltip()}.
    * @param {String} text
    * @returns {UI_Icon}
    */
	SetTooltipText:function(text)
	{
		return this.SetTooltip(text);
	},
	
   /**
    * Displays the help cursor when the user hovers over the icon.
    * 
    * @returns {UI_Icon}
    */
	CursorHelp:function()
	{
		return this.SetCursor('help');
	},
	
   /**
    * Displays the move cursor when the user hovers over the icon.
    * 
    * @returns {UI_Icon}
    */
	CursorMove:function()
	{
		return this.SetCursor('move');
	},
	
   /**
    * Sets the cursor to use when the user hovers over
    * the icon. This can be any of the available standard
    * CSS cursor types:
    * 
    * alias = The cursor indicates an alias of something is to be created
    * all-scroll = The cursor indicates that something can be scrolled in any direction
    * auto = Default. The browser sets a cursor
    * cell = The cursor indicates that a cell (or set of cells) may be selected
    * context-menu = The cursor indicates that a context-menu is available
    * col-resize = The cursor indicates that the column can be resized horizontally
    * copy = The cursor indicates something is to be copied
    * crosshair = The cursor render as a crosshair
    * default = The default cursor
    * e-resize = The cursor indicates that an edge of a box is to be moved right (east)
    * ew-resize = Indicates a bidirectional resize cursor
    * grab = The cursor indicates that something can be grabbed
    * grabbing = The cursor indicates that something can be grabbed
    * help = The cursor indicates that help is available
    * move = The cursor indicates something is to be moved
    * n-resize = The cursor indicates that an edge of a box is to be moved up (north)
    * ne-resize = The cursor indicates that an edge of a box is to be moved up and right (north/east)
    * nesw-resize = Indicates a bidirectional resize cursor
    * ns-resize = Indicates a bidirectional resize cursor
    * nw-resize = The cursor indicates that an edge of a box is to be moved up and left (north/west)
    * nwse-resize = Indicates a bidirectional resize cursor
    * no-drop = The cursor indicates that the dragged item cannot be dropped here
    * none = No cursor is rendered for the element
    * not-allowed = The cursor indicates that the requested action will not be executed
    * pointer = The cursor is a pointer and indicates a link
    * progress = The cursor indicates that the program is busy (in progress)
    * row-resize = The cursor indicates that the row can be resized vertically
    * s-resize = The cursor indicates that an edge of a box is to be moved down (south)
    * se-resize = The cursor indicates that an edge of a box is to be moved down and right (south/east)
    * sw-resize = The cursor indicates that an edge of a box is to be moved down and left (south/west)
    * text = The cursor indicates text that may be selected
    * vertical-text = The cursor indicates vertical-text that may be selected
    * w-resize = The cursor indicates that an edge of a box is to be moved left (west)
    * wait = The cursor indicates that the program is busy
    * zoom-in = The cursor indicates that something can be zoomed in
    * zoom-out = The cursor indicates that something can be zoomed out
    * 
    * @param {String} type
    * @returns {UI_Icon}
    */
	SetCursor:function(type)
	{
		this.cursor = type;
		return this;
	},
	
   /**
    * Sets the title attribute for the standard hover tooltip.
    * 
    * @returns {UI_Icon}
    */
	SetTitle:function(title)
	{
		this.title = title;
		return this;
	},
	
   /**
    * Sets the ID attribute.
    * 
    * @returns {UI_Icon}
    */
	SetID:function(id)
	{
		this.id = id;
		return this;
	},
	
   /**
    * Sets an attribute of the resulting <i> tag.
    * 
    * @returns {UI_Icon}
    */
	SetAttribute:function(name, value)
	{
		this.attributes[name] = value;
		return this;
	},
	
   /**
    * Checks whether the icon has a click handler set, making
    * it a clickable element.
    * 
    * @returns {Boolean}
    */
	_IsClickable:function()
	{
		if(this.eventHandlers['click'] != null || this.eventHandlers['doubleClick'] != null) {
			return true;
		}
		
		return false;
	},
	
   /**
    * Renders the required markup for the icon and returns it.
    * 
    * Note that this usually does not need to be called manually,
    * since the icon automatically renders itself if it is used in
    * a string context.
    * 
    * @returns {String}
    */
	Render:function()
	{
		// make the icon visibly clickable
		if(this._IsClickable()) {
			this.AddClass('clickable');
		}
		
		if(this.layout != null) {
			this.AddClass(this.layout);
		}
		
		this.SetAttribute('class', this.prefix+' fa-'+this.type+' '+this.classes.join(' '));
		
		if(this.cursor != null) {
			this.AddStyle('cursor:'+this.cursor);
		}
		
		if(this.styles.length > 0) {
			this.SetAttribute('style', this.styles.join(';'));
		}
		
		this._CheckTooltip();
		this._CheckTitle();
		this._CheckID();
		
		var tag = '<i '+UI.CompileAttributes(this.attributes)+'></i>';

		if(this._IsPostRenderRequired()) {
			var icon = this;
			UI.RefreshTimeout(function() {
				icon.PostRender();
			});
		}
		
		return tag;
	},
	
	DestroyIcon:function()
	{
		var el = $('#'+this.id);
		if(!isEmpty(this.tooltip.text)) {
			el.tooltip('destroy');
		}
		
		el.remove();
	},
	
	_IsPostRenderRequired:function()
	{
		if(this.tooltip.text != null) {
			return true;
		}
		
		var required = false;
		$.each(this.eventHandlers, function(name, value) {
			if(value != null) {
				required = true;
			}
		});
		
		return required;
	},

	PostRender:function()
	{
		var el = $('#'+this.id);
		el.data('icon', this);
		
		// element not present in the DOM yet? Schedule another
		// attempt a little later.
		if(el.length==0) {
			this.postRenderAttempts++;
			if(this.postRenderAttempts==this.maxPostRenderAttempts) {
				return;
			}
			
			var button = this;
			UI.RefreshTimeout(function() {
				button.PostRender();
			});
			return;
		}
		
		this._Tooltipify();

		var icon = this;

		if(this.eventHandlers['click'] != null) {
			el.click(function(domNode) {
				icon.eventHandlers['click'].call(domNode, icon);
			});
		}
		
		if(this.eventHandlers['doubleClick'] != null) {
			el.dblclick(function(domNode) {
				icon.eventHandlers['doubleClick'].call(domNode, icon);
			});
		}
	},
	
	_CheckTooltip:function()
	{
		if(this.tooltip.text==null) {
			return;
		}
		
		if(this.id==null) {
			this.id = 'jic'+nextJSID();
		}
		
		this.SetAttribute('title', this.tooltip.text);
	},
	
	_CheckTitle:function()
	{
		if(this.title!=null) {
			this.SetAttribute('title', this.title);
		}
	},
	
	_CheckID:function()
	{
		// set the id if it will be needed and hasn't been set specifically.
		if(this._IsPostRenderRequired() && this.id==null) {
			this.id = nextJSID();
		}
		
		if(this.id != null) {
			this.SetAttribute('id', this.id);
		}
	},
	
	_Tooltipify:function()
	{
		UI.MakeTooltip('#'+this.id, null, this.tooltip.placement);
	},
	
	toString:function()
	{
		return this.Render();
	},
	
   /**
    * Sets the onlick handler function that will be called
    * if the user clicks the icon. 
    * 
    * The function gets the dom node as "this", and the icon
    * instance as parameter.
    * 
    * @param {Function} handler
    */
	Click:function(handler)
	{
		this.CursorPointer();
		this.eventHandlers['click'] = handler;
		return this;
	},
	
   /**
    * Sets the doubleclick handler that will be called if the
    * user double-clicks the icon.
    * 
    * The function gets the dom node as "this", and the icon
    * instance as parameter.
    * 
    * @param {Function} handler
    */
	DoubleClick:function(handler)
	{
		this.eventHandlers['doubleClick'] = handler;
		return this;
	},
	
	Close:function()
	{
		return this.Delete();
	},

    /**
     * @returns {String|null}
     */
	GetType:function()
	{
		return this.type;
	},

    /**
     * @returns {String}
     * @constructor
     */
    GetPrefix:function()
    {
        return this.prefix;
    },
	
   /**
    * Makes the tooltip display on the bottom.
    * @returns {UI_Button}
    */
	MakeTooltipBottom:function()
	{
		return this.SetTooltipPosition('bottom');
	},
	
   /**
    * Makes the tooltip display on the left.
    * @returns {UI_Button}
    */
	MakeTooltipLeft:function()
	{
		return this.SetTooltipPosition('left');
	},

   /**
    * Makes the tooltip display on the right.
    * @returns {UI_Button}
    */
	MakeTooltipRight:function()
	{
		return this.SetTooltipPosition('right');
	},
	
   /**
    * Makes the tooltip display on the top (default).
    * @returns {UI_Button}
    */
	MakeTooltipTop:function()
	{
		return this.SetTooltipPosition('top');
	},
	
   /**
    * Sets the position of the tooltip relative to the element.
    * 
    * @param {String} position "top" (default), "left", "right", "bottom"
    * @returns {UI_Button}
    */
	SetTooltipPosition:function(position)
	{
		if(in_array(position, ['top', 'left', 'right', 'bottom'])) {
			this.tooltip.placement = position;
		}
		
		return this;
	},

   /**
    * Makes the cursor show as clickable when hovering over the icon. 
    * @returns {UI_Button}
    */
	CursorPointer:function()
	{
		return this.SetCursor('pointer');
	}
};

UI_Icon = Class.extend(UI_Icon);