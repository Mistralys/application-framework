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

    /**
     * @var array<string,string>
     */
    protected $types = array(
        'ACTIONCODE' => 'rocket',
        'ACTIVATE' => 'far:sun',
        'ADD' => 'plus-circle',
        'ATTENTION_REQUIRED' => 'exclamation-triangle',
        'BACK' => 'arrow-circle-left',
        'BACKUP' => 'recycle',
        'BACK_TO_CURRENT' => 'fas:level-down-alt',
        'BROWSE' => 'folder-open',
        'BUGREPORT' => 'bug',
        'CALENDAR' => 'calendar',
        'CAMPAIGNS' => 'flag',
        'CANCEL' => 'ban',
        'CARET_DOWN' => 'caret-down',
        'CARET_UP' => 'caret-up',
        'CATEGORY' => 'bars',
        'CHANGELOG' => 'edit',
        'CHANGE_ORDER' => 'bars',
        'COLLAPSE' => 'minus-circle',
        'COMBINATION' => 'object-group',
        'COMBINE' => 'link',
        'COMMENT' => 'comment',
        'CONVERT' => 'cogs',
        'COPY' => 'copy',
        'COUNTDOWN' => 'far:clock',
        'CSV' => 'far:file-alt',
        'DEACTIVATE' => 'far:moon',
        'DEACTIVATED' => 'far:moon',
        'DELETE' => 'times',
        'DELETED' => 'times',
        'DELETE_SIGN' => 'far:times-circle',
        'DESELECT_ALL' => 'far:minus-square',
        'DESTROY' => 'exclamation-triangle',
        'DEVELOPER' => 'asterisk',
        'DISABLED' => 'ban',
        'DISCARD' => 'far:trash-alt',
        'DISCONNECT' => 'unlink',
        'DOWNLOAD' => 'download',
        'DRAFT' => 'puzzle-piece',
        'DRAG' => 'bars',
        'DROPDOWN' => 'caret-down',
        'EDIT' => 'fas:pencil-alt',
        'EDITOR' => 'cubes',
        'ENABLED' => 'fas:check-circle',
        'EXPAND' => 'plus-circle',
        'EXPORT' => 'fas:bolt',
        'EXPORT_ARCHIVE' => 'archive',
        'FEATURETABLES' => 'server',
        'FEEDBACK' => 'far:thumbs-up',
        'FILTER' => 'filter',
        'FIRST' => 'step-backward',
        'FORWARD' => 'arrow-circle-right',
        'GENERATE' => 'fas:bolt',
        'GLOBAL_CONTENT' => 'cube',
        'HELP' => 'question-circle',
        'HIDE' => 'eye-slash',
        'HOME' => 'home',
        'HTML' => 'code',
        'ID' => 'key',
        'IMAGE' => 'image',
        'IMPORT' => 'briefcase',
        'INFORMATION' => 'info-circle',
        'INACTIVE' => 'far:moon',
        'ITEM_ACTIVE' => 'circle',
        'ITEM_INACTIVE' => 'far:circle',
        'JUMP_UP' => 'arrow-up',
        'JUMP_TO' => 'far:arrow-alt-circle-right',
        'KEYWORD' => 'bookmark',
        'LAST' => 'step-forward',
        'LINK' => 'link',
        'LOAD' => 'far:folder-open',
        'LOCKED' => 'lock',
        'LOG_IN' => 'fas:sign-in-alt',
        'LOG_OUT' => 'power-off',
        'OFF' => 'power-off',
        'ON' => 'far:dot-circle',
        'LOOKUP' => 'ellipsis-h',
        'MAILS' => 'envelope',
        'MAIL_HEADERS' => 'crosshairs',
        'MAIL_HEADER_TITLE' => 'fas:heading',
        'MAXIMIZE' => 'expand',
        'MEDIA' => 'image',
        'MENU' => 'bars',
        'MERGE' => 'fas:level-down-alt',
        'MESSAGE' => 'far:comment-alt',
        'MINUS' => 'minus',
        'MOVE' => 'arrows-alt',
        'MOVE_LEFT_RIGHT' => 'fas:arrows-alt-h',
        'MOVE_TO' => 'fas:sign-out-alt',
        'MOVE_UP_DOWN' => 'fas:arrows-alt-v',
        'NEXT' => 'chevron-right',
        'NO' => 'times',
        'NOT_AVAILABLE' => 'ban',
        'NOT_REQUIRED' => 'minus',
        'OK' => 'check',
        'OMS' => 'fab:telegram',
        'OPTIONS' => 'far:dot-circle',
        'PAGE' => 'file',
        'PAGEMODEL' => 'far:newspaper',
        'PAUSE' => 'pause',
        'PLAY' => 'play',
        'PLUS' => 'plus',
        'POSITION_ANY' => 'fas:sort',
        'POSITION_BOTTOM' => 'arrow-circle-down',
        'POSITION_TOP' => 'arrow-circle-up',
        'PRESETS' => 'server',
        'LIST' => 'server',
        'PREVIEW' => 'far:file-code',
        'PREVIOUS' => 'chevron-left',
        'PRINT' => 'print',
        'PRINTER' => 'print',
        'PRODUCT' => 'shopping-basket',
        'PROMS' => 'database',
        'PROOFING' => 'far:check-square',
        'PROPERTIES' => 'fas:cogs',
        'PUBLISH' => 'fas:sign-out-alt',
        'PUBLISHED' => 'check',
        'REFRESH' => 'fas:sync',
        'REQUIRED' => 'exclamation-circle',
        'RESET' => 'minus-square',
        'RESTORE' => 'share',
        'REVERT' => 'history',
        'SAVE' => 'save',
        'SEARCH' => 'search',
        'SELECTED' => 'list',
        'SELECT_ALL' => 'far:plus-square',
        'SEND' => 'envelope',
        'SETTINGS' => 'wrench',
        'SHOP' => 'shopping-cart',
        'SORT' => 'sort',
        'SORTING' => 'fas:sort-amount-down',
        'SORT_ASC' => 'angle-up',
        'SORT_DESC' => 'angle-down',
        'SPINNER' => 'spinner',
        'STRUCTURAL' => 'cubes',
        'STOP' => 'pause',
        'SWITCH' => 'retweet',
        'SWITCH_CAMPAIGN' => 'fas:exchange-alt',
        'SWITCH_MODE' => 'compass',
        'TABLE' => 'table',
        'TARIFF_MATRIX' => 'table',
        'TEMPLATE' => 'far:file-alt',
        'TEXT' => 'font',
        'TOGGLE' => 'retweet',
        'TRANSLATION' => 'globe',
        'UNCOMBINE' => 'unlink',
        'UNCOMBINED' => 'object-ungroup',
        'UNDELETE' => 'reply',
        'UNLOCK' => 'unlock',
        'UNLOCKED' => 'unlock',
        'UPLOAD' => 'upload',
        'USER' => 'user',
        'VALIDATE' => 'check-circle',
        'VARIABLES' => 'code-branch',
        'CUSTOM_VARIABLES' => 'project-diagram',
        'VIEW' => 'eye',
        'WAITING' => 'far:clock',
        'WARNING' => 'fas:exclamation-triangle',
        'WHITELIST' => 'far:star',
        'WORKFLOW' => 'sitemap',
        'XML' => 'code',
        'YES' => 'check',
        'WORDWRAP' => 'fas:terminal',
        'UTILS' => 'fas:first-aid',
        'RATING' => 'star',
        'TOOLS' => 'tools',
        'BOX' => 'archive',
        'COMMANDS' => 'terminal',
        'CHECK' => 'check-double',
        'ACTIVITY' => 'bullhorn',
        'GROUPED' => 'layer-group',
        'FLAT' => 'th',
        'BUTTON' => 'external-link-square-alt',
        'SUGGEST' => 'lightbulb',
        'FILE' => 'file-alt',
        'STATUS' => 'shield-alt',
        'REVIEW' => 'user-edit',
        'GLOBAL' => 'globe-europe',
        'USERS' => 'users',
        'COMTYPES' => 'broadcast-tower',
        'VARIATIONS' => 'sitemap',
        'BUILD' => 'magic',
        'CODE' => 'code',
        'MAIL_TESTS' => 'envelope-open-text',
        'COUNTRIES' => 'far:flag',
        'EMAIL' => 'at',
        'AUDIENCE' => 'podcast',
        'TIME' => 'clock',
        'TASK' => 'tasks',
        'BUSINESS' => 'university',
        'TENANT' => 'award',
        'COLORS' => 'palette',
        'MONEY' => 'money-check-alt',
        'PRICE' => 'money-check-alt',
        'COLLAPSE_RIGHT' => 'caret-square-right',
        'COLLAPSE_LEFT' => 'caret-square-left',
        'EXPAND_RIGHT' => 'caret-square-right',
        'EXPAND_LEFT' => 'caret-square-left',
        'NOTEPAD' => 'far:sticky-note',
        'PIN' => 'thumbtack',
        'RECORD_TYPE' => 'bezier-curve',
        'TRANSMISSION' => 'satellite-dish',
        'CONTENT_TYPES' => 'fab:elementor'
    );

    public function __construct()
    {
        $this->id = 'ic'.nextJSID();
    }

    // region: Icon type methods

    /* START METHODS */
    public function actioncode() : UI_Icon { return $this->setType('ACTIONCODE'); }
    public function activate() : UI_Icon { return $this->setType('ACTIVATE'); }
    public function activity() : UI_Icon { return $this->setType('ACTIVITY'); }
    public function add() : UI_Icon { return $this->setType('ADD'); }
    public function attentionRequired() : UI_Icon { return $this->setType('ATTENTION_REQUIRED'); }
    public function audience() : UI_Icon { return $this->setType('AUDIENCE'); }
    public function back() : UI_Icon { return $this->setType('BACK'); }
    public function backup() : UI_Icon { return $this->setType('BACKUP'); }
    public function backToCurrent() : UI_Icon { return $this->setType('BACK_TO_CURRENT'); }
    public function box() : UI_Icon { return $this->setType('BOX'); }
    public function browse() : UI_Icon { return $this->setType('BROWSE'); }
    public function bugreport() : UI_Icon { return $this->setType('BUGREPORT'); }
    public function build() : UI_Icon { return $this->setType('BUILD'); }
    public function business() : UI_Icon { return $this->setType('BUSINESS'); }
    public function button() : UI_Icon { return $this->setType('BUTTON'); }
    public function calendar() : UI_Icon { return $this->setType('CALENDAR'); }
    public function campaigns() : UI_Icon { return $this->setType('CAMPAIGNS'); }
    public function cancel() : UI_Icon { return $this->setType('CANCEL'); }
    public function caretDown() : UI_Icon { return $this->setType('CARET_DOWN'); }
    public function caretUp() : UI_Icon { return $this->setType('CARET_UP'); }
    public function category() : UI_Icon { return $this->setType('CATEGORY'); }
    public function changelog() : UI_Icon { return $this->setType('CHANGELOG'); }
    public function changeOrder() : UI_Icon { return $this->setType('CHANGE_ORDER'); }
    public function check() : UI_Icon { return $this->setType('CHECK'); }
    public function code() : UI_Icon { return $this->setType('CODE'); }
    public function collapse() : UI_Icon { return $this->setType('COLLAPSE'); }
    public function collapseLeft() : UI_Icon { return $this->setType('COLLAPSE_LEFT'); }
    public function collapseRight() : UI_Icon { return $this->setType('COLLAPSE_RIGHT'); }
    public function colors() : UI_Icon { return $this->setType('COLORS'); }
    public function combination() : UI_Icon { return $this->setType('COMBINATION'); }
    public function combine() : UI_Icon { return $this->setType('COMBINE'); }
    public function commands() : UI_Icon { return $this->setType('COMMANDS'); }
    public function comment() : UI_Icon { return $this->setType('COMMENT'); }
    public function comtypes() : UI_Icon { return $this->setType('COMTYPES'); }
    public function contentTypes() : UI_Icon { return $this->setType('CONTENT_TYPES'); }
    public function convert() : UI_Icon { return $this->setType('CONVERT'); }
    public function copy() : UI_Icon { return $this->setType('COPY'); }
    public function countdown() : UI_Icon { return $this->setType('COUNTDOWN'); }
    public function countries() : UI_Icon { return $this->setType('COUNTRIES'); }
    public function csv() : UI_Icon { return $this->setType('CSV'); }
    public function customVariables() : UI_Icon { return $this->setType('CUSTOM_VARIABLES'); }
    public function deactivate() : UI_Icon { return $this->setType('DEACTIVATE'); }
    public function deactivated() : UI_Icon { return $this->setType('DEACTIVATED'); }
    public function delete() : UI_Icon { return $this->setType('DELETE'); }
    public function deleted() : UI_Icon { return $this->setType('DELETED'); }
    public function deleteSign() : UI_Icon { return $this->setType('DELETE_SIGN'); }
    public function deselectAll() : UI_Icon { return $this->setType('DESELECT_ALL'); }
    public function destroy() : UI_Icon { return $this->setType('DESTROY'); }
    public function developer() : UI_Icon { return $this->setType('DEVELOPER'); }
    public function disabled() : UI_Icon { return $this->setType('DISABLED'); }
    public function discard() : UI_Icon { return $this->setType('DISCARD'); }
    public function disconnect() : UI_Icon { return $this->setType('DISCONNECT'); }
    public function download() : UI_Icon { return $this->setType('DOWNLOAD'); }
    public function draft() : UI_Icon { return $this->setType('DRAFT'); }
    public function drag() : UI_Icon { return $this->setType('DRAG'); }
    public function dropdown() : UI_Icon { return $this->setType('DROPDOWN'); }
    public function edit() : UI_Icon { return $this->setType('EDIT'); }
    public function editor() : UI_Icon { return $this->setType('EDITOR'); }
    public function email() : UI_Icon { return $this->setType('EMAIL'); }
    public function enabled() : UI_Icon { return $this->setType('ENABLED'); }
    public function expand() : UI_Icon { return $this->setType('EXPAND'); }
    public function expandLeft() : UI_Icon { return $this->setType('EXPAND_LEFT'); }
    public function expandRight() : UI_Icon { return $this->setType('EXPAND_RIGHT'); }
    public function export() : UI_Icon { return $this->setType('EXPORT'); }
    public function exportArchive() : UI_Icon { return $this->setType('EXPORT_ARCHIVE'); }
    public function featuretables() : UI_Icon { return $this->setType('FEATURETABLES'); }
    public function feedback() : UI_Icon { return $this->setType('FEEDBACK'); }
    public function file() : UI_Icon { return $this->setType('FILE'); }
    public function filter() : UI_Icon { return $this->setType('FILTER'); }
    public function first() : UI_Icon { return $this->setType('FIRST'); }
    public function flat() : UI_Icon { return $this->setType('FLAT'); }
    public function forward() : UI_Icon { return $this->setType('FORWARD'); }
    public function generate() : UI_Icon { return $this->setType('GENERATE'); }
    public function global() : UI_Icon { return $this->setType('GLOBAL'); }
    public function globalContent() : UI_Icon { return $this->setType('GLOBAL_CONTENT'); }
    public function grouped() : UI_Icon { return $this->setType('GROUPED'); }
    public function help() : UI_Icon { return $this->setType('HELP'); }
    public function hide() : UI_Icon { return $this->setType('HIDE'); }
    public function home() : UI_Icon { return $this->setType('HOME'); }
    public function html() : UI_Icon { return $this->setType('HTML'); }
    public function id() : UI_Icon { return $this->setType('ID'); }
    public function image() : UI_Icon { return $this->setType('IMAGE'); }
    public function import() : UI_Icon { return $this->setType('IMPORT'); }
    public function inactive() : UI_Icon { return $this->setType('INACTIVE'); }
    public function information() : UI_Icon { return $this->setType('INFORMATION'); }
    public function itemActive() : UI_Icon { return $this->setType('ITEM_ACTIVE'); }
    public function itemInactive() : UI_Icon { return $this->setType('ITEM_INACTIVE'); }
    public function jumpTo() : UI_Icon { return $this->setType('JUMP_TO'); }
    public function jumpUp() : UI_Icon { return $this->setType('JUMP_UP'); }
    public function keyword() : UI_Icon { return $this->setType('KEYWORD'); }
    public function last() : UI_Icon { return $this->setType('LAST'); }
    public function link() : UI_Icon { return $this->setType('LINK'); }
    public function list() : UI_Icon { return $this->setType('LIST'); }
    public function load() : UI_Icon { return $this->setType('LOAD'); }
    public function locked() : UI_Icon { return $this->setType('LOCKED'); }
    public function logIn() : UI_Icon { return $this->setType('LOG_IN'); }
    public function logOut() : UI_Icon { return $this->setType('LOG_OUT'); }
    public function lookup() : UI_Icon { return $this->setType('LOOKUP'); }
    public function mails() : UI_Icon { return $this->setType('MAILS'); }
    public function mailHeaders() : UI_Icon { return $this->setType('MAIL_HEADERS'); }
    public function mailHeaderTitle() : UI_Icon { return $this->setType('MAIL_HEADER_TITLE'); }
    public function mailTests() : UI_Icon { return $this->setType('MAIL_TESTS'); }
    public function maximize() : UI_Icon { return $this->setType('MAXIMIZE'); }
    public function media() : UI_Icon { return $this->setType('MEDIA'); }
    public function menu() : UI_Icon { return $this->setType('MENU'); }
    public function merge() : UI_Icon { return $this->setType('MERGE'); }
    public function message() : UI_Icon { return $this->setType('MESSAGE'); }
    public function minus() : UI_Icon { return $this->setType('MINUS'); }
    public function money() : UI_Icon { return $this->setType('MONEY'); }
    public function move() : UI_Icon { return $this->setType('MOVE'); }
    public function moveLeftRight() : UI_Icon { return $this->setType('MOVE_LEFT_RIGHT'); }
    public function moveTo() : UI_Icon { return $this->setType('MOVE_TO'); }
    public function moveUpDown() : UI_Icon { return $this->setType('MOVE_UP_DOWN'); }
    public function next() : UI_Icon { return $this->setType('NEXT'); }
    public function no() : UI_Icon { return $this->setType('NO'); }
    public function notepad() : UI_Icon { return $this->setType('NOTEPAD'); }
    public function notAvailable() : UI_Icon { return $this->setType('NOT_AVAILABLE'); }
    public function notRequired() : UI_Icon { return $this->setType('NOT_REQUIRED'); }
    public function off() : UI_Icon { return $this->setType('OFF'); }
    public function ok() : UI_Icon { return $this->setType('OK'); }
    public function oms() : UI_Icon { return $this->setType('OMS'); }
    public function on() : UI_Icon { return $this->setType('ON'); }
    public function options() : UI_Icon { return $this->setType('OPTIONS'); }
    public function page() : UI_Icon { return $this->setType('PAGE'); }
    public function pagemodel() : UI_Icon { return $this->setType('PAGEMODEL'); }
    public function pause() : UI_Icon { return $this->setType('PAUSE'); }
    public function pin() : UI_Icon { return $this->setType('PIN'); }
    public function play() : UI_Icon { return $this->setType('PLAY'); }
    public function plus() : UI_Icon { return $this->setType('PLUS'); }
    public function positionAny() : UI_Icon { return $this->setType('POSITION_ANY'); }
    public function positionBottom() : UI_Icon { return $this->setType('POSITION_BOTTOM'); }
    public function positionTop() : UI_Icon { return $this->setType('POSITION_TOP'); }
    public function presets() : UI_Icon { return $this->setType('PRESETS'); }
    public function preview() : UI_Icon { return $this->setType('PREVIEW'); }
    public function previous() : UI_Icon { return $this->setType('PREVIOUS'); }
    public function price() : UI_Icon { return $this->setType('PRICE'); }
    public function print() : UI_Icon { return $this->setType('PRINT'); }
    public function printer() : UI_Icon { return $this->setType('PRINTER'); }
    public function product() : UI_Icon { return $this->setType('PRODUCT'); }
    public function proms() : UI_Icon { return $this->setType('PROMS'); }
    public function proofing() : UI_Icon { return $this->setType('PROOFING'); }
    public function properties() : UI_Icon { return $this->setType('PROPERTIES'); }
    public function publish() : UI_Icon { return $this->setType('PUBLISH'); }
    public function published() : UI_Icon { return $this->setType('PUBLISHED'); }
    public function rating() : UI_Icon { return $this->setType('RATING'); }
    public function recordType() : UI_Icon { return $this->setType('RECORD_TYPE'); }
    public function refresh() : UI_Icon { return $this->setType('REFRESH'); }
    public function required() : UI_Icon { return $this->setType('REQUIRED'); }
    public function reset() : UI_Icon { return $this->setType('RESET'); }
    public function restore() : UI_Icon { return $this->setType('RESTORE'); }
    public function revert() : UI_Icon { return $this->setType('REVERT'); }
    public function review() : UI_Icon { return $this->setType('REVIEW'); }
    public function save() : UI_Icon { return $this->setType('SAVE'); }
    public function search() : UI_Icon { return $this->setType('SEARCH'); }
    public function selected() : UI_Icon { return $this->setType('SELECTED'); }
    public function selectAll() : UI_Icon { return $this->setType('SELECT_ALL'); }
    public function send() : UI_Icon { return $this->setType('SEND'); }
    public function settings() : UI_Icon { return $this->setType('SETTINGS'); }
    public function shop() : UI_Icon { return $this->setType('SHOP'); }
    public function sort() : UI_Icon { return $this->setType('SORT'); }
    public function sorting() : UI_Icon { return $this->setType('SORTING'); }
    public function sortAsc() : UI_Icon { return $this->setType('SORT_ASC'); }
    public function sortDesc() : UI_Icon { return $this->setType('SORT_DESC'); }
    public function status() : UI_Icon { return $this->setType('STATUS'); }
    public function stop() : UI_Icon { return $this->setType('STOP'); }
    public function structural() : UI_Icon { return $this->setType('STRUCTURAL'); }
    public function suggest() : UI_Icon { return $this->setType('SUGGEST'); }
    public function switch() : UI_Icon { return $this->setType('SWITCH'); }
    public function switchCampaign() : UI_Icon { return $this->setType('SWITCH_CAMPAIGN'); }
    public function switchMode() : UI_Icon { return $this->setType('SWITCH_MODE'); }
    public function table() : UI_Icon { return $this->setType('TABLE'); }
    public function tariffMatrix() : UI_Icon { return $this->setType('TARIFF_MATRIX'); }
    public function task() : UI_Icon { return $this->setType('TASK'); }
    public function template() : UI_Icon { return $this->setType('TEMPLATE'); }
    public function tenant() : UI_Icon { return $this->setType('TENANT'); }
    public function text() : UI_Icon { return $this->setType('TEXT'); }
    public function time() : UI_Icon { return $this->setType('TIME'); }
    public function toggle() : UI_Icon { return $this->setType('TOGGLE'); }
    public function tools() : UI_Icon { return $this->setType('TOOLS'); }
    public function translation() : UI_Icon { return $this->setType('TRANSLATION'); }
    public function transmission() : UI_Icon { return $this->setType('TRANSMISSION'); }
    public function uncombine() : UI_Icon { return $this->setType('UNCOMBINE'); }
    public function uncombined() : UI_Icon { return $this->setType('UNCOMBINED'); }
    public function undelete() : UI_Icon { return $this->setType('UNDELETE'); }
    public function unlock() : UI_Icon { return $this->setType('UNLOCK'); }
    public function unlocked() : UI_Icon { return $this->setType('UNLOCKED'); }
    public function upload() : UI_Icon { return $this->setType('UPLOAD'); }
    public function user() : UI_Icon { return $this->setType('USER'); }
    public function users() : UI_Icon { return $this->setType('USERS'); }
    public function utils() : UI_Icon { return $this->setType('UTILS'); }
    public function validate() : UI_Icon { return $this->setType('VALIDATE'); }
    public function variables() : UI_Icon { return $this->setType('VARIABLES'); }
    public function variations() : UI_Icon { return $this->setType('VARIATIONS'); }
    public function view() : UI_Icon { return $this->setType('VIEW'); }
    public function waiting() : UI_Icon { return $this->setType('WAITING'); }
    public function warning() : UI_Icon { return $this->setType('WARNING'); }
    public function whitelist() : UI_Icon { return $this->setType('WHITELIST'); }
    public function wordwrap() : UI_Icon { return $this->setType('WORDWRAP'); }
    public function workflow() : UI_Icon { return $this->setType('WORKFLOW'); }
    public function xml() : UI_Icon { return $this->setType('XML'); }
    public function yes() : UI_Icon { return $this->setType('YES'); }
    /* END METHODS */

    // endregion

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
        $this->type = $type;
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

        if(isset($this->types[$type]))
        {
            return $this->types[$type];
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
        return $this->types; 
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