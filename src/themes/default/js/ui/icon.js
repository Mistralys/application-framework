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

    /* START TYPES */

    // region: Icon type definitions
    
    // WARNING: This list is automatically generated.
    // To modify the available icons, use the framework
    // manager's icons generation feature.

    'TYPE_ACTIONCODE':'actioncode',
    'TYPE_ACTIVATE':'activate',
    'TYPE_ACTIVITY':'activity',
    'TYPE_ADD':'add',
    'TYPE_ATTENTION_REQUIRED':'attention_required',
    'TYPE_AUDIENCE':'audience',
    'TYPE_BACK':'back',
    'TYPE_BACK_TO_CURRENT':'back_to_current',
    'TYPE_BACKUP':'backup',
    'TYPE_BOX':'box',
    'TYPE_BROWSE':'browse',
    'TYPE_BUGREPORT':'bugreport',
    'TYPE_BUILD':'build',
    'TYPE_BUSINESS':'business',
    'TYPE_BUTTON':'button',
    'TYPE_CALENDAR':'calendar',
    'TYPE_CAMPAIGNS':'campaigns',
    'TYPE_CANCEL':'cancel',
    'TYPE_CARET_DOWN':'caret_down',
    'TYPE_CARET_UP':'caret_up',
    'TYPE_CATEGORY':'category',
    'TYPE_CHANGE_ORDER':'change_order',
    'TYPE_CHANGELOG':'changelog',
    'TYPE_CHECK':'check',
    'TYPE_CODE':'code',
    'TYPE_COLLAPSE':'collapse',
    'TYPE_COLLAPSE_LEFT':'collapse_left',
    'TYPE_COLLAPSE_RIGHT':'collapse_right',
    'TYPE_COLORS':'colors',
    'TYPE_COMBINATION':'combination',
    'TYPE_COMBINE':'combine',
    'TYPE_COMMANDS':'commands',
    'TYPE_COMMENT':'comment',
    'TYPE_COMTYPES':'comtypes',
    'TYPE_CONTENT_TYPES':'content_types',
    'TYPE_CONVERT':'convert',
    'TYPE_COPY':'copy',
    'TYPE_COUNTDOWN':'countdown',
    'TYPE_COUNTRIES':'countries',
    'TYPE_CSV':'csv',
    'TYPE_CUSTOM_VARIABLES':'custom_variables',
    'TYPE_DEACTIVATE':'deactivate',
    'TYPE_DEACTIVATED':'deactivated',
    'TYPE_DELETE':'delete',
    'TYPE_DELETE_SIGN':'delete_sign',
    'TYPE_DELETED':'deleted',
    'TYPE_DESELECT_ALL':'deselect_all',
    'TYPE_DESTROY':'destroy',
    'TYPE_DEVELOPER':'developer',
    'TYPE_DISABLED':'disabled',
    'TYPE_DISCARD':'discard',
    'TYPE_DISCONNECT':'disconnect',
    'TYPE_DOWNLOAD':'download',
    'TYPE_DRAFT':'draft',
    'TYPE_DRAG':'drag',
    'TYPE_DROPDOWN':'dropdown',
    'TYPE_EDIT':'edit',
    'TYPE_EDITOR':'editor',
    'TYPE_EMAIL':'email',
    'TYPE_ENABLED':'enabled',
    'TYPE_EXPAND':'expand',
    'TYPE_EXPAND_LEFT':'expand_left',
    'TYPE_EXPAND_RIGHT':'expand_right',
    'TYPE_EXPORT':'export',
    'TYPE_EXPORT_ARCHIVE':'export_archive',
    'TYPE_FEATURETABLES':'featuretables',
    'TYPE_FEEDBACK':'feedback',
    'TYPE_FILE':'file',
    'TYPE_FILTER':'filter',
    'TYPE_FIRST':'first',
    'TYPE_FLAT':'flat',
    'TYPE_FORWARD':'forward',
    'TYPE_GENERATE':'generate',
    'TYPE_GLOBAL':'global',
    'TYPE_GLOBAL_CONTENT':'global_content',
    'TYPE_GROUPED':'grouped',
    'TYPE_HELP':'help',
    'TYPE_HIDE':'hide',
    'TYPE_HOME':'home',
    'TYPE_HTML':'html',
    'TYPE_ID':'id',
    'TYPE_IMAGE':'image',
    'TYPE_IMPORT':'import',
    'TYPE_INACTIVE':'inactive',
    'TYPE_INFORMATION':'information',
    'TYPE_ITEM_ACTIVE':'item_active',
    'TYPE_ITEM_INACTIVE':'item_inactive',
    'TYPE_JUMP_TO':'jump_to',
    'TYPE_JUMP_UP':'jump_up',
    'TYPE_KEYWORD':'keyword',
    'TYPE_LAST':'last',
    'TYPE_LINK':'link',
    'TYPE_LIST':'list',
    'TYPE_LOAD':'load',
    'TYPE_LOCKED':'locked',
    'TYPE_LOG_IN':'log_in',
    'TYPE_LOG_OUT':'log_out',
    'TYPE_LOOKUP':'lookup',
    'TYPE_MAIL_HEADER_TITLE':'mail_header_title',
    'TYPE_MAIL_HEADERS':'mail_headers',
    'TYPE_MAIL_TESTS':'mail_tests',
    'TYPE_MAILS':'mails',
    'TYPE_MAXIMIZE':'maximize',
    'TYPE_MEDIA':'media',
    'TYPE_MENU':'menu',
    'TYPE_MERGE':'merge',
    'TYPE_MESSAGE':'message',
    'TYPE_MINUS':'minus',
    'TYPE_MONEY':'money',
    'TYPE_MOVE':'move',
    'TYPE_MOVE_LEFT_RIGHT':'move_left_right',
    'TYPE_MOVE_TO':'move_to',
    'TYPE_MOVE_UP_DOWN':'move_up_down',
    'TYPE_NEXT':'next',
    'TYPE_NO':'no',
    'TYPE_NOT_AVAILABLE':'not_available',
    'TYPE_NOT_REQUIRED':'not_required',
    'TYPE_NOTEPAD':'notepad',
    'TYPE_OFF':'off',
    'TYPE_OK':'ok',
    'TYPE_OMS':'oms',
    'TYPE_ON':'on',
    'TYPE_OPTIONS':'options',
    'TYPE_PAGE':'page',
    'TYPE_PAGEMODEL':'pagemodel',
    'TYPE_PAUSE':'pause',
    'TYPE_PIN':'pin',
    'TYPE_PLAY':'play',
    'TYPE_PLUS':'plus',
    'TYPE_POSITION_ANY':'position_any',
    'TYPE_POSITION_BOTTOM':'position_bottom',
    'TYPE_POSITION_TOP':'position_top',
    'TYPE_PRESETS':'presets',
    'TYPE_PREVIEW':'preview',
    'TYPE_PREVIOUS':'previous',
    'TYPE_PRICE':'price',
    'TYPE_PRINT':'print',
    'TYPE_PRINTER':'printer',
    'TYPE_PRODUCT':'product',
    'TYPE_PROMS':'proms',
    'TYPE_PROOFING':'proofing',
    'TYPE_PROPERTIES':'properties',
    'TYPE_PUBLISH':'publish',
    'TYPE_PUBLISHED':'published',
    'TYPE_RATING':'rating',
    'TYPE_RECORD_TYPE':'record_type',
    'TYPE_REFRESH':'refresh',
    'TYPE_REQUIRED':'required',
    'TYPE_RESET':'reset',
    'TYPE_RESTORE':'restore',
    'TYPE_REVERT':'revert',
    'TYPE_REVIEW':'review',
    'TYPE_SAVE':'save',
    'TYPE_SEARCH':'search',
    'TYPE_SELECT_ALL':'select_all',
    'TYPE_SELECTED':'selected',
    'TYPE_SEND':'send',
    'TYPE_SETTINGS':'settings',
    'TYPE_SHOP':'shop',
    'TYPE_SORT':'sort',
    'TYPE_SORT_ASC':'sort_asc',
    'TYPE_SORT_DESC':'sort_desc',
    'TYPE_SORTING':'sorting',
    'TYPE_SPINNER':'spinner',
    'TYPE_STATUS':'status',
    'TYPE_STOP':'stop',
    'TYPE_STRUCTURAL':'structural',
    'TYPE_SUGGEST':'suggest',
    'TYPE_SWITCH':'switch',
    'TYPE_SWITCH_CAMPAIGN':'switch_campaign',
    'TYPE_SWITCH_MODE':'switch_mode',
    'TYPE_TABLE':'table',
    'TYPE_TARIFF_MATRIX':'tariff_matrix',
    'TYPE_TASK':'task',
    'TYPE_TEMPLATE':'template',
    'TYPE_TENANT':'tenant',
    'TYPE_TEXT':'text',
    'TYPE_TIME':'time',
    'TYPE_TOGGLE':'toggle',
    'TYPE_TOOLS':'tools',
    'TYPE_TRANSLATION':'translation',
    'TYPE_TRANSMISSION':'transmission',
    'TYPE_UNCOMBINE':'uncombine',
    'TYPE_UNCOMBINED':'uncombined',
    'TYPE_UNDELETE':'undelete',
    'TYPE_UNLOCK':'unlock',
    'TYPE_UNLOCKED':'unlocked',
    'TYPE_UPLOAD':'upload',
    'TYPE_USER':'user',
    'TYPE_USERS':'users',
    'TYPE_UTILS':'utils',
    'TYPE_VALIDATE':'validate',
    'TYPE_VARIABLES':'variables',
    'TYPE_VARIATIONS':'variations',
    'TYPE_VIEW':'view',
    'TYPE_WAITING':'waiting',
    'TYPE_WARNING':'warning',
    'TYPE_WHITELIST':'whitelist',
    'TYPE_WORDWRAP':'wordwrap',
    'TYPE_WORKFLOW':'workflow',
    'TYPE_XML':'xml',
    'TYPE_YES':'yes',

    'types':null,
    
    InitTypes:function()
    {
        this.types = {};
        
        this.types[this.TYPE_ACTIONCODE] = 'rocket';
        this.types[this.TYPE_ACTIVATE] = 'far:sun';
        this.types[this.TYPE_ACTIVITY] = 'bullhorn';
        this.types[this.TYPE_ADD] = 'plus-circle';
        this.types[this.TYPE_ATTENTION_REQUIRED] = 'exclamation-triangle';
        this.types[this.TYPE_AUDIENCE] = 'podcast';
        this.types[this.TYPE_BACK] = 'arrow-circle-left';
        this.types[this.TYPE_BACK_TO_CURRENT] = 'fas:level-down-alt';
        this.types[this.TYPE_BACKUP] = 'recycle';
        this.types[this.TYPE_BOX] = 'archive';
        this.types[this.TYPE_BROWSE] = 'folder-open';
        this.types[this.TYPE_BUGREPORT] = 'bug';
        this.types[this.TYPE_BUILD] = 'magic';
        this.types[this.TYPE_BUSINESS] = 'university';
        this.types[this.TYPE_BUTTON] = 'external-link-square-alt';
        this.types[this.TYPE_CALENDAR] = 'calendar';
        this.types[this.TYPE_CAMPAIGNS] = 'flag';
        this.types[this.TYPE_CANCEL] = 'ban';
        this.types[this.TYPE_CARET_DOWN] = 'caret-down';
        this.types[this.TYPE_CARET_UP] = 'caret-up';
        this.types[this.TYPE_CATEGORY] = 'bars';
        this.types[this.TYPE_CHANGE_ORDER] = 'bars';
        this.types[this.TYPE_CHANGELOG] = 'edit';
        this.types[this.TYPE_CHECK] = 'check-double';
        this.types[this.TYPE_CODE] = 'code';
        this.types[this.TYPE_COLLAPSE] = 'minus-circle';
        this.types[this.TYPE_COLLAPSE_LEFT] = 'caret-square-left';
        this.types[this.TYPE_COLLAPSE_RIGHT] = 'caret-square-right';
        this.types[this.TYPE_COLORS] = 'palette';
        this.types[this.TYPE_COMBINATION] = 'object-group';
        this.types[this.TYPE_COMBINE] = 'link';
        this.types[this.TYPE_COMMANDS] = 'terminal';
        this.types[this.TYPE_COMMENT] = 'comment';
        this.types[this.TYPE_COMTYPES] = 'broadcast-tower';
        this.types[this.TYPE_CONTENT_TYPES] = 'fab:elementor';
        this.types[this.TYPE_CONVERT] = 'cogs';
        this.types[this.TYPE_COPY] = 'copy';
        this.types[this.TYPE_COUNTDOWN] = 'far:clock';
        this.types[this.TYPE_COUNTRIES] = 'far:flag';
        this.types[this.TYPE_CSV] = 'far:file-alt';
        this.types[this.TYPE_CUSTOM_VARIABLES] = 'project-diagram';
        this.types[this.TYPE_DEACTIVATE] = 'far:moon';
        this.types[this.TYPE_DEACTIVATED] = 'far:moon';
        this.types[this.TYPE_DELETE] = 'times';
        this.types[this.TYPE_DELETE_SIGN] = 'far:times-circle';
        this.types[this.TYPE_DELETED] = 'times';
        this.types[this.TYPE_DESELECT_ALL] = 'far:minus-square';
        this.types[this.TYPE_DESTROY] = 'exclamation-triangle';
        this.types[this.TYPE_DEVELOPER] = 'asterisk';
        this.types[this.TYPE_DISABLED] = 'ban';
        this.types[this.TYPE_DISCARD] = 'far:trash-alt';
        this.types[this.TYPE_DISCONNECT] = 'unlink';
        this.types[this.TYPE_DOWNLOAD] = 'download';
        this.types[this.TYPE_DRAFT] = 'puzzle-piece';
        this.types[this.TYPE_DRAG] = 'bars';
        this.types[this.TYPE_DROPDOWN] = 'caret-down';
        this.types[this.TYPE_EDIT] = 'fas:pencil-alt';
        this.types[this.TYPE_EDITOR] = 'cubes';
        this.types[this.TYPE_EMAIL] = 'at';
        this.types[this.TYPE_ENABLED] = 'fas:check-circle';
        this.types[this.TYPE_EXPAND] = 'plus-circle';
        this.types[this.TYPE_EXPAND_LEFT] = 'caret-square-left';
        this.types[this.TYPE_EXPAND_RIGHT] = 'caret-square-right';
        this.types[this.TYPE_EXPORT] = 'fas:bolt';
        this.types[this.TYPE_EXPORT_ARCHIVE] = 'archive';
        this.types[this.TYPE_FEATURETABLES] = 'server';
        this.types[this.TYPE_FEEDBACK] = 'far:thumbs-up';
        this.types[this.TYPE_FILE] = 'file-alt';
        this.types[this.TYPE_FILTER] = 'filter';
        this.types[this.TYPE_FIRST] = 'step-backward';
        this.types[this.TYPE_FLAT] = 'th';
        this.types[this.TYPE_FORWARD] = 'arrow-circle-right';
        this.types[this.TYPE_GENERATE] = 'fas:bolt';
        this.types[this.TYPE_GLOBAL] = 'globe-europe';
        this.types[this.TYPE_GLOBAL_CONTENT] = 'cube';
        this.types[this.TYPE_GROUPED] = 'layer-group';
        this.types[this.TYPE_HELP] = 'question-circle';
        this.types[this.TYPE_HIDE] = 'eye-slash';
        this.types[this.TYPE_HOME] = 'home';
        this.types[this.TYPE_HTML] = 'code';
        this.types[this.TYPE_ID] = 'key';
        this.types[this.TYPE_IMAGE] = 'image';
        this.types[this.TYPE_IMPORT] = 'briefcase';
        this.types[this.TYPE_INACTIVE] = 'far:moon';
        this.types[this.TYPE_INFORMATION] = 'info-circle';
        this.types[this.TYPE_ITEM_ACTIVE] = 'circle';
        this.types[this.TYPE_ITEM_INACTIVE] = 'far:circle';
        this.types[this.TYPE_JUMP_TO] = 'far:arrow-alt-circle-right';
        this.types[this.TYPE_JUMP_UP] = 'arrow-up';
        this.types[this.TYPE_KEYWORD] = 'bookmark';
        this.types[this.TYPE_LAST] = 'step-forward';
        this.types[this.TYPE_LINK] = 'link';
        this.types[this.TYPE_LIST] = 'server';
        this.types[this.TYPE_LOAD] = 'far:folder-open';
        this.types[this.TYPE_LOCKED] = 'lock';
        this.types[this.TYPE_LOG_IN] = 'fas:sign-in-alt';
        this.types[this.TYPE_LOG_OUT] = 'power-off';
        this.types[this.TYPE_LOOKUP] = 'ellipsis-h';
        this.types[this.TYPE_MAIL_HEADER_TITLE] = 'fas:heading';
        this.types[this.TYPE_MAIL_HEADERS] = 'crosshairs';
        this.types[this.TYPE_MAIL_TESTS] = 'envelope-open-text';
        this.types[this.TYPE_MAILS] = 'envelope';
        this.types[this.TYPE_MAXIMIZE] = 'expand';
        this.types[this.TYPE_MEDIA] = 'image';
        this.types[this.TYPE_MENU] = 'bars';
        this.types[this.TYPE_MERGE] = 'fas:level-down-alt';
        this.types[this.TYPE_MESSAGE] = 'far:comment-alt';
        this.types[this.TYPE_MINUS] = 'minus';
        this.types[this.TYPE_MONEY] = 'money-check-alt';
        this.types[this.TYPE_MOVE] = 'arrows-alt';
        this.types[this.TYPE_MOVE_LEFT_RIGHT] = 'fas:arrows-alt-h';
        this.types[this.TYPE_MOVE_TO] = 'fas:sign-out-alt';
        this.types[this.TYPE_MOVE_UP_DOWN] = 'fas:arrows-alt-v';
        this.types[this.TYPE_NEXT] = 'chevron-right';
        this.types[this.TYPE_NO] = 'times';
        this.types[this.TYPE_NOT_AVAILABLE] = 'ban';
        this.types[this.TYPE_NOT_REQUIRED] = 'minus';
        this.types[this.TYPE_NOTEPAD] = 'far:sticky-note';
        this.types[this.TYPE_OFF] = 'power-off';
        this.types[this.TYPE_OK] = 'check';
        this.types[this.TYPE_OMS] = 'fab:telegram';
        this.types[this.TYPE_ON] = 'far:dot-circle';
        this.types[this.TYPE_OPTIONS] = 'far:dot-circle';
        this.types[this.TYPE_PAGE] = 'file';
        this.types[this.TYPE_PAGEMODEL] = 'far:newspaper';
        this.types[this.TYPE_PAUSE] = 'pause';
        this.types[this.TYPE_PIN] = 'thumbtack';
        this.types[this.TYPE_PLAY] = 'play';
        this.types[this.TYPE_PLUS] = 'plus';
        this.types[this.TYPE_POSITION_ANY] = 'fas:sort';
        this.types[this.TYPE_POSITION_BOTTOM] = 'arrow-circle-down';
        this.types[this.TYPE_POSITION_TOP] = 'arrow-circle-up';
        this.types[this.TYPE_PRESETS] = 'server';
        this.types[this.TYPE_PREVIEW] = 'far:file-code';
        this.types[this.TYPE_PREVIOUS] = 'chevron-left';
        this.types[this.TYPE_PRICE] = 'money-check-alt';
        this.types[this.TYPE_PRINT] = 'print';
        this.types[this.TYPE_PRINTER] = 'print';
        this.types[this.TYPE_PRODUCT] = 'shopping-basket';
        this.types[this.TYPE_PROMS] = 'database';
        this.types[this.TYPE_PROOFING] = 'far:check-square';
        this.types[this.TYPE_PROPERTIES] = 'fas:cogs';
        this.types[this.TYPE_PUBLISH] = 'fas:sign-out-alt';
        this.types[this.TYPE_PUBLISHED] = 'check';
        this.types[this.TYPE_RATING] = 'star';
        this.types[this.TYPE_RECORD_TYPE] = 'bezier-curve';
        this.types[this.TYPE_REFRESH] = 'fas:sync';
        this.types[this.TYPE_REQUIRED] = 'exclamation-circle';
        this.types[this.TYPE_RESET] = 'minus-square';
        this.types[this.TYPE_RESTORE] = 'share';
        this.types[this.TYPE_REVERT] = 'history';
        this.types[this.TYPE_REVIEW] = 'user-edit';
        this.types[this.TYPE_SAVE] = 'save';
        this.types[this.TYPE_SEARCH] = 'search';
        this.types[this.TYPE_SELECT_ALL] = 'far:plus-square';
        this.types[this.TYPE_SELECTED] = 'list';
        this.types[this.TYPE_SEND] = 'envelope';
        this.types[this.TYPE_SETTINGS] = 'wrench';
        this.types[this.TYPE_SHOP] = 'shopping-cart';
        this.types[this.TYPE_SORT] = 'sort';
        this.types[this.TYPE_SORT_ASC] = 'angle-up';
        this.types[this.TYPE_SORT_DESC] = 'angle-down';
        this.types[this.TYPE_SORTING] = 'fas:sort-amount-down';
        this.types[this.TYPE_SPINNER] = 'spinner';
        this.types[this.TYPE_STATUS] = 'shield-alt';
        this.types[this.TYPE_STOP] = 'pause';
        this.types[this.TYPE_STRUCTURAL] = 'cubes';
        this.types[this.TYPE_SUGGEST] = 'lightbulb';
        this.types[this.TYPE_SWITCH] = 'retweet';
        this.types[this.TYPE_SWITCH_CAMPAIGN] = 'fas:exchange-alt';
        this.types[this.TYPE_SWITCH_MODE] = 'compass';
        this.types[this.TYPE_TABLE] = 'table';
        this.types[this.TYPE_TARIFF_MATRIX] = 'table';
        this.types[this.TYPE_TASK] = 'tasks';
        this.types[this.TYPE_TEMPLATE] = 'far:file-alt';
        this.types[this.TYPE_TENANT] = 'award';
        this.types[this.TYPE_TEXT] = 'font';
        this.types[this.TYPE_TIME] = 'clock';
        this.types[this.TYPE_TOGGLE] = 'retweet';
        this.types[this.TYPE_TOOLS] = 'tools';
        this.types[this.TYPE_TRANSLATION] = 'globe';
        this.types[this.TYPE_TRANSMISSION] = 'satellite-dish';
        this.types[this.TYPE_UNCOMBINE] = 'unlink';
        this.types[this.TYPE_UNCOMBINED] = 'object-ungroup';
        this.types[this.TYPE_UNDELETE] = 'reply';
        this.types[this.TYPE_UNLOCK] = 'unlock';
        this.types[this.TYPE_UNLOCKED] = 'unlock';
        this.types[this.TYPE_UPLOAD] = 'upload';
        this.types[this.TYPE_USER] = 'user';
        this.types[this.TYPE_USERS] = 'users';
        this.types[this.TYPE_UTILS] = 'fas:first-aid';
        this.types[this.TYPE_VALIDATE] = 'check-circle';
        this.types[this.TYPE_VARIABLES] = 'code-branch';
        this.types[this.TYPE_VARIATIONS] = 'sitemap';
        this.types[this.TYPE_VIEW] = 'eye';
        this.types[this.TYPE_WAITING] = 'far:clock';
        this.types[this.TYPE_WARNING] = 'fas:exclamation-triangle';
        this.types[this.TYPE_WHITELIST] = 'far:star';
        this.types[this.TYPE_WORDWRAP] = 'fas:terminal';
        this.types[this.TYPE_WORKFLOW] = 'sitemap';
        this.types[this.TYPE_XML] = 'code';
        this.types[this.TYPE_YES] = 'check';
    },
    
    // endregion

    /* END TYPES */
    
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

		if(this.types == null)
		{
			this.InitTypes();
		}
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

    // region: Icon methods
    
    Actioncode:function() { return this.SetType(this.TYPE_ACTIONCODE); },
    Activate:function() { return this.SetType(this.TYPE_ACTIVATE); },
    Activity:function() { return this.SetType(this.TYPE_ACTIVITY); },
    Add:function() { return this.SetType(this.TYPE_ADD); },
    AttentionRequired:function() { return this.SetType(this.TYPE_ATTENTION_REQUIRED); },
    Audience:function() { return this.SetType(this.TYPE_AUDIENCE); },
    Back:function() { return this.SetType(this.TYPE_BACK); },
    BackToCurrent:function() { return this.SetType(this.TYPE_BACK_TO_CURRENT); },
    Backup:function() { return this.SetType(this.TYPE_BACKUP); },
    Box:function() { return this.SetType(this.TYPE_BOX); },
    Browse:function() { return this.SetType(this.TYPE_BROWSE); },
    Bugreport:function() { return this.SetType(this.TYPE_BUGREPORT); },
    Build:function() { return this.SetType(this.TYPE_BUILD); },
    Business:function() { return this.SetType(this.TYPE_BUSINESS); },
    Button:function() { return this.SetType(this.TYPE_BUTTON); },
    Calendar:function() { return this.SetType(this.TYPE_CALENDAR); },
    Campaigns:function() { return this.SetType(this.TYPE_CAMPAIGNS); },
    Cancel:function() { return this.SetType(this.TYPE_CANCEL); },
    CaretDown:function() { return this.SetType(this.TYPE_CARET_DOWN); },
    CaretUp:function() { return this.SetType(this.TYPE_CARET_UP); },
    Category:function() { return this.SetType(this.TYPE_CATEGORY); },
    ChangeOrder:function() { return this.SetType(this.TYPE_CHANGE_ORDER); },
    Changelog:function() { return this.SetType(this.TYPE_CHANGELOG); },
    Check:function() { return this.SetType(this.TYPE_CHECK); },
    Code:function() { return this.SetType(this.TYPE_CODE); },
    Collapse:function() { return this.SetType(this.TYPE_COLLAPSE); },
    CollapseLeft:function() { return this.SetType(this.TYPE_COLLAPSE_LEFT); },
    CollapseRight:function() { return this.SetType(this.TYPE_COLLAPSE_RIGHT); },
    Colors:function() { return this.SetType(this.TYPE_COLORS); },
    Combination:function() { return this.SetType(this.TYPE_COMBINATION); },
    Combine:function() { return this.SetType(this.TYPE_COMBINE); },
    Commands:function() { return this.SetType(this.TYPE_COMMANDS); },
    Comment:function() { return this.SetType(this.TYPE_COMMENT); },
    Comtypes:function() { return this.SetType(this.TYPE_COMTYPES); },
    ContentTypes:function() { return this.SetType(this.TYPE_CONTENT_TYPES); },
    Convert:function() { return this.SetType(this.TYPE_CONVERT); },
    Copy:function() { return this.SetType(this.TYPE_COPY); },
    Countdown:function() { return this.SetType(this.TYPE_COUNTDOWN); },
    Countries:function() { return this.SetType(this.TYPE_COUNTRIES); },
    Csv:function() { return this.SetType(this.TYPE_CSV); },
    CustomVariables:function() { return this.SetType(this.TYPE_CUSTOM_VARIABLES); },
    Deactivate:function() { return this.SetType(this.TYPE_DEACTIVATE); },
    Deactivated:function() { return this.SetType(this.TYPE_DEACTIVATED); },
    Delete:function() { return this.SetType(this.TYPE_DELETE); },
    DeleteSign:function() { return this.SetType(this.TYPE_DELETE_SIGN); },
    Deleted:function() { return this.SetType(this.TYPE_DELETED); },
    DeselectAll:function() { return this.SetType(this.TYPE_DESELECT_ALL); },
    Destroy:function() { return this.SetType(this.TYPE_DESTROY); },
    Developer:function() { return this.SetType(this.TYPE_DEVELOPER); },
    Disabled:function() { return this.SetType(this.TYPE_DISABLED); },
    Discard:function() { return this.SetType(this.TYPE_DISCARD); },
    Disconnect:function() { return this.SetType(this.TYPE_DISCONNECT); },
    Download:function() { return this.SetType(this.TYPE_DOWNLOAD); },
    Draft:function() { return this.SetType(this.TYPE_DRAFT); },
    Drag:function() { return this.SetType(this.TYPE_DRAG); },
    Dropdown:function() { return this.SetType(this.TYPE_DROPDOWN); },
    Edit:function() { return this.SetType(this.TYPE_EDIT); },
    Editor:function() { return this.SetType(this.TYPE_EDITOR); },
    Email:function() { return this.SetType(this.TYPE_EMAIL); },
    Enabled:function() { return this.SetType(this.TYPE_ENABLED); },
    Expand:function() { return this.SetType(this.TYPE_EXPAND); },
    ExpandLeft:function() { return this.SetType(this.TYPE_EXPAND_LEFT); },
    ExpandRight:function() { return this.SetType(this.TYPE_EXPAND_RIGHT); },
    Export:function() { return this.SetType(this.TYPE_EXPORT); },
    ExportArchive:function() { return this.SetType(this.TYPE_EXPORT_ARCHIVE); },
    Featuretables:function() { return this.SetType(this.TYPE_FEATURETABLES); },
    Feedback:function() { return this.SetType(this.TYPE_FEEDBACK); },
    File:function() { return this.SetType(this.TYPE_FILE); },
    Filter:function() { return this.SetType(this.TYPE_FILTER); },
    First:function() { return this.SetType(this.TYPE_FIRST); },
    Flat:function() { return this.SetType(this.TYPE_FLAT); },
    Forward:function() { return this.SetType(this.TYPE_FORWARD); },
    Generate:function() { return this.SetType(this.TYPE_GENERATE); },
    Global:function() { return this.SetType(this.TYPE_GLOBAL); },
    GlobalContent:function() { return this.SetType(this.TYPE_GLOBAL_CONTENT); },
    Grouped:function() { return this.SetType(this.TYPE_GROUPED); },
    Help:function() { return this.SetType(this.TYPE_HELP); },
    Hide:function() { return this.SetType(this.TYPE_HIDE); },
    Home:function() { return this.SetType(this.TYPE_HOME); },
    Html:function() { return this.SetType(this.TYPE_HTML); },
    Id:function() { return this.SetType(this.TYPE_ID); },
    Image:function() { return this.SetType(this.TYPE_IMAGE); },
    Import:function() { return this.SetType(this.TYPE_IMPORT); },
    Inactive:function() { return this.SetType(this.TYPE_INACTIVE); },
    Information:function() { return this.SetType(this.TYPE_INFORMATION); },
    ItemActive:function() { return this.SetType(this.TYPE_ITEM_ACTIVE); },
    ItemInactive:function() { return this.SetType(this.TYPE_ITEM_INACTIVE); },
    JumpTo:function() { return this.SetType(this.TYPE_JUMP_TO); },
    JumpUp:function() { return this.SetType(this.TYPE_JUMP_UP); },
    Keyword:function() { return this.SetType(this.TYPE_KEYWORD); },
    Last:function() { return this.SetType(this.TYPE_LAST); },
    Link:function() { return this.SetType(this.TYPE_LINK); },
    List:function() { return this.SetType(this.TYPE_LIST); },
    Load:function() { return this.SetType(this.TYPE_LOAD); },
    Locked:function() { return this.SetType(this.TYPE_LOCKED); },
    LogIn:function() { return this.SetType(this.TYPE_LOG_IN); },
    LogOut:function() { return this.SetType(this.TYPE_LOG_OUT); },
    Lookup:function() { return this.SetType(this.TYPE_LOOKUP); },
    MailHeaderTitle:function() { return this.SetType(this.TYPE_MAIL_HEADER_TITLE); },
    MailHeaders:function() { return this.SetType(this.TYPE_MAIL_HEADERS); },
    MailTests:function() { return this.SetType(this.TYPE_MAIL_TESTS); },
    Mails:function() { return this.SetType(this.TYPE_MAILS); },
    Maximize:function() { return this.SetType(this.TYPE_MAXIMIZE); },
    Media:function() { return this.SetType(this.TYPE_MEDIA); },
    Menu:function() { return this.SetType(this.TYPE_MENU); },
    Merge:function() { return this.SetType(this.TYPE_MERGE); },
    Message:function() { return this.SetType(this.TYPE_MESSAGE); },
    Minus:function() { return this.SetType(this.TYPE_MINUS); },
    Money:function() { return this.SetType(this.TYPE_MONEY); },
    Move:function() { return this.SetType(this.TYPE_MOVE); },
    MoveLeftRight:function() { return this.SetType(this.TYPE_MOVE_LEFT_RIGHT); },
    MoveTo:function() { return this.SetType(this.TYPE_MOVE_TO); },
    MoveUpDown:function() { return this.SetType(this.TYPE_MOVE_UP_DOWN); },
    Next:function() { return this.SetType(this.TYPE_NEXT); },
    No:function() { return this.SetType(this.TYPE_NO); },
    NotAvailable:function() { return this.SetType(this.TYPE_NOT_AVAILABLE); },
    NotRequired:function() { return this.SetType(this.TYPE_NOT_REQUIRED); },
    Notepad:function() { return this.SetType(this.TYPE_NOTEPAD); },
    Off:function() { return this.SetType(this.TYPE_OFF); },
    Ok:function() { return this.SetType(this.TYPE_OK); },
    Oms:function() { return this.SetType(this.TYPE_OMS); },
    On:function() { return this.SetType(this.TYPE_ON); },
    Options:function() { return this.SetType(this.TYPE_OPTIONS); },
    Page:function() { return this.SetType(this.TYPE_PAGE); },
    Pagemodel:function() { return this.SetType(this.TYPE_PAGEMODEL); },
    Pause:function() { return this.SetType(this.TYPE_PAUSE); },
    Pin:function() { return this.SetType(this.TYPE_PIN); },
    Play:function() { return this.SetType(this.TYPE_PLAY); },
    Plus:function() { return this.SetType(this.TYPE_PLUS); },
    PositionAny:function() { return this.SetType(this.TYPE_POSITION_ANY); },
    PositionBottom:function() { return this.SetType(this.TYPE_POSITION_BOTTOM); },
    PositionTop:function() { return this.SetType(this.TYPE_POSITION_TOP); },
    Presets:function() { return this.SetType(this.TYPE_PRESETS); },
    Preview:function() { return this.SetType(this.TYPE_PREVIEW); },
    Previous:function() { return this.SetType(this.TYPE_PREVIOUS); },
    Price:function() { return this.SetType(this.TYPE_PRICE); },
    Print:function() { return this.SetType(this.TYPE_PRINT); },
    Printer:function() { return this.SetType(this.TYPE_PRINTER); },
    Product:function() { return this.SetType(this.TYPE_PRODUCT); },
    Proms:function() { return this.SetType(this.TYPE_PROMS); },
    Proofing:function() { return this.SetType(this.TYPE_PROOFING); },
    Properties:function() { return this.SetType(this.TYPE_PROPERTIES); },
    Publish:function() { return this.SetType(this.TYPE_PUBLISH); },
    Published:function() { return this.SetType(this.TYPE_PUBLISHED); },
    Rating:function() { return this.SetType(this.TYPE_RATING); },
    RecordType:function() { return this.SetType(this.TYPE_RECORD_TYPE); },
    Refresh:function() { return this.SetType(this.TYPE_REFRESH); },
    Required:function() { return this.SetType(this.TYPE_REQUIRED); },
    Reset:function() { return this.SetType(this.TYPE_RESET); },
    Restore:function() { return this.SetType(this.TYPE_RESTORE); },
    Revert:function() { return this.SetType(this.TYPE_REVERT); },
    Review:function() { return this.SetType(this.TYPE_REVIEW); },
    Save:function() { return this.SetType(this.TYPE_SAVE); },
    Search:function() { return this.SetType(this.TYPE_SEARCH); },
    SelectAll:function() { return this.SetType(this.TYPE_SELECT_ALL); },
    Selected:function() { return this.SetType(this.TYPE_SELECTED); },
    Send:function() { return this.SetType(this.TYPE_SEND); },
    Settings:function() { return this.SetType(this.TYPE_SETTINGS); },
    Shop:function() { return this.SetType(this.TYPE_SHOP); },
    Sort:function() { return this.SetType(this.TYPE_SORT); },
    SortAsc:function() { return this.SetType(this.TYPE_SORT_ASC); },
    SortDesc:function() { return this.SetType(this.TYPE_SORT_DESC); },
    Sorting:function() { return this.SetType(this.TYPE_SORTING); },
    Status:function() { return this.SetType(this.TYPE_STATUS); },
    Stop:function() { return this.SetType(this.TYPE_STOP); },
    Structural:function() { return this.SetType(this.TYPE_STRUCTURAL); },
    Suggest:function() { return this.SetType(this.TYPE_SUGGEST); },
    Switch:function() { return this.SetType(this.TYPE_SWITCH); },
    SwitchCampaign:function() { return this.SetType(this.TYPE_SWITCH_CAMPAIGN); },
    SwitchMode:function() { return this.SetType(this.TYPE_SWITCH_MODE); },
    Table:function() { return this.SetType(this.TYPE_TABLE); },
    TariffMatrix:function() { return this.SetType(this.TYPE_TARIFF_MATRIX); },
    Task:function() { return this.SetType(this.TYPE_TASK); },
    Template:function() { return this.SetType(this.TYPE_TEMPLATE); },
    Tenant:function() { return this.SetType(this.TYPE_TENANT); },
    Text:function() { return this.SetType(this.TYPE_TEXT); },
    Time:function() { return this.SetType(this.TYPE_TIME); },
    Toggle:function() { return this.SetType(this.TYPE_TOGGLE); },
    Tools:function() { return this.SetType(this.TYPE_TOOLS); },
    Translation:function() { return this.SetType(this.TYPE_TRANSLATION); },
    Transmission:function() { return this.SetType(this.TYPE_TRANSMISSION); },
    Uncombine:function() { return this.SetType(this.TYPE_UNCOMBINE); },
    Uncombined:function() { return this.SetType(this.TYPE_UNCOMBINED); },
    Undelete:function() { return this.SetType(this.TYPE_UNDELETE); },
    Unlock:function() { return this.SetType(this.TYPE_UNLOCK); },
    Unlocked:function() { return this.SetType(this.TYPE_UNLOCKED); },
    Upload:function() { return this.SetType(this.TYPE_UPLOAD); },
    User:function() { return this.SetType(this.TYPE_USER); },
    Users:function() { return this.SetType(this.TYPE_USERS); },
    Utils:function() { return this.SetType(this.TYPE_UTILS); },
    Validate:function() { return this.SetType(this.TYPE_VALIDATE); },
    Variables:function() { return this.SetType(this.TYPE_VARIABLES); },
    Variations:function() { return this.SetType(this.TYPE_VARIATIONS); },
    View:function() { return this.SetType(this.TYPE_VIEW); },
    Waiting:function() { return this.SetType(this.TYPE_WAITING); },
    Warning:function() { return this.SetType(this.TYPE_WARNING); },
    Whitelist:function() { return this.SetType(this.TYPE_WHITELIST); },
    Wordwrap:function() { return this.SetType(this.TYPE_WORDWRAP); },
    Workflow:function() { return this.SetType(this.TYPE_WORKFLOW); },
    Xml:function() { return this.SetType(this.TYPE_XML); },
    Yes:function() { return this.SetType(this.TYPE_YES); },

    // endregion

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