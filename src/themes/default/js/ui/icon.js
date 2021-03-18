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

	'types':{
        'ACTIONCODE':'rocket',
        'ACTIVATE':'far:sun',
        'ACTIVITY':'bullhorn',
        'ADD':'plus-circle',
        'ATTENTION_REQUIRED':'exclamation-triangle',
        'AUDIENCE':'podcast',
        'BACK':'arrow-circle-left',
        'BACKUP':'recycle',
        'BACK_TO_CURRENT':'fas:level-down-alt',
        'BOX':'archive',
        'BROWSE':'folder-open',
        'BUGREPORT':'bug',
        'BUILD':'magic',
        'BUSINESS':'university',
        'BUTTON':'external-link-square-alt',
        'CALENDAR':'calendar',
        'CAMPAIGNS':'flag',
        'CANCEL':'ban',
        'CARET_DOWN':'caret-down',
        'CARET_UP':'caret-up',
        'CATEGORY':'bars',
        'CHANGELOG':'edit',
        'CHANGE_ORDER':'bars',
        'CHECK':'check-double',
        'CODE':'code',
        'COLLAPSE':'minus-circle',
        'COLLAPSE_LEFT':'caret-square-left',
        'COLLAPSE_RIGHT':'caret-square-right',
        'COLORS':'palette',
        'COMBINATION':'object-group',
        'COMBINE':'link',
        'COMMANDS':'terminal',
        'COMMENT':'comment',
        'COMTYPES':'broadcast-tower',
        'CONVERT':'cogs',
        'COPY':'copy',
        'COUNTDOWN':'far:clock',
        'COUNTRIES':'far:flag',
        'CSV':'far:file-alt',
        'CUSTOM_VARIABLES':'project-diagram',
        'DEACTIVATE':'far:moon',
        'DEACTIVATED':'far:moon',
        'DELETE':'times',
        'DELETED':'times',
        'DELETE_SIGN':'far:times-circle',
        'DESELECT_ALL':'far:minus-square',
        'DESTROY':'exclamation-triangle',
        'DEVELOPER':'asterisk',
        'DISABLED':'ban',
        'DISCARD':'far:trash-alt',
        'DISCONNECT':'unlink',
        'DOWNLOAD':'download',
        'DRAFT':'puzzle-piece',
        'DRAG':'bars',
        'DROPDOWN':'caret-down',
        'EDIT':'fas:pencil-alt',
        'EDITOR':'cubes',
        'EMAIL':'at',
        'ENABLED':'check',
        'EXPAND':'plus-circle',
        'EXPAND_LEFT':'caret-square-left',
        'EXPAND_RIGHT':'caret-square-right',
        'EXPORT':'fas:bolt',
        'EXPORT_ARCHIVE':'archive',
        'FEATURETABLES':'server',
        'FEEDBACK':'far:thumbs-up',
        'FILE':'file-alt',
        'FILTER':'filter',
        'FIRST':'step-backward',
        'FLAT':'th',
        'FORWARD':'arrow-circle-right',
        'GENERATE':'fas:bolt',
        'GLOBAL':'globe-europe',
        'GLOBAL_CONTENT':'cube',
        'GROUPED':'layer-group',
        'HELP':'question-circle',
        'HIDE':'eye-slash',
        'HOME':'home',
        'HTML':'code',
        'ID':'key',
        'IMAGE':'image',
        'IMPORT':'briefcase',
        'INACTIVE':'far:moon',
        'INFORMATION':'info-circle',
        'ITEM_ACTIVE':'circle',
        'ITEM_INACTIVE':'far:circle',
        'JUMP_TO':'far:arrow-alt-circle-right',
        'JUMP_UP':'arrow-up',
        'KEYWORD':'bookmark',
        'LAST':'step-forward',
        'LINK':'link',
        'LIST':'server',
        'LOAD':'far:folder-open',
        'LOCKED':'lock',
        'LOG_IN':'fas:sign-in-alt',
        'LOG_OUT':'power-off',
        'LOOKUP':'ellipsis-h',
        'MAILS':'envelope',
        'MAIL_HEADERS':'crosshairs',
        'MAIL_HEADER_TITLE':'fas:heading',
        'MAIL_TESTS':'envelope-open-text',
        'MAXIMIZE':'expand',
        'MEDIA':'image',
        'MENU':'bars',
        'MERGE':'fas:level-down-alt',
        'MESSAGE':'far:comment-alt',
        'MINUS':'minus',
        'MONEY':'money-check-alt',
        'MOVE':'arrows-alt',
        'MOVE_LEFT_RIGHT':'fas:arrows-alt-h',
        'MOVE_TO':'fas:sign-out-alt',
        'MOVE_UP_DOWN':'fas:arrows-alt-v',
        'NEXT':'chevron-right',
        'NO':'times',
        'NOT_AVAILABLE':'ban',
        'NOT_REQUIRED':'minus',
        'OK':'check',
        'OMS':'fab:telegram',
        'OPTIONS':'far:dot-circle',
        'PAGE':'file',
        'PAGEMODEL':'far:newspaper',
        'PAUSE':'pause',
        'PLAY':'play',
        'PLUS':'plus',
        'POSITION_ANY':'fas:sort',
        'POSITION_BOTTOM':'arrow-circle-down',
        'POSITION_TOP':'arrow-circle-up',
        'PRESETS':'server',
        'PREVIEW':'far:file-code',
        'PREVIOUS':'chevron-left',
        'PRICE':'money-check-alt',
        'PRINT':'print',
        'PRINTER':'print',
        'PRODUCT':'shopping-basket',
        'PROMS':'database',
        'PROOFING':'far:check-square',
        'PROPERTIES':'fas:cogs',
        'PUBLISH':'fas:sign-out-alt',
        'PUBLISHED':'check',
        'RATING':'star',
        'REFRESH':'fas:sync',
        'REQUIRED':'exclamation-circle',
        'RESET':'minus-square',
        'RESTORE':'share',
        'REVERT':'history',
        'REVIEW':'user-edit',
        'SAVE':'save',
        'SEARCH':'search',
        'SELECTED':'list',
        'SELECT_ALL':'far:plus-square',
        'SEND':'envelope',
        'SETTINGS':'wrench',
        'SHOP':'shopping-cart',
        'SORT':'sort',
        'SORTING':'fas:sort-amount-down',
        'SORT_ASC':'angle-up',
        'SORT_DESC':'angle-down',
        'SPINNER':'spinner',
        'STATUS':'shield-alt',
        'STOP':'pause',
        'STRUCTURAL':'cubes',
        'SUGGEST':'lightbulb',
        'SWITCH':'retweet',
        'SWITCH_CAMPAIGN':'fas:exchange-alt',
        'SWITCH_MODE':'compass',
        'TABLE':'table',
        'TARIFF_MATRIX':'table',
        'TASK':'tasks',
        'TEMPLATE':'far:file-alt',
        'TENANT':'award',
        'TEXT':'font',
        'TIME':'clock',
        'TOGGLE':'retweet',
        'TOOLS':'tools',
        'TRANSLATION':'globe',
        'UNCOMBINE':'unlink',
        'UNCOMBINED':'object-ungroup',
        'UNDELETE':'reply',
        'UNLOCK':'unlock',
        'UNLOCKED':'unlock',
        'UPLOAD':'upload',
        'USER':'user',
        'USERS':'users',
        'UTILS':'fas:first-aid',
        'VALIDATE':'check-circle',
        'VARIABLES':'code-branch',
        'VARIATIONS':'sitemap',
        'VIEW':'eye',
        'WAITING':'far:clock',
        'WARNING':'fas:exclamation-triangle',
        'WHITELIST':'far:star',
        'WORDWRAP':'fas:terminal',
        'WORKFLOW':'sitemap',
        'XML':'code',
        'YES':'check'
    },
    
   /**
    * @constructs
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
		var types = [];
		$.each(this.types, function(typeName, iconName) {
			types.push({
				'type':typeName,
				'icon':iconName
			});
		}); 
		
		types.sort(function(a, b) {
			if(a.type > b.type) { return 1; }
			if(b.type > a.type) { return -1; }
			return 0;
		});
		
		var html = ''+
		'<table class="table table-compact">'+
			'<thead>'+
				'<tr>'+
					'<th class="align-right">'+t('Name')+'</th>'+
					'<th style="width:100%">'+t('Icon')+'</th>'+
					'<th style="width:100%">FontAwesome name</th>'+
				'</tr>'+
			'</thead>'+
			'<tbody>';
				$.each(types, function(idx, type) {
					var icon = UI.Icon().SetType(type.type);
					
					html += ''+
					'<tr>'+
						'<td class="align-right">'+icon.GetTypeMethod(type.type)+'</td>'+
						'<td>'+icon+'</td>'+
						'<td style="white-space:nowrap">'+icon.prefix+' fa-'+icon.type+'</td>'+
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
    Actioncode:function() { return this.SetType('ACTIONCODE'); },
    Activate:function() { return this.SetType('ACTIVATE'); },
    Activity:function() { return this.SetType('ACTIVITY'); },
    Add:function() { return this.SetType('ADD'); },
    AttentionRequired:function() { return this.SetType('ATTENTION_REQUIRED'); },
    Audience:function() { return this.SetType('AUDIENCE'); },
    Back:function() { return this.SetType('BACK'); },
    Backup:function() { return this.SetType('BACKUP'); },
    BackToCurrent:function() { return this.SetType('BACK_TO_CURRENT'); },
    Box:function() { return this.SetType('BOX'); },
    Browse:function() { return this.SetType('BROWSE'); },
    Bugreport:function() { return this.SetType('BUGREPORT'); },
    Build:function() { return this.SetType('BUILD'); },
    Business:function() { return this.SetType('BUSINESS'); },
    Button:function() { return this.SetType('BUTTON'); },
    Calendar:function() { return this.SetType('CALENDAR'); },
    Campaigns:function() { return this.SetType('CAMPAIGNS'); },
    Cancel:function() { return this.SetType('CANCEL'); },
    CaretDown:function() { return this.SetType('CARET_DOWN'); },
    CaretUp:function() { return this.SetType('CARET_UP'); },
    Category:function() { return this.SetType('CATEGORY'); },
    Changelog:function() { return this.SetType('CHANGELOG'); },
    ChangeOrder:function() { return this.SetType('CHANGE_ORDER'); },
    Check:function() { return this.SetType('CHECK'); },
    Code:function() { return this.SetType('CODE'); },
    Collapse:function() { return this.SetType('COLLAPSE'); },
    CollapseLeft:function() { return this.SetType('COLLAPSE_LEFT'); },
    CollapseRight:function() { return this.SetType('COLLAPSE_RIGHT'); },
    Colors:function() { return this.SetType('COLORS'); },
    Combination:function() { return this.SetType('COMBINATION'); },
    Combine:function() { return this.SetType('COMBINE'); },
    Commands:function() { return this.SetType('COMMANDS'); },
    Comment:function() { return this.SetType('COMMENT'); },
    Comtypes:function() { return this.SetType('COMTYPES'); },
    Convert:function() { return this.SetType('CONVERT'); },
    Copy:function() { return this.SetType('COPY'); },
    Countdown:function() { return this.SetType('COUNTDOWN'); },
    Countries:function() { return this.SetType('COUNTRIES'); },
    Csv:function() { return this.SetType('CSV'); },
    CustomVariables:function() { return this.SetType('CUSTOM_VARIABLES'); },
    Deactivate:function() { return this.SetType('DEACTIVATE'); },
    Deactivated:function() { return this.SetType('DEACTIVATED'); },
    Delete:function() { return this.SetType('DELETE'); },
    Deleted:function() { return this.SetType('DELETED'); },
    DeleteSign:function() { return this.SetType('DELETE_SIGN'); },
    DeselectAll:function() { return this.SetType('DESELECT_ALL'); },
    Destroy:function() { return this.SetType('DESTROY'); },
    Developer:function() { return this.SetType('DEVELOPER'); },
    Disabled:function() { return this.SetType('DISABLED'); },
    Discard:function() { return this.SetType('DISCARD'); },
    Disconnect:function() { return this.SetType('DISCONNECT'); },
    Download:function() { return this.SetType('DOWNLOAD'); },
    Draft:function() { return this.SetType('DRAFT'); },
    Drag:function() { return this.SetType('DRAG'); },
    Dropdown:function() { return this.SetType('DROPDOWN'); },
    Edit:function() { return this.SetType('EDIT'); },
    Editor:function() { return this.SetType('EDITOR'); },
    Email:function() { return this.SetType('EMAIL'); },
    Enabled:function() { return this.SetType('ENABLED'); },
    Expand:function() { return this.SetType('EXPAND'); },
    ExpandLeft:function() { return this.SetType('EXPAND_LEFT'); },
    ExpandRight:function() { return this.SetType('EXPAND_RIGHT'); },
    Export:function() { return this.SetType('EXPORT'); },
    ExportArchive:function() { return this.SetType('EXPORT_ARCHIVE'); },
    Featuretables:function() { return this.SetType('FEATURETABLES'); },
    Feedback:function() { return this.SetType('FEEDBACK'); },
    File:function() { return this.SetType('FILE'); },
    Filter:function() { return this.SetType('FILTER'); },
    First:function() { return this.SetType('FIRST'); },
    Flat:function() { return this.SetType('FLAT'); },
    Forward:function() { return this.SetType('FORWARD'); },
    Generate:function() { return this.SetType('GENERATE'); },
    Global:function() { return this.SetType('GLOBAL'); },
    GlobalContent:function() { return this.SetType('GLOBAL_CONTENT'); },
    Grouped:function() { return this.SetType('GROUPED'); },
    Help:function() { return this.SetType('HELP'); },
    Hide:function() { return this.SetType('HIDE'); },
    Home:function() { return this.SetType('HOME'); },
    Html:function() { return this.SetType('HTML'); },
    Id:function() { return this.SetType('ID'); },
    Image:function() { return this.SetType('IMAGE'); },
    Import:function() { return this.SetType('IMPORT'); },
    Inactive:function() { return this.SetType('INACTIVE'); },
    Information:function() { return this.SetType('INFORMATION'); },
    ItemActive:function() { return this.SetType('ITEM_ACTIVE'); },
    ItemInactive:function() { return this.SetType('ITEM_INACTIVE'); },
    JumpTo:function() { return this.SetType('JUMP_TO'); },
    JumpUp:function() { return this.SetType('JUMP_UP'); },
    Keyword:function() { return this.SetType('KEYWORD'); },
    Last:function() { return this.SetType('LAST'); },
    Link:function() { return this.SetType('LINK'); },
    List:function() { return this.SetType('LIST'); },
    Load:function() { return this.SetType('LOAD'); },
    Locked:function() { return this.SetType('LOCKED'); },
    LogIn:function() { return this.SetType('LOG_IN'); },
    LogOut:function() { return this.SetType('LOG_OUT'); },
    Lookup:function() { return this.SetType('LOOKUP'); },
    Mails:function() { return this.SetType('MAILS'); },
    MailHeaders:function() { return this.SetType('MAIL_HEADERS'); },
    MailHeaderTitle:function() { return this.SetType('MAIL_HEADER_TITLE'); },
    MailTests:function() { return this.SetType('MAIL_TESTS'); },
    Maximize:function() { return this.SetType('MAXIMIZE'); },
    Media:function() { return this.SetType('MEDIA'); },
    Menu:function() { return this.SetType('MENU'); },
    Merge:function() { return this.SetType('MERGE'); },
    Message:function() { return this.SetType('MESSAGE'); },
    Minus:function() { return this.SetType('MINUS'); },
    Money:function() { return this.SetType('MONEY'); },
    Move:function() { return this.SetType('MOVE'); },
    MoveLeftRight:function() { return this.SetType('MOVE_LEFT_RIGHT'); },
    MoveTo:function() { return this.SetType('MOVE_TO'); },
    MoveUpDown:function() { return this.SetType('MOVE_UP_DOWN'); },
    Next:function() { return this.SetType('NEXT'); },
    No:function() { return this.SetType('NO'); },
    NotAvailable:function() { return this.SetType('NOT_AVAILABLE'); },
    NotRequired:function() { return this.SetType('NOT_REQUIRED'); },
    Ok:function() { return this.SetType('OK'); },
    Oms:function() { return this.SetType('OMS'); },
    Options:function() { return this.SetType('OPTIONS'); },
    Page:function() { return this.SetType('PAGE'); },
    Pagemodel:function() { return this.SetType('PAGEMODEL'); },
    Pause:function() { return this.SetType('PAUSE'); },
    Play:function() { return this.SetType('PLAY'); },
    Plus:function() { return this.SetType('PLUS'); },
    PositionAny:function() { return this.SetType('POSITION_ANY'); },
    PositionBottom:function() { return this.SetType('POSITION_BOTTOM'); },
    PositionTop:function() { return this.SetType('POSITION_TOP'); },
    Presets:function() { return this.SetType('PRESETS'); },
    Preview:function() { return this.SetType('PREVIEW'); },
    Previous:function() { return this.SetType('PREVIOUS'); },
    Price:function() { return this.SetType('PRICE'); },
    Print:function() { return this.SetType('PRINT'); },
    Printer:function() { return this.SetType('PRINTER'); },
    Product:function() { return this.SetType('PRODUCT'); },
    Proms:function() { return this.SetType('PROMS'); },
    Proofing:function() { return this.SetType('PROOFING'); },
    Properties:function() { return this.SetType('PROPERTIES'); },
    Publish:function() { return this.SetType('PUBLISH'); },
    Published:function() { return this.SetType('PUBLISHED'); },
    Rating:function() { return this.SetType('RATING'); },
    Refresh:function() { return this.SetType('REFRESH'); },
    Required:function() { return this.SetType('REQUIRED'); },
    Reset:function() { return this.SetType('RESET'); },
    Restore:function() { return this.SetType('RESTORE'); },
    Revert:function() { return this.SetType('REVERT'); },
    Review:function() { return this.SetType('REVIEW'); },
    Save:function() { return this.SetType('SAVE'); },
    Search:function() { return this.SetType('SEARCH'); },
    Selected:function() { return this.SetType('SELECTED'); },
    SelectAll:function() { return this.SetType('SELECT_ALL'); },
    Send:function() { return this.SetType('SEND'); },
    Settings:function() { return this.SetType('SETTINGS'); },
    Shop:function() { return this.SetType('SHOP'); },
    Sort:function() { return this.SetType('SORT'); },
    Sorting:function() { return this.SetType('SORTING'); },
    SortAsc:function() { return this.SetType('SORT_ASC'); },
    SortDesc:function() { return this.SetType('SORT_DESC'); },
    Status:function() { return this.SetType('STATUS'); },
    Stop:function() { return this.SetType('STOP'); },
    Structural:function() { return this.SetType('STRUCTURAL'); },
    Suggest:function() { return this.SetType('SUGGEST'); },
    Switch:function() { return this.SetType('SWITCH'); },
    SwitchCampaign:function() { return this.SetType('SWITCH_CAMPAIGN'); },
    SwitchMode:function() { return this.SetType('SWITCH_MODE'); },
    Table:function() { return this.SetType('TABLE'); },
    TariffMatrix:function() { return this.SetType('TARIFF_MATRIX'); },
    Task:function() { return this.SetType('TASK'); },
    Template:function() { return this.SetType('TEMPLATE'); },
    Tenant:function() { return this.SetType('TENANT'); },
    Text:function() { return this.SetType('TEXT'); },
    Time:function() { return this.SetType('TIME'); },
    Toggle:function() { return this.SetType('TOGGLE'); },
    Tools:function() { return this.SetType('TOOLS'); },
    Translation:function() { return this.SetType('TRANSLATION'); },
    Uncombine:function() { return this.SetType('UNCOMBINE'); },
    Uncombined:function() { return this.SetType('UNCOMBINED'); },
    Undelete:function() { return this.SetType('UNDELETE'); },
    Unlock:function() { return this.SetType('UNLOCK'); },
    Unlocked:function() { return this.SetType('UNLOCKED'); },
    Upload:function() { return this.SetType('UPLOAD'); },
    User:function() { return this.SetType('USER'); },
    Users:function() { return this.SetType('USERS'); },
    Utils:function() { return this.SetType('UTILS'); },
    Validate:function() { return this.SetType('VALIDATE'); },
    Variables:function() { return this.SetType('VARIABLES'); },
    Variations:function() { return this.SetType('VARIATIONS'); },
    View:function() { return this.SetType('VIEW'); },
    Waiting:function() { return this.SetType('WAITING'); },
    Warning:function() { return this.SetType('WARNING'); },
    Whitelist:function() { return this.SetType('WHITELIST'); },
    Wordwrap:function() { return this.SetType('WORDWRAP'); },
    Workflow:function() { return this.SetType('WORKFLOW'); },
    Xml:function() { return this.SetType('XML'); },
    Yes:function() { return this.SetType('YES'); },
    /* END METHODS */
	
	Spinner:function() 
	{
		this.SetType('SPINNER');
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
	
	SetType:function(type)
	{
		if(typeof(this.types[type]) == 'undefined') {
			console.log('Unknown type: ['+type+'].');
			return this;
		}
		
		this.type = this.types[type];
		this.prefix = 'fa';
		
		idx = this.type.indexOf(':');
		if(idx > -1) {
			this.prefix = this.type.substring(0, idx);
			this.type = this.type.substring(idx+1);
		}
		
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
    * Makes the icon spin (roate).
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
	
	GetType:function()
	{
		var current = this.type;
		var found = null;
		$.each(this.types, function(internalName, faName) {
			if(faName == current) {
				found = internalName;
				return false;
			}
		});
		
		return found;
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