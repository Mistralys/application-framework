<?php
/**
 * File containing the class {@see UI_Icon}.
 *
 * @package User Interface
 * @see UI_Icon
 */

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\Interface_Stringable;

/**
 * Icon class used to display FontAwesome icons in the
 * user interface. Convertable to string, the class generates
 * the according HTML code to display the selected icon.
 *
 * @package User Interface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Icon implements Interface_Stringable, UI_Renderable_Interface
{
    public const ERROR_INVALID_TYPE_SELECTED = 95601;
    public const ERROR_INVALID_COLOR_STYLE = 95602;
    public const ERROR_INVALID_TOOLTIP_POSITION = 95603;

    use UI_Traits_RenderableGeneric;

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string[]
     */
    protected $classes = array();

    /**
     * @var array{text:string,placement:string}
     */
    protected $tooltip = array(
        'text' => '',
        'placement' => 'top'
    );

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array<string,string|number>
     */
    protected $attributes = array();

    /**
     * @var string
     */
    protected $prefix = 'fa';

    /* START TYPES */

    // region: Icon type definitions
    
    // WARNING: This list is automatically generated.
    // To modify the available icons, use the framework
    // manager's icons generation feature.
    
    public const TYPE_ACTIONCODE = 'actioncode';
    public const TYPE_ACTIVATE = 'activate';
    public const TYPE_ACTIVITY = 'activity';
    public const TYPE_ADD = 'add';
    public const TYPE_ATTENTION_REQUIRED = 'attention_required';
    public const TYPE_AUDIENCE = 'audience';
    public const TYPE_BACK = 'back';
    public const TYPE_BACK_TO_CURRENT = 'back_to_current';
    public const TYPE_BACKUP = 'backup';
    public const TYPE_BOX = 'box';
    public const TYPE_BROWSE = 'browse';
    public const TYPE_BUGREPORT = 'bugreport';
    public const TYPE_BUILD = 'build';
    public const TYPE_BUSINESS = 'business';
    public const TYPE_BUTTON = 'button';
    public const TYPE_CALENDAR = 'calendar';
    public const TYPE_CAMPAIGNS = 'campaigns';
    public const TYPE_CANCEL = 'cancel';
    public const TYPE_CARET_DOWN = 'caret_down';
    public const TYPE_CARET_UP = 'caret_up';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_CHANGE_ORDER = 'change_order';
    public const TYPE_CHANGELOG = 'changelog';
    public const TYPE_CHECK = 'check';
    public const TYPE_CODE = 'code';
    public const TYPE_COLLAPSE = 'collapse';
    public const TYPE_COLLAPSE_LEFT = 'collapse_left';
    public const TYPE_COLLAPSE_RIGHT = 'collapse_right';
    public const TYPE_COLORS = 'colors';
    public const TYPE_COMBINATION = 'combination';
    public const TYPE_COMBINE = 'combine';
    public const TYPE_COMMAND_DECK = 'command_deck';
    public const TYPE_COMMANDS = 'commands';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_COMTYPES = 'comtypes';
    public const TYPE_CONTENT_TYPES = 'content_types';
    public const TYPE_CONVERT = 'convert';
    public const TYPE_COPY = 'copy';
    public const TYPE_COUNTDOWN = 'countdown';
    public const TYPE_COUNTRIES = 'countries';
    public const TYPE_CSV = 'csv';
    public const TYPE_CUSTOM_VARIABLES = 'custom_variables';
    public const TYPE_DEACTIVATE = 'deactivate';
    public const TYPE_DEACTIVATED = 'deactivated';
    public const TYPE_DELETE = 'delete';
    public const TYPE_DELETE_SIGN = 'delete_sign';
    public const TYPE_DELETED = 'deleted';
    public const TYPE_DESELECT_ALL = 'deselect_all';
    public const TYPE_DESTROY = 'destroy';
    public const TYPE_DEVELOPER = 'developer';
    public const TYPE_DISABLED = 'disabled';
    public const TYPE_DISCARD = 'discard';
    public const TYPE_DISCONNECT = 'disconnect';
    public const TYPE_DOWNLOAD = 'download';
    public const TYPE_DRAFT = 'draft';
    public const TYPE_DRAG = 'drag';
    public const TYPE_DROPDOWN = 'dropdown';
    public const TYPE_EDIT = 'edit';
    public const TYPE_EDITOR = 'editor';
    public const TYPE_EMAIL = 'email';
    public const TYPE_ENABLED = 'enabled';
    public const TYPE_EXPAND = 'expand';
    public const TYPE_EXPAND_LEFT = 'expand_left';
    public const TYPE_EXPAND_RIGHT = 'expand_right';
    public const TYPE_EXPORT = 'export';
    public const TYPE_EXPORT_ARCHIVE = 'export_archive';
    public const TYPE_FEATURETABLES = 'featuretables';
    public const TYPE_FEEDBACK = 'feedback';
    public const TYPE_FILE = 'file';
    public const TYPE_FILTER = 'filter';
    public const TYPE_FIRST = 'first';
    public const TYPE_FLAT = 'flat';
    public const TYPE_FORWARD = 'forward';
    public const TYPE_GENERATE = 'generate';
    public const TYPE_GLOBAL = 'global';
    public const TYPE_GLOBAL_CONTENT = 'global_content';
    public const TYPE_GROUPED = 'grouped';
    public const TYPE_HELP = 'help';
    public const TYPE_HIDE = 'hide';
    public const TYPE_HOME = 'home';
    public const TYPE_HTML = 'html';
    public const TYPE_ID = 'id';
    public const TYPE_IMAGE = 'image';
    public const TYPE_IMPORT = 'import';
    public const TYPE_INACTIVE = 'inactive';
    public const TYPE_INFORMATION = 'information';
    public const TYPE_ITEM_ACTIVE = 'item_active';
    public const TYPE_ITEM_INACTIVE = 'item_inactive';
    public const TYPE_JUMP_TO = 'jump_to';
    public const TYPE_JUMP_UP = 'jump_up';
    public const TYPE_KEYWORD = 'keyword';
    public const TYPE_LAST = 'last';
    public const TYPE_LINK = 'link';
    public const TYPE_LIST = 'list';
    public const TYPE_LOAD = 'load';
    public const TYPE_LOCKED = 'locked';
    public const TYPE_LOG_IN = 'log_in';
    public const TYPE_LOG_OUT = 'log_out';
    public const TYPE_LOOKUP = 'lookup';
    public const TYPE_MAIL_HEADER_TITLE = 'mail_header_title';
    public const TYPE_MAIL_HEADERS = 'mail_headers';
    public const TYPE_MAIL_TESTS = 'mail_tests';
    public const TYPE_MAILS = 'mails';
    public const TYPE_MAXIMIZE = 'maximize';
    public const TYPE_MEDIA = 'media';
    public const TYPE_MENU = 'menu';
    public const TYPE_MERGE = 'merge';
    public const TYPE_MESSAGE = 'message';
    public const TYPE_MINUS = 'minus';
    public const TYPE_MONEY = 'money';
    public const TYPE_MOVE = 'move';
    public const TYPE_MOVE_LEFT_RIGHT = 'move_left_right';
    public const TYPE_MOVE_TO = 'move_to';
    public const TYPE_MOVE_UP_DOWN = 'move_up_down';
    public const TYPE_NEXT = 'next';
    public const TYPE_NO = 'no';
    public const TYPE_NOT_AVAILABLE = 'not_available';
    public const TYPE_NOT_REQUIRED = 'not_required';
    public const TYPE_NOTEPAD = 'notepad';
    public const TYPE_OFF = 'off';
    public const TYPE_OK = 'ok';
    public const TYPE_OMS = 'oms';
    public const TYPE_ON = 'on';
    public const TYPE_OPTIONS = 'options';
    public const TYPE_PAGE = 'page';
    public const TYPE_PAGEMODEL = 'pagemodel';
    public const TYPE_PAUSE = 'pause';
    public const TYPE_PIN = 'pin';
    public const TYPE_PLAY = 'play';
    public const TYPE_PLUS = 'plus';
    public const TYPE_POSITION_ANY = 'position_any';
    public const TYPE_POSITION_BOTTOM = 'position_bottom';
    public const TYPE_POSITION_TOP = 'position_top';
    public const TYPE_PRESETS = 'presets';
    public const TYPE_PREVIEW = 'preview';
    public const TYPE_PREVIOUS = 'previous';
    public const TYPE_PRICE = 'price';
    public const TYPE_PRINT = 'print';
    public const TYPE_PRINTER = 'printer';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_PROMS = 'proms';
    public const TYPE_PROOFING = 'proofing';
    public const TYPE_PROPERTIES = 'properties';
    public const TYPE_PUBLISH = 'publish';
    public const TYPE_PUBLISHED = 'published';
    public const TYPE_RATING = 'rating';
    public const TYPE_RECORD_TYPE = 'record_type';
    public const TYPE_REFRESH = 'refresh';
    public const TYPE_REQUIRED = 'required';
    public const TYPE_RESET = 'reset';
    public const TYPE_RESTORE = 'restore';
    public const TYPE_REVERT = 'revert';
    public const TYPE_REVIEW = 'review';
    public const TYPE_SAVE = 'save';
    public const TYPE_SEARCH = 'search';
    public const TYPE_SELECT_ALL = 'select_all';
    public const TYPE_SELECTED = 'selected';
    public const TYPE_SEND = 'send';
    public const TYPE_SETTINGS = 'settings';
    public const TYPE_SHOP = 'shop';
    public const TYPE_SORT = 'sort';
    public const TYPE_SORT_ASC = 'sort_asc';
    public const TYPE_SORT_DESC = 'sort_desc';
    public const TYPE_SORTING = 'sorting';
    public const TYPE_SPINNER = 'spinner';
    public const TYPE_STATUS = 'status';
    public const TYPE_STOP = 'stop';
    public const TYPE_STRUCTURAL = 'structural';
    public const TYPE_SUGGEST = 'suggest';
    public const TYPE_SWITCH = 'switch';
    public const TYPE_SWITCH_CAMPAIGN = 'switch_campaign';
    public const TYPE_SWITCH_MODE = 'switch_mode';
    public const TYPE_TABLE = 'table';
    public const TYPE_TARIFF_MATRIX = 'tariff_matrix';
    public const TYPE_TASK = 'task';
    public const TYPE_TEMPLATE = 'template';
    public const TYPE_TENANT = 'tenant';
    public const TYPE_TEXT = 'text';
    public const TYPE_TIME = 'time';
    public const TYPE_TOGGLE = 'toggle';
    public const TYPE_TOOLS = 'tools';
    public const TYPE_TRANSLATION = 'translation';
    public const TYPE_TRANSMISSION = 'transmission';
    public const TYPE_UNCOMBINE = 'uncombine';
    public const TYPE_UNCOMBINED = 'uncombined';
    public const TYPE_UNDELETE = 'undelete';
    public const TYPE_UNLOCK = 'unlock';
    public const TYPE_UNLOCKED = 'unlocked';
    public const TYPE_UPLOAD = 'upload';
    public const TYPE_USER = 'user';
    public const TYPE_USERS = 'users';
    public const TYPE_UTILS = 'utils';
    public const TYPE_VALIDATE = 'validate';
    public const TYPE_VARIABLES = 'variables';
    public const TYPE_VARIATIONS = 'variations';
    public const TYPE_VIEW = 'view';
    public const TYPE_WAITING = 'waiting';
    public const TYPE_WARNING = 'warning';
    public const TYPE_WHITELIST = 'whitelist';
    public const TYPE_WORDWRAP = 'wordwrap';
    public const TYPE_WORKFLOW = 'workflow';
    public const TYPE_XML = 'xml';
    public const TYPE_YES = 'yes';

    /**
     * @var array<string,string>
     */
    private static $types = array(
        self::TYPE_ACTIONCODE => 'rocket',
        self::TYPE_ACTIVATE => 'far:sun',
        self::TYPE_ACTIVITY => 'bullhorn',
        self::TYPE_ADD => 'plus-circle',
        self::TYPE_ATTENTION_REQUIRED => 'exclamation-triangle',
        self::TYPE_AUDIENCE => 'podcast',
        self::TYPE_BACK => 'arrow-circle-left',
        self::TYPE_BACK_TO_CURRENT => 'fas:level-down-alt',
        self::TYPE_BACKUP => 'recycle',
        self::TYPE_BOX => 'archive',
        self::TYPE_BROWSE => 'folder-open',
        self::TYPE_BUGREPORT => 'bug',
        self::TYPE_BUILD => 'magic',
        self::TYPE_BUSINESS => 'university',
        self::TYPE_BUTTON => 'external-link-square-alt',
        self::TYPE_CALENDAR => 'calendar',
        self::TYPE_CAMPAIGNS => 'flag',
        self::TYPE_CANCEL => 'ban',
        self::TYPE_CARET_DOWN => 'caret-down',
        self::TYPE_CARET_UP => 'caret-up',
        self::TYPE_CATEGORY => 'bars',
        self::TYPE_CHANGE_ORDER => 'bars',
        self::TYPE_CHANGELOG => 'edit',
        self::TYPE_CHECK => 'check-double',
        self::TYPE_CODE => 'code',
        self::TYPE_COLLAPSE => 'minus-circle',
        self::TYPE_COLLAPSE_LEFT => 'caret-square-left',
        self::TYPE_COLLAPSE_RIGHT => 'caret-square-right',
        self::TYPE_COLORS => 'palette',
        self::TYPE_COMBINATION => 'object-group',
        self::TYPE_COMBINE => 'link',
        self::TYPE_COMMAND_DECK => 'fas:dice-d20',
        self::TYPE_COMMANDS => 'terminal',
        self::TYPE_COMMENT => 'comment',
        self::TYPE_COMTYPES => 'broadcast-tower',
        self::TYPE_CONTENT_TYPES => 'fab:elementor',
        self::TYPE_CONVERT => 'cogs',
        self::TYPE_COPY => 'copy',
        self::TYPE_COUNTDOWN => 'far:clock',
        self::TYPE_COUNTRIES => 'far:flag',
        self::TYPE_CSV => 'far:file-alt',
        self::TYPE_CUSTOM_VARIABLES => 'project-diagram',
        self::TYPE_DEACTIVATE => 'far:moon',
        self::TYPE_DEACTIVATED => 'far:moon',
        self::TYPE_DELETE => 'times',
        self::TYPE_DELETE_SIGN => 'far:times-circle',
        self::TYPE_DELETED => 'times',
        self::TYPE_DESELECT_ALL => 'far:minus-square',
        self::TYPE_DESTROY => 'exclamation-triangle',
        self::TYPE_DEVELOPER => 'asterisk',
        self::TYPE_DISABLED => 'ban',
        self::TYPE_DISCARD => 'far:trash-alt',
        self::TYPE_DISCONNECT => 'unlink',
        self::TYPE_DOWNLOAD => 'download',
        self::TYPE_DRAFT => 'puzzle-piece',
        self::TYPE_DRAG => 'bars',
        self::TYPE_DROPDOWN => 'caret-down',
        self::TYPE_EDIT => 'fas:pencil-alt',
        self::TYPE_EDITOR => 'cubes',
        self::TYPE_EMAIL => 'at',
        self::TYPE_ENABLED => 'fas:check-circle',
        self::TYPE_EXPAND => 'plus-circle',
        self::TYPE_EXPAND_LEFT => 'caret-square-left',
        self::TYPE_EXPAND_RIGHT => 'caret-square-right',
        self::TYPE_EXPORT => 'fas:bolt',
        self::TYPE_EXPORT_ARCHIVE => 'archive',
        self::TYPE_FEATURETABLES => 'server',
        self::TYPE_FEEDBACK => 'far:thumbs-up',
        self::TYPE_FILE => 'file-alt',
        self::TYPE_FILTER => 'filter',
        self::TYPE_FIRST => 'step-backward',
        self::TYPE_FLAT => 'th',
        self::TYPE_FORWARD => 'arrow-circle-right',
        self::TYPE_GENERATE => 'fas:bolt',
        self::TYPE_GLOBAL => 'globe-europe',
        self::TYPE_GLOBAL_CONTENT => 'cube',
        self::TYPE_GROUPED => 'layer-group',
        self::TYPE_HELP => 'question-circle',
        self::TYPE_HIDE => 'eye-slash',
        self::TYPE_HOME => 'home',
        self::TYPE_HTML => 'code',
        self::TYPE_ID => 'key',
        self::TYPE_IMAGE => 'image',
        self::TYPE_IMPORT => 'briefcase',
        self::TYPE_INACTIVE => 'far:moon',
        self::TYPE_INFORMATION => 'info-circle',
        self::TYPE_ITEM_ACTIVE => 'circle',
        self::TYPE_ITEM_INACTIVE => 'far:circle',
        self::TYPE_JUMP_TO => 'far:arrow-alt-circle-right',
        self::TYPE_JUMP_UP => 'arrow-up',
        self::TYPE_KEYWORD => 'bookmark',
        self::TYPE_LAST => 'step-forward',
        self::TYPE_LINK => 'link',
        self::TYPE_LIST => 'server',
        self::TYPE_LOAD => 'far:folder-open',
        self::TYPE_LOCKED => 'lock',
        self::TYPE_LOG_IN => 'fas:sign-in-alt',
        self::TYPE_LOG_OUT => 'power-off',
        self::TYPE_LOOKUP => 'ellipsis-h',
        self::TYPE_MAIL_HEADER_TITLE => 'fas:heading',
        self::TYPE_MAIL_HEADERS => 'crosshairs',
        self::TYPE_MAIL_TESTS => 'envelope-open-text',
        self::TYPE_MAILS => 'envelope',
        self::TYPE_MAXIMIZE => 'expand',
        self::TYPE_MEDIA => 'image',
        self::TYPE_MENU => 'bars',
        self::TYPE_MERGE => 'fas:level-down-alt',
        self::TYPE_MESSAGE => 'far:comment-alt',
        self::TYPE_MINUS => 'minus',
        self::TYPE_MONEY => 'money-check-alt',
        self::TYPE_MOVE => 'arrows-alt',
        self::TYPE_MOVE_LEFT_RIGHT => 'fas:arrows-alt-h',
        self::TYPE_MOVE_TO => 'fas:sign-out-alt',
        self::TYPE_MOVE_UP_DOWN => 'fas:arrows-alt-v',
        self::TYPE_NEXT => 'chevron-right',
        self::TYPE_NO => 'times',
        self::TYPE_NOT_AVAILABLE => 'ban',
        self::TYPE_NOT_REQUIRED => 'minus',
        self::TYPE_NOTEPAD => 'far:sticky-note',
        self::TYPE_OFF => 'power-off',
        self::TYPE_OK => 'check',
        self::TYPE_OMS => 'fab:telegram',
        self::TYPE_ON => 'far:dot-circle',
        self::TYPE_OPTIONS => 'far:dot-circle',
        self::TYPE_PAGE => 'file',
        self::TYPE_PAGEMODEL => 'far:newspaper',
        self::TYPE_PAUSE => 'pause',
        self::TYPE_PIN => 'thumbtack',
        self::TYPE_PLAY => 'play',
        self::TYPE_PLUS => 'plus',
        self::TYPE_POSITION_ANY => 'fas:sort',
        self::TYPE_POSITION_BOTTOM => 'arrow-circle-down',
        self::TYPE_POSITION_TOP => 'arrow-circle-up',
        self::TYPE_PRESETS => 'server',
        self::TYPE_PREVIEW => 'far:file-code',
        self::TYPE_PREVIOUS => 'chevron-left',
        self::TYPE_PRICE => 'money-check-alt',
        self::TYPE_PRINT => 'print',
        self::TYPE_PRINTER => 'print',
        self::TYPE_PRODUCT => 'shopping-basket',
        self::TYPE_PROMS => 'database',
        self::TYPE_PROOFING => 'far:check-square',
        self::TYPE_PROPERTIES => 'fas:cogs',
        self::TYPE_PUBLISH => 'fas:sign-out-alt',
        self::TYPE_PUBLISHED => 'check',
        self::TYPE_RATING => 'star',
        self::TYPE_RECORD_TYPE => 'bezier-curve',
        self::TYPE_REFRESH => 'fas:sync',
        self::TYPE_REQUIRED => 'exclamation-circle',
        self::TYPE_RESET => 'minus-square',
        self::TYPE_RESTORE => 'share',
        self::TYPE_REVERT => 'history',
        self::TYPE_REVIEW => 'user-edit',
        self::TYPE_SAVE => 'save',
        self::TYPE_SEARCH => 'search',
        self::TYPE_SELECT_ALL => 'far:plus-square',
        self::TYPE_SELECTED => 'list',
        self::TYPE_SEND => 'envelope',
        self::TYPE_SETTINGS => 'wrench',
        self::TYPE_SHOP => 'shopping-cart',
        self::TYPE_SORT => 'sort',
        self::TYPE_SORT_ASC => 'angle-up',
        self::TYPE_SORT_DESC => 'angle-down',
        self::TYPE_SORTING => 'fas:sort-amount-down',
        self::TYPE_SPINNER => 'spinner',
        self::TYPE_STATUS => 'shield-alt',
        self::TYPE_STOP => 'pause',
        self::TYPE_STRUCTURAL => 'cubes',
        self::TYPE_SUGGEST => 'lightbulb',
        self::TYPE_SWITCH => 'retweet',
        self::TYPE_SWITCH_CAMPAIGN => 'fas:exchange-alt',
        self::TYPE_SWITCH_MODE => 'compass',
        self::TYPE_TABLE => 'table',
        self::TYPE_TARIFF_MATRIX => 'table',
        self::TYPE_TASK => 'tasks',
        self::TYPE_TEMPLATE => 'far:file-alt',
        self::TYPE_TENANT => 'award',
        self::TYPE_TEXT => 'font',
        self::TYPE_TIME => 'clock',
        self::TYPE_TOGGLE => 'retweet',
        self::TYPE_TOOLS => 'tools',
        self::TYPE_TRANSLATION => 'globe',
        self::TYPE_TRANSMISSION => 'satellite-dish',
        self::TYPE_UNCOMBINE => 'unlink',
        self::TYPE_UNCOMBINED => 'object-ungroup',
        self::TYPE_UNDELETE => 'reply',
        self::TYPE_UNLOCK => 'unlock',
        self::TYPE_UNLOCKED => 'unlock',
        self::TYPE_UPLOAD => 'upload',
        self::TYPE_USER => 'user',
        self::TYPE_USERS => 'users',
        self::TYPE_UTILS => 'fas:first-aid',
        self::TYPE_VALIDATE => 'check-circle',
        self::TYPE_VARIABLES => 'code-branch',
        self::TYPE_VARIATIONS => 'sitemap',
        self::TYPE_VIEW => 'eye',
        self::TYPE_WAITING => 'far:clock',
        self::TYPE_WARNING => 'fas:exclamation-triangle',
        self::TYPE_WHITELIST => 'far:star',
        self::TYPE_WORDWRAP => 'fas:terminal',
        self::TYPE_WORKFLOW => 'sitemap',
        self::TYPE_XML => 'code',
        self::TYPE_YES => 'check'
    );
    
    // endregion

    /* END TYPES */

    public function __construct()
    {
        $this->id = 'ic'.nextJSID();
    }

    /* START METHODS */

    // region: Icon type methods
    
    public function actioncode() : UI_Icon { return $this->setType(self::TYPE_ACTIONCODE); }
    public function activate() : UI_Icon { return $this->setType(self::TYPE_ACTIVATE); }
    public function activity() : UI_Icon { return $this->setType(self::TYPE_ACTIVITY); }
    public function add() : UI_Icon { return $this->setType(self::TYPE_ADD); }
    public function attentionRequired() : UI_Icon { return $this->setType(self::TYPE_ATTENTION_REQUIRED); }
    public function audience() : UI_Icon { return $this->setType(self::TYPE_AUDIENCE); }
    public function back() : UI_Icon { return $this->setType(self::TYPE_BACK); }
    public function backToCurrent() : UI_Icon { return $this->setType(self::TYPE_BACK_TO_CURRENT); }
    public function backup() : UI_Icon { return $this->setType(self::TYPE_BACKUP); }
    public function box() : UI_Icon { return $this->setType(self::TYPE_BOX); }
    public function browse() : UI_Icon { return $this->setType(self::TYPE_BROWSE); }
    public function bugreport() : UI_Icon { return $this->setType(self::TYPE_BUGREPORT); }
    public function build() : UI_Icon { return $this->setType(self::TYPE_BUILD); }
    public function business() : UI_Icon { return $this->setType(self::TYPE_BUSINESS); }
    public function button() : UI_Icon { return $this->setType(self::TYPE_BUTTON); }
    public function calendar() : UI_Icon { return $this->setType(self::TYPE_CALENDAR); }
    public function campaigns() : UI_Icon { return $this->setType(self::TYPE_CAMPAIGNS); }
    public function cancel() : UI_Icon { return $this->setType(self::TYPE_CANCEL); }
    public function caretDown() : UI_Icon { return $this->setType(self::TYPE_CARET_DOWN); }
    public function caretUp() : UI_Icon { return $this->setType(self::TYPE_CARET_UP); }
    public function category() : UI_Icon { return $this->setType(self::TYPE_CATEGORY); }
    public function changeOrder() : UI_Icon { return $this->setType(self::TYPE_CHANGE_ORDER); }
    public function changelog() : UI_Icon { return $this->setType(self::TYPE_CHANGELOG); }
    public function check() : UI_Icon { return $this->setType(self::TYPE_CHECK); }
    public function code() : UI_Icon { return $this->setType(self::TYPE_CODE); }
    public function collapse() : UI_Icon { return $this->setType(self::TYPE_COLLAPSE); }
    public function collapseLeft() : UI_Icon { return $this->setType(self::TYPE_COLLAPSE_LEFT); }
    public function collapseRight() : UI_Icon { return $this->setType(self::TYPE_COLLAPSE_RIGHT); }
    public function colors() : UI_Icon { return $this->setType(self::TYPE_COLORS); }
    public function combination() : UI_Icon { return $this->setType(self::TYPE_COMBINATION); }
    public function combine() : UI_Icon { return $this->setType(self::TYPE_COMBINE); }
    public function commandDeck() : UI_Icon { return $this->setType(self::TYPE_COMMAND_DECK); }
    public function commands() : UI_Icon { return $this->setType(self::TYPE_COMMANDS); }
    public function comment() : UI_Icon { return $this->setType(self::TYPE_COMMENT); }
    public function comtypes() : UI_Icon { return $this->setType(self::TYPE_COMTYPES); }
    public function contentTypes() : UI_Icon { return $this->setType(self::TYPE_CONTENT_TYPES); }
    public function convert() : UI_Icon { return $this->setType(self::TYPE_CONVERT); }
    public function copy() : UI_Icon { return $this->setType(self::TYPE_COPY); }
    public function countdown() : UI_Icon { return $this->setType(self::TYPE_COUNTDOWN); }
    public function countries() : UI_Icon { return $this->setType(self::TYPE_COUNTRIES); }
    public function csv() : UI_Icon { return $this->setType(self::TYPE_CSV); }
    public function customVariables() : UI_Icon { return $this->setType(self::TYPE_CUSTOM_VARIABLES); }
    public function deactivate() : UI_Icon { return $this->setType(self::TYPE_DEACTIVATE); }
    public function deactivated() : UI_Icon { return $this->setType(self::TYPE_DEACTIVATED); }
    public function delete() : UI_Icon { return $this->setType(self::TYPE_DELETE); }
    public function deleteSign() : UI_Icon { return $this->setType(self::TYPE_DELETE_SIGN); }
    public function deleted() : UI_Icon { return $this->setType(self::TYPE_DELETED); }
    public function deselectAll() : UI_Icon { return $this->setType(self::TYPE_DESELECT_ALL); }
    public function destroy() : UI_Icon { return $this->setType(self::TYPE_DESTROY); }
    public function developer() : UI_Icon { return $this->setType(self::TYPE_DEVELOPER); }
    public function disabled() : UI_Icon { return $this->setType(self::TYPE_DISABLED); }
    public function discard() : UI_Icon { return $this->setType(self::TYPE_DISCARD); }
    public function disconnect() : UI_Icon { return $this->setType(self::TYPE_DISCONNECT); }
    public function download() : UI_Icon { return $this->setType(self::TYPE_DOWNLOAD); }
    public function draft() : UI_Icon { return $this->setType(self::TYPE_DRAFT); }
    public function drag() : UI_Icon { return $this->setType(self::TYPE_DRAG); }
    public function dropdown() : UI_Icon { return $this->setType(self::TYPE_DROPDOWN); }
    public function edit() : UI_Icon { return $this->setType(self::TYPE_EDIT); }
    public function editor() : UI_Icon { return $this->setType(self::TYPE_EDITOR); }
    public function email() : UI_Icon { return $this->setType(self::TYPE_EMAIL); }
    public function enabled() : UI_Icon { return $this->setType(self::TYPE_ENABLED); }
    public function expand() : UI_Icon { return $this->setType(self::TYPE_EXPAND); }
    public function expandLeft() : UI_Icon { return $this->setType(self::TYPE_EXPAND_LEFT); }
    public function expandRight() : UI_Icon { return $this->setType(self::TYPE_EXPAND_RIGHT); }
    public function export() : UI_Icon { return $this->setType(self::TYPE_EXPORT); }
    public function exportArchive() : UI_Icon { return $this->setType(self::TYPE_EXPORT_ARCHIVE); }
    public function featuretables() : UI_Icon { return $this->setType(self::TYPE_FEATURETABLES); }
    public function feedback() : UI_Icon { return $this->setType(self::TYPE_FEEDBACK); }
    public function file() : UI_Icon { return $this->setType(self::TYPE_FILE); }
    public function filter() : UI_Icon { return $this->setType(self::TYPE_FILTER); }
    public function first() : UI_Icon { return $this->setType(self::TYPE_FIRST); }
    public function flat() : UI_Icon { return $this->setType(self::TYPE_FLAT); }
    public function forward() : UI_Icon { return $this->setType(self::TYPE_FORWARD); }
    public function generate() : UI_Icon { return $this->setType(self::TYPE_GENERATE); }
    public function global() : UI_Icon { return $this->setType(self::TYPE_GLOBAL); }
    public function globalContent() : UI_Icon { return $this->setType(self::TYPE_GLOBAL_CONTENT); }
    public function grouped() : UI_Icon { return $this->setType(self::TYPE_GROUPED); }
    public function help() : UI_Icon { return $this->setType(self::TYPE_HELP); }
    public function hide() : UI_Icon { return $this->setType(self::TYPE_HIDE); }
    public function home() : UI_Icon { return $this->setType(self::TYPE_HOME); }
    public function html() : UI_Icon { return $this->setType(self::TYPE_HTML); }
    public function id() : UI_Icon { return $this->setType(self::TYPE_ID); }
    public function image() : UI_Icon { return $this->setType(self::TYPE_IMAGE); }
    public function import() : UI_Icon { return $this->setType(self::TYPE_IMPORT); }
    public function inactive() : UI_Icon { return $this->setType(self::TYPE_INACTIVE); }
    public function information() : UI_Icon { return $this->setType(self::TYPE_INFORMATION); }
    public function itemActive() : UI_Icon { return $this->setType(self::TYPE_ITEM_ACTIVE); }
    public function itemInactive() : UI_Icon { return $this->setType(self::TYPE_ITEM_INACTIVE); }
    public function jumpTo() : UI_Icon { return $this->setType(self::TYPE_JUMP_TO); }
    public function jumpUp() : UI_Icon { return $this->setType(self::TYPE_JUMP_UP); }
    public function keyword() : UI_Icon { return $this->setType(self::TYPE_KEYWORD); }
    public function last() : UI_Icon { return $this->setType(self::TYPE_LAST); }
    public function link() : UI_Icon { return $this->setType(self::TYPE_LINK); }
    public function list() : UI_Icon { return $this->setType(self::TYPE_LIST); }
    public function load() : UI_Icon { return $this->setType(self::TYPE_LOAD); }
    public function locked() : UI_Icon { return $this->setType(self::TYPE_LOCKED); }
    public function logIn() : UI_Icon { return $this->setType(self::TYPE_LOG_IN); }
    public function logOut() : UI_Icon { return $this->setType(self::TYPE_LOG_OUT); }
    public function lookup() : UI_Icon { return $this->setType(self::TYPE_LOOKUP); }
    public function mailHeaderTitle() : UI_Icon { return $this->setType(self::TYPE_MAIL_HEADER_TITLE); }
    public function mailHeaders() : UI_Icon { return $this->setType(self::TYPE_MAIL_HEADERS); }
    public function mailTests() : UI_Icon { return $this->setType(self::TYPE_MAIL_TESTS); }
    public function mails() : UI_Icon { return $this->setType(self::TYPE_MAILS); }
    public function maximize() : UI_Icon { return $this->setType(self::TYPE_MAXIMIZE); }
    public function media() : UI_Icon { return $this->setType(self::TYPE_MEDIA); }
    public function menu() : UI_Icon { return $this->setType(self::TYPE_MENU); }
    public function merge() : UI_Icon { return $this->setType(self::TYPE_MERGE); }
    public function message() : UI_Icon { return $this->setType(self::TYPE_MESSAGE); }
    public function minus() : UI_Icon { return $this->setType(self::TYPE_MINUS); }
    public function money() : UI_Icon { return $this->setType(self::TYPE_MONEY); }
    public function move() : UI_Icon { return $this->setType(self::TYPE_MOVE); }
    public function moveLeftRight() : UI_Icon { return $this->setType(self::TYPE_MOVE_LEFT_RIGHT); }
    public function moveTo() : UI_Icon { return $this->setType(self::TYPE_MOVE_TO); }
    public function moveUpDown() : UI_Icon { return $this->setType(self::TYPE_MOVE_UP_DOWN); }
    public function next() : UI_Icon { return $this->setType(self::TYPE_NEXT); }
    public function no() : UI_Icon { return $this->setType(self::TYPE_NO); }
    public function notAvailable() : UI_Icon { return $this->setType(self::TYPE_NOT_AVAILABLE); }
    public function notRequired() : UI_Icon { return $this->setType(self::TYPE_NOT_REQUIRED); }
    public function notepad() : UI_Icon { return $this->setType(self::TYPE_NOTEPAD); }
    public function off() : UI_Icon { return $this->setType(self::TYPE_OFF); }
    public function ok() : UI_Icon { return $this->setType(self::TYPE_OK); }
    public function oms() : UI_Icon { return $this->setType(self::TYPE_OMS); }
    public function on() : UI_Icon { return $this->setType(self::TYPE_ON); }
    public function options() : UI_Icon { return $this->setType(self::TYPE_OPTIONS); }
    public function page() : UI_Icon { return $this->setType(self::TYPE_PAGE); }
    public function pagemodel() : UI_Icon { return $this->setType(self::TYPE_PAGEMODEL); }
    public function pause() : UI_Icon { return $this->setType(self::TYPE_PAUSE); }
    public function pin() : UI_Icon { return $this->setType(self::TYPE_PIN); }
    public function play() : UI_Icon { return $this->setType(self::TYPE_PLAY); }
    public function plus() : UI_Icon { return $this->setType(self::TYPE_PLUS); }
    public function positionAny() : UI_Icon { return $this->setType(self::TYPE_POSITION_ANY); }
    public function positionBottom() : UI_Icon { return $this->setType(self::TYPE_POSITION_BOTTOM); }
    public function positionTop() : UI_Icon { return $this->setType(self::TYPE_POSITION_TOP); }
    public function presets() : UI_Icon { return $this->setType(self::TYPE_PRESETS); }
    public function preview() : UI_Icon { return $this->setType(self::TYPE_PREVIEW); }
    public function previous() : UI_Icon { return $this->setType(self::TYPE_PREVIOUS); }
    public function price() : UI_Icon { return $this->setType(self::TYPE_PRICE); }
    public function print() : UI_Icon { return $this->setType(self::TYPE_PRINT); }
    public function printer() : UI_Icon { return $this->setType(self::TYPE_PRINTER); }
    public function product() : UI_Icon { return $this->setType(self::TYPE_PRODUCT); }
    public function proms() : UI_Icon { return $this->setType(self::TYPE_PROMS); }
    public function proofing() : UI_Icon { return $this->setType(self::TYPE_PROOFING); }
    public function properties() : UI_Icon { return $this->setType(self::TYPE_PROPERTIES); }
    public function publish() : UI_Icon { return $this->setType(self::TYPE_PUBLISH); }
    public function published() : UI_Icon { return $this->setType(self::TYPE_PUBLISHED); }
    public function rating() : UI_Icon { return $this->setType(self::TYPE_RATING); }
    public function recordType() : UI_Icon { return $this->setType(self::TYPE_RECORD_TYPE); }
    public function refresh() : UI_Icon { return $this->setType(self::TYPE_REFRESH); }
    public function required() : UI_Icon { return $this->setType(self::TYPE_REQUIRED); }
    public function reset() : UI_Icon { return $this->setType(self::TYPE_RESET); }
    public function restore() : UI_Icon { return $this->setType(self::TYPE_RESTORE); }
    public function revert() : UI_Icon { return $this->setType(self::TYPE_REVERT); }
    public function review() : UI_Icon { return $this->setType(self::TYPE_REVIEW); }
    public function save() : UI_Icon { return $this->setType(self::TYPE_SAVE); }
    public function search() : UI_Icon { return $this->setType(self::TYPE_SEARCH); }
    public function selectAll() : UI_Icon { return $this->setType(self::TYPE_SELECT_ALL); }
    public function selected() : UI_Icon { return $this->setType(self::TYPE_SELECTED); }
    public function send() : UI_Icon { return $this->setType(self::TYPE_SEND); }
    public function settings() : UI_Icon { return $this->setType(self::TYPE_SETTINGS); }
    public function shop() : UI_Icon { return $this->setType(self::TYPE_SHOP); }
    public function sort() : UI_Icon { return $this->setType(self::TYPE_SORT); }
    public function sortAsc() : UI_Icon { return $this->setType(self::TYPE_SORT_ASC); }
    public function sortDesc() : UI_Icon { return $this->setType(self::TYPE_SORT_DESC); }
    public function sorting() : UI_Icon { return $this->setType(self::TYPE_SORTING); }
    public function status() : UI_Icon { return $this->setType(self::TYPE_STATUS); }
    public function stop() : UI_Icon { return $this->setType(self::TYPE_STOP); }
    public function structural() : UI_Icon { return $this->setType(self::TYPE_STRUCTURAL); }
    public function suggest() : UI_Icon { return $this->setType(self::TYPE_SUGGEST); }
    public function switch() : UI_Icon { return $this->setType(self::TYPE_SWITCH); }
    public function switchCampaign() : UI_Icon { return $this->setType(self::TYPE_SWITCH_CAMPAIGN); }
    public function switchMode() : UI_Icon { return $this->setType(self::TYPE_SWITCH_MODE); }
    public function table() : UI_Icon { return $this->setType(self::TYPE_TABLE); }
    public function tariffMatrix() : UI_Icon { return $this->setType(self::TYPE_TARIFF_MATRIX); }
    public function task() : UI_Icon { return $this->setType(self::TYPE_TASK); }
    public function template() : UI_Icon { return $this->setType(self::TYPE_TEMPLATE); }
    public function tenant() : UI_Icon { return $this->setType(self::TYPE_TENANT); }
    public function text() : UI_Icon { return $this->setType(self::TYPE_TEXT); }
    public function time() : UI_Icon { return $this->setType(self::TYPE_TIME); }
    public function toggle() : UI_Icon { return $this->setType(self::TYPE_TOGGLE); }
    public function tools() : UI_Icon { return $this->setType(self::TYPE_TOOLS); }
    public function translation() : UI_Icon { return $this->setType(self::TYPE_TRANSLATION); }
    public function transmission() : UI_Icon { return $this->setType(self::TYPE_TRANSMISSION); }
    public function uncombine() : UI_Icon { return $this->setType(self::TYPE_UNCOMBINE); }
    public function uncombined() : UI_Icon { return $this->setType(self::TYPE_UNCOMBINED); }
    public function undelete() : UI_Icon { return $this->setType(self::TYPE_UNDELETE); }
    public function unlock() : UI_Icon { return $this->setType(self::TYPE_UNLOCK); }
    public function unlocked() : UI_Icon { return $this->setType(self::TYPE_UNLOCKED); }
    public function upload() : UI_Icon { return $this->setType(self::TYPE_UPLOAD); }
    public function user() : UI_Icon { return $this->setType(self::TYPE_USER); }
    public function users() : UI_Icon { return $this->setType(self::TYPE_USERS); }
    public function utils() : UI_Icon { return $this->setType(self::TYPE_UTILS); }
    public function validate() : UI_Icon { return $this->setType(self::TYPE_VALIDATE); }
    public function variables() : UI_Icon { return $this->setType(self::TYPE_VARIABLES); }
    public function variations() : UI_Icon { return $this->setType(self::TYPE_VARIATIONS); }
    public function view() : UI_Icon { return $this->setType(self::TYPE_VIEW); }
    public function waiting() : UI_Icon { return $this->setType(self::TYPE_WAITING); }
    public function warning() : UI_Icon { return $this->setType(self::TYPE_WARNING); }
    public function whitelist() : UI_Icon { return $this->setType(self::TYPE_WHITELIST); }
    public function wordwrap() : UI_Icon { return $this->setType(self::TYPE_WORDWRAP); }
    public function workflow() : UI_Icon { return $this->setType(self::TYPE_WORKFLOW); }
    public function xml() : UI_Icon { return $this->setType(self::TYPE_XML); }
    public function yes() : UI_Icon { return $this->setType(self::TYPE_YES); }
    
    // endregion

    /* END METHODS */

    public function spinner() : UI_Icon
    {
        $this->setType('SPINNER');
        $this->makeSpinner();
        return $this;
    }

    /**
     * Sets the icon's type.
     * @param string $type
     * @return UI_Icon
     */
    public function setType(string $type) : UI_Icon
    {
        $this->type = strtolower($type);
        return $this;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Adds a class name that will be added to the
     * icon tag's class attribute.
     *
     * @param string $className
     * @return UI_Icon
     */
    public function addClass(string $className) : UI_Icon
    {
        if (!in_array($className, $this->classes)) {
            $this->classes[] = $className;
        }

        return $this;
    }

    public function makeSpinner() : UI_Icon
    {
        return $this->addClass('fa-spin');
    }

    // region: Color styles

    /**
     * @var string|null
     */
    private $colorStyle = null;

    public const COLOR_STYLE_DANGER = 'danger';
    public const COLOR_STYLE_WARNING = 'warning';
    public const COLOR_STYLE_MUTED = 'muted';
    public const COLOR_STYLE_SUCCESS = 'success';
    public const COLOR_STYLE_INFO = 'info';
    public const COLOR_STYLE_WHITE = 'white';

    /**
     * @var array<string,string>
     */
    private static $colorClasses = array(
        self::COLOR_STYLE_DANGER => 'text-error',
        self::COLOR_STYLE_WARNING => 'text-warning',
        self::COLOR_STYLE_MUTED => 'muted',
        self::COLOR_STYLE_SUCCESS => 'text-success',
        self::COLOR_STYLE_INFO => 'text-info',
        self::COLOR_STYLE_WHITE => 'icon-white'
    );

    /**
     * @param string $style
     * @throws UI_Exception
     */
    public static function requireValidColorStyle(string $style) : void
    {
        if(isset(self::$colorClasses[$style]))
        {
            return;
        }

        throw new UI_Exception(
            'Invalid icon color style.',
            sprintf(
                'The color style [%s] does not exist. Valid styles are [%s].',
                $style,
                implode(', ', array_keys(self::$colorClasses))
            ),
            self::ERROR_INVALID_COLOR_STYLE
        );
    }

    public function makeColorStyle(string $style) : UI_Icon
    {
        self::requireValidColorStyle($style);

        $this->colorStyle = $style;
        return $this;
    }

    /**
     * Resets the color style to the default mode to
     * inherit the surrounding text's color.
     *
     * @return $this
     */
    public function makeRegular() : UI_Icon
    {
        $this->colorStyle = null;
        return $this;
    }

    public function makeDangerous() : UI_Icon
    {
        return $this->makeColorStyle(self::COLOR_STYLE_DANGER);
    }
    
    public function makeWarning() : UI_Icon
    {
        return $this->makeColorStyle(self::COLOR_STYLE_WARNING);
    }
    
    public function makeMuted() : UI_Icon
    {
        return $this->makeColorStyle(self::COLOR_STYLE_MUTED);
    }

    public function makeSuccess() : UI_Icon
    {
        return $this->makeColorStyle(self::COLOR_STYLE_SUCCESS);
    }

    public function makeInformation() : UI_Icon
    {
        return $this->makeColorStyle(self::COLOR_STYLE_INFO);
    }

    public function makeWhite() : UI_Icon
    {
        return $this->makeColorStyle(self::COLOR_STYLE_WHITE);
    }

    // endregion

   /**
    * Gives the icon a clickable style: the cursor
    * will be the click-enabled cursor. Optionally
    * a click handling statement can be specified.
    */
    public function makeClickable(?string $statement=null) : UI_Icon
    {
        if(!empty($statement))
        {
            $this->setAttribute('onclick', $statement);    
        }
        
        return $this->addClass('clickable');
    }

    /**
     * @param string|number|UI_Renderable_Interface $text
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($text) : UI_Icon
    {
        $this->tooltip['text'] = toString($text);
        
        return $this;
    }

    public function setID(string $id) : UI_Icon
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $name, $value) : UI_Icon
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return string[]
     * @throws UI_Exception
     */
    private function resolveClasses() : array
    {
        $classes = array();
        
        $type = $this->parseType();
        
        $classes[] = $type['prefix'];
        $classes[] = 'fa-'.$type['type'];

        if(isset($this->colorStyle))
        {
            $classes[] = self::$colorClasses[$this->colorStyle];
        }
        
        return array_merge($classes, $this->classes);
    }

    /**
     * Resolves the type to use to render the icon.
     *
     * @return string
     * @throws UI_Exception
     */
    private function resolveType() : string
    {
        $type = $this->getType();

        if(isset(self::$types[$type]))
        {
            return self::$types[$type];
        }

        throw new UI_Exception(
            'No icon type selected.',
            sprintf(
                'An icon does not have a type, or has an invalid type selected: [%s].',
                $type
            ),
            self::ERROR_INVALID_TYPE_SELECTED
        );
    }

    /**
     * @return array{prefix:string,type:string}
     * @throws UI_Exception
     */
    private function parseType() : array
    {
        $type = $this->resolveType();
        $prefix = $this->prefix;
        $pos = strpos($type, ':');
        
        if($pos !== false) 
        {
            $prefix = substr($type, 0, $pos);
            $type = substr($type, $pos+1);
        }
        
        return array(
            'prefix' => $prefix,
            'type' => $type
        );
    }
    
    public function render() : string
    {
        $tag = sprintf(
            '<i %s></i>',
            ConvertHelper::array2attributeString($this->resolveAttributes())
        );

        // if we have a tooltip, we schedule setting up the
        // tooltip after a short delay, to allow for the tag
        // to be inserted in the DOM.
        if(!empty($this->tooltip['text'])) 
        {
            $this->tooltipify();
        }

        return $tag;
    }

    private function resolveAttributes() : array
    {
        $atts = $this->attributes;
        
        $atts['id'] = $this->id;
        $atts['class'] = implode(' ', $this->resolveClasses());
        
        $tooltip = $this->getTooltip();
        
        if(!empty($tooltip)) 
        {
            $atts['title'] = $tooltip;
        }
        
        if(!empty($this->styles)) 
        {
            $atts['style'] = ConvertHelper::array2styleString($this->styles);
        }
        
        return $atts;
    }
    
    public function getID() : string
    {
        return $this->id;
    }
    
    public function getTooltip() : string
    {
        return (string)$this->tooltip['text'];
    }

    protected function tooltipify() : void
    {
        JSHelper::tooltipify($this->getID(), $this->tooltip['placement']);
    }

    /**
     * Override the toString method to allow an easier syntax
     * without having to call the render method manually.
     */
    public function __toString()
    {
        return $this->render();
    }
    
   /**
    * Displays a help cursor when hovering over the icon.
    * @return UI_Icon
    */
    public function cursorHelp() : UI_Icon
    {
        return $this->setStyle('cursor', 'help');
    }

   /**
    * Sets a style for the icon's <code>style</code> attribute.
    * 
    * Example:
    * 
    * <pre>
    * $icon->setStyle('margin-right', '10px');
    * </pre>
    * 
    * @param string $name
    * @param string $value
    * @return UI_Icon
    */
    public function setStyle(string $name, string $value) : UI_Icon
    {
        $this->styles[$name] = $value;
        return $this;
    }

    public function removeStyle(string $name) : UI_Icon
    {
        if(isset($this->styles[$name]))
        {
            unset($this->styles[$name]);
        }

        return $this;
    }

    protected $styles = array();

    public const TOOLTIP_POSITION_TOP = 'top';
    public const TOOLTIP_POSITION_BOTTOM = 'bottom';
    public const TOOLTIP_POSITION_LEFT = 'left';
    public const TOOLTIP_POSITION_RIGHT = 'right';

    public static function requireValidTooltipPosition(string $pos) : void
    {
        $validPositions = array(
            self::TOOLTIP_POSITION_TOP,
            self::TOOLTIP_POSITION_BOTTOM,
            self::TOOLTIP_POSITION_LEFT,
            self::TOOLTIP_POSITION_RIGHT
        );

        if(in_array($pos, $validPositions))
        {
            return;
        }

        throw new UI_Exception(
            'Invalid icon tooltip position.',
            sprintf(
                'The position [%s] is invalid. Valid positions are: [%s].',
                $pos,
                implode(', ', $validPositions)
            ),
            self::ERROR_INVALID_TOOLTIP_POSITION
        );
    }

    /**
     * Sets the position for the tooltip, if one is used.
     *
     * @param string $position "top" (default), "left", "right", "bottom"
     * @return UI_Icon
     * @throws UI_Exception
     */
    public function setTooltipPosition(string $position=self::TOOLTIP_POSITION_TOP) : UI_Icon
    {
        self::requireValidTooltipPosition($position);
        
        $this->tooltip['placement'] = $position;
        return $this;
    }
    
    public function makeTooltipTop() : UI_Icon
    {
        return $this->setTooltipPosition('top');
    }

    public function makeTooltipLeft() : UI_Icon
    {
        return $this->setTooltipPosition('left');
    }

    public function makeTooltipRight() : UI_Icon
    {
        return $this->setTooltipPosition('right');
    }

    public function makeTooltipBottom() : UI_Icon
    {
        return $this->setTooltipPosition('bottom');
    }

    /**
     * @return array<string,string>
     */
    public function getIconTypes() : array
    {
        return self::$types; 
    }
    
    public function setHidden(bool $hidden=true) : UI_Icon
    {
        if($hidden)
        {
            return $this->setStyle('display', 'none');
        }

        return $this->removeStyle('display');
    }
}