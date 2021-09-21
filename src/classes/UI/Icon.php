<?php

use AppUtils\ConvertHelper;
use AppUtils\Interface_Stringable;

class UI_Icon implements Interface_Stringable
{
    protected $type = null;

    protected $classes = array();

    protected $tooltip = array(
        'text' => '',
        'placement' => 'top'
    );

    protected $id = null;

    protected $attributes = array();

    protected $prefix = 'fa';
    
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
        'ENABLED' => 'check',
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
        'TRANSMISSION' => 'satellite-dish'
    );

    public function __construct()
    {
        $this->id = 'ic'.nextJSID();
    }

    /* START METHODS */
    public function actioncode() { return $this->setType('ACTIONCODE'); }
    public function activate() { return $this->setType('ACTIVATE'); }
    public function activity() { return $this->setType('ACTIVITY'); }
    public function add() { return $this->setType('ADD'); }
    public function attentionRequired() { return $this->setType('ATTENTION_REQUIRED'); }
    public function audience() { return $this->setType('AUDIENCE'); }
    public function back() { return $this->setType('BACK'); }
    public function backup() { return $this->setType('BACKUP'); }
    public function backToCurrent() { return $this->setType('BACK_TO_CURRENT'); }
    public function box() { return $this->setType('BOX'); }
    public function browse() { return $this->setType('BROWSE'); }
    public function bugreport() { return $this->setType('BUGREPORT'); }
    public function build() { return $this->setType('BUILD'); }
    public function business() { return $this->setType('BUSINESS'); }
    public function button() { return $this->setType('BUTTON'); }
    public function calendar() { return $this->setType('CALENDAR'); }
    public function campaigns() { return $this->setType('CAMPAIGNS'); }
    public function cancel() { return $this->setType('CANCEL'); }
    public function caretDown() { return $this->setType('CARET_DOWN'); }
    public function caretUp() { return $this->setType('CARET_UP'); }
    public function category() { return $this->setType('CATEGORY'); }
    public function changelog() { return $this->setType('CHANGELOG'); }
    public function changeOrder() { return $this->setType('CHANGE_ORDER'); }
    public function check() { return $this->setType('CHECK'); }
    public function code() { return $this->setType('CODE'); }
    public function collapse() { return $this->setType('COLLAPSE'); }
    public function collapseLeft() { return $this->setType('COLLAPSE_LEFT'); }
    public function collapseRight() { return $this->setType('COLLAPSE_RIGHT'); }
    public function colors() { return $this->setType('COLORS'); }
    public function combination() { return $this->setType('COMBINATION'); }
    public function combine() { return $this->setType('COMBINE'); }
    public function commands() { return $this->setType('COMMANDS'); }
    public function comment() { return $this->setType('COMMENT'); }
    public function comtypes() { return $this->setType('COMTYPES'); }
    public function convert() { return $this->setType('CONVERT'); }
    public function copy() { return $this->setType('COPY'); }
    public function countdown() { return $this->setType('COUNTDOWN'); }
    public function countries() { return $this->setType('COUNTRIES'); }
    public function csv() { return $this->setType('CSV'); }
    public function customVariables() { return $this->setType('CUSTOM_VARIABLES'); }
    public function deactivate() { return $this->setType('DEACTIVATE'); }
    public function deactivated() { return $this->setType('DEACTIVATED'); }
    public function delete() { return $this->setType('DELETE'); }
    public function deleted() { return $this->setType('DELETED'); }
    public function deleteSign() { return $this->setType('DELETE_SIGN'); }
    public function deselectAll() { return $this->setType('DESELECT_ALL'); }
    public function destroy() { return $this->setType('DESTROY'); }
    public function developer() { return $this->setType('DEVELOPER'); }
    public function disabled() { return $this->setType('DISABLED'); }
    public function discard() { return $this->setType('DISCARD'); }
    public function disconnect() { return $this->setType('DISCONNECT'); }
    public function download() { return $this->setType('DOWNLOAD'); }
    public function draft() { return $this->setType('DRAFT'); }
    public function drag() { return $this->setType('DRAG'); }
    public function dropdown() { return $this->setType('DROPDOWN'); }
    public function edit() { return $this->setType('EDIT'); }
    public function editor() { return $this->setType('EDITOR'); }
    public function email() { return $this->setType('EMAIL'); }
    public function enabled() { return $this->setType('ENABLED'); }
    public function expand() { return $this->setType('EXPAND'); }
    public function expandLeft() { return $this->setType('EXPAND_LEFT'); }
    public function expandRight() { return $this->setType('EXPAND_RIGHT'); }
    public function export() { return $this->setType('EXPORT'); }
    public function exportArchive() { return $this->setType('EXPORT_ARCHIVE'); }
    public function featuretables() { return $this->setType('FEATURETABLES'); }
    public function feedback() { return $this->setType('FEEDBACK'); }
    public function file() { return $this->setType('FILE'); }
    public function filter() { return $this->setType('FILTER'); }
    public function first() { return $this->setType('FIRST'); }
    public function flat() { return $this->setType('FLAT'); }
    public function forward() { return $this->setType('FORWARD'); }
    public function generate() { return $this->setType('GENERATE'); }
    public function global() { return $this->setType('GLOBAL'); }
    public function globalContent() { return $this->setType('GLOBAL_CONTENT'); }
    public function grouped() { return $this->setType('GROUPED'); }
    public function help() { return $this->setType('HELP'); }
    public function hide() { return $this->setType('HIDE'); }
    public function home() { return $this->setType('HOME'); }
    public function html() { return $this->setType('HTML'); }
    public function id() { return $this->setType('ID'); }
    public function image() { return $this->setType('IMAGE'); }
    public function import() { return $this->setType('IMPORT'); }
    public function inactive() { return $this->setType('INACTIVE'); }
    public function information() { return $this->setType('INFORMATION'); }
    public function itemActive() { return $this->setType('ITEM_ACTIVE'); }
    public function itemInactive() { return $this->setType('ITEM_INACTIVE'); }
    public function jumpTo() { return $this->setType('JUMP_TO'); }
    public function jumpUp() { return $this->setType('JUMP_UP'); }
    public function keyword() { return $this->setType('KEYWORD'); }
    public function last() { return $this->setType('LAST'); }
    public function link() { return $this->setType('LINK'); }
    public function list() { return $this->setType('LIST'); }
    public function load() { return $this->setType('LOAD'); }
    public function locked() { return $this->setType('LOCKED'); }
    public function logIn() { return $this->setType('LOG_IN'); }
    public function logOut() { return $this->setType('LOG_OUT'); }
    public function lookup() { return $this->setType('LOOKUP'); }
    public function mails() { return $this->setType('MAILS'); }
    public function mailHeaders() { return $this->setType('MAIL_HEADERS'); }
    public function mailHeaderTitle() { return $this->setType('MAIL_HEADER_TITLE'); }
    public function mailTests() { return $this->setType('MAIL_TESTS'); }
    public function maximize() { return $this->setType('MAXIMIZE'); }
    public function media() { return $this->setType('MEDIA'); }
    public function menu() { return $this->setType('MENU'); }
    public function merge() { return $this->setType('MERGE'); }
    public function message() { return $this->setType('MESSAGE'); }
    public function minus() { return $this->setType('MINUS'); }
    public function money() { return $this->setType('MONEY'); }
    public function move() { return $this->setType('MOVE'); }
    public function moveLeftRight() { return $this->setType('MOVE_LEFT_RIGHT'); }
    public function moveTo() { return $this->setType('MOVE_TO'); }
    public function moveUpDown() { return $this->setType('MOVE_UP_DOWN'); }
    public function next() { return $this->setType('NEXT'); }
    public function no() { return $this->setType('NO'); }
    public function notepad() { return $this->setType('NOTEPAD'); }
    public function notAvailable() { return $this->setType('NOT_AVAILABLE'); }
    public function notRequired() { return $this->setType('NOT_REQUIRED'); }
    public function ok() { return $this->setType('OK'); }
    public function oms() { return $this->setType('OMS'); }
    public function options() { return $this->setType('OPTIONS'); }
    public function page() { return $this->setType('PAGE'); }
    public function pagemodel() { return $this->setType('PAGEMODEL'); }
    public function pause() { return $this->setType('PAUSE'); }
    public function pin() { return $this->setType('PIN'); }
    public function play() { return $this->setType('PLAY'); }
    public function plus() { return $this->setType('PLUS'); }
    public function positionAny() { return $this->setType('POSITION_ANY'); }
    public function positionBottom() { return $this->setType('POSITION_BOTTOM'); }
    public function positionTop() { return $this->setType('POSITION_TOP'); }
    public function presets() { return $this->setType('PRESETS'); }
    public function preview() { return $this->setType('PREVIEW'); }
    public function previous() { return $this->setType('PREVIOUS'); }
    public function price() { return $this->setType('PRICE'); }
    public function print() { return $this->setType('PRINT'); }
    public function printer() { return $this->setType('PRINTER'); }
    public function product() { return $this->setType('PRODUCT'); }
    public function proms() { return $this->setType('PROMS'); }
    public function proofing() { return $this->setType('PROOFING'); }
    public function properties() { return $this->setType('PROPERTIES'); }
    public function publish() { return $this->setType('PUBLISH'); }
    public function published() { return $this->setType('PUBLISHED'); }
    public function rating() { return $this->setType('RATING'); }
    public function recordType() { return $this->setType('RECORD_TYPE'); }
    public function refresh() { return $this->setType('REFRESH'); }
    public function required() { return $this->setType('REQUIRED'); }
    public function reset() { return $this->setType('RESET'); }
    public function restore() { return $this->setType('RESTORE'); }
    public function revert() { return $this->setType('REVERT'); }
    public function review() { return $this->setType('REVIEW'); }
    public function save() { return $this->setType('SAVE'); }
    public function search() { return $this->setType('SEARCH'); }
    public function selected() { return $this->setType('SELECTED'); }
    public function selectAll() { return $this->setType('SELECT_ALL'); }
    public function send() { return $this->setType('SEND'); }
    public function settings() { return $this->setType('SETTINGS'); }
    public function shop() { return $this->setType('SHOP'); }
    public function sort() { return $this->setType('SORT'); }
    public function sorting() { return $this->setType('SORTING'); }
    public function sortAsc() { return $this->setType('SORT_ASC'); }
    public function sortDesc() { return $this->setType('SORT_DESC'); }
    public function status() { return $this->setType('STATUS'); }
    public function stop() { return $this->setType('STOP'); }
    public function structural() { return $this->setType('STRUCTURAL'); }
    public function suggest() { return $this->setType('SUGGEST'); }
    public function switch() { return $this->setType('SWITCH'); }
    public function switchCampaign() { return $this->setType('SWITCH_CAMPAIGN'); }
    public function switchMode() { return $this->setType('SWITCH_MODE'); }
    public function table() { return $this->setType('TABLE'); }
    public function tariffMatrix() { return $this->setType('TARIFF_MATRIX'); }
    public function task() { return $this->setType('TASK'); }
    public function template() { return $this->setType('TEMPLATE'); }
    public function tenant() { return $this->setType('TENANT'); }
    public function text() { return $this->setType('TEXT'); }
    public function time() { return $this->setType('TIME'); }
    public function toggle() { return $this->setType('TOGGLE'); }
    public function tools() { return $this->setType('TOOLS'); }
    public function translation() { return $this->setType('TRANSLATION'); }
    public function transmission() { return $this->setType('TRANSMISSION'); }
    public function uncombine() { return $this->setType('UNCOMBINE'); }
    public function uncombined() { return $this->setType('UNCOMBINED'); }
    public function undelete() { return $this->setType('UNDELETE'); }
    public function unlock() { return $this->setType('UNLOCK'); }
    public function unlocked() { return $this->setType('UNLOCKED'); }
    public function upload() { return $this->setType('UPLOAD'); }
    public function user() { return $this->setType('USER'); }
    public function users() { return $this->setType('USERS'); }
    public function utils() { return $this->setType('UTILS'); }
    public function validate() { return $this->setType('VALIDATE'); }
    public function variables() { return $this->setType('VARIABLES'); }
    public function variations() { return $this->setType('VARIATIONS'); }
    public function view() { return $this->setType('VIEW'); }
    public function waiting() { return $this->setType('WAITING'); }
    public function warning() { return $this->setType('WARNING'); }
    public function whitelist() { return $this->setType('WHITELIST'); }
    public function wordwrap() { return $this->setType('WORDWRAP'); }
    public function workflow() { return $this->setType('WORKFLOW'); }
    public function xml() { return $this->setType('XML'); }
    public function yes() { return $this->setType('YES'); }
    /* END METHODS */
    
    public function spinner()
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
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    public function getType()
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
    public function addClass($className)
    {
        if (!in_array($className, $this->classes)) {
            $this->classes[] = $className;
        }

        return $this;
    }

    public function makeSpinner()
    {
        return $this->addClass('fa-spin');
    }

    public function makeDangerous()
    {
        return $this->addClass('text-error');
    }
    
    public function makeWarning()
    {
        return $this->addClass('text-warning');
    }
    
    public function makeMuted()
    {
        return $this->addClass('muted');
    }

    public function makeSuccess()
    {
        return $this->addClass('text-success');
    }

    public function makeInformation()
    {
        return $this->addClass('text-info');
    }

    public function makeWhite()
    {
        return $this->addClass('icon-white');
    }

   /**
    * Gives the icon a clickable style: the cursor
    * will be the click-enabled cursor. Optionally
    * a click handling statement can be specified.
    */
    public function makeClickable($statement=null)
    {
        if(!empty($statement)) {
            $this->setAttribute('onclick', $statement);    
        }
        
        return $this->addClass('clickable');
    }

    public function setTooltip($text)
    {
        $this->tooltip['text'] = $text;
        
        return $this;
    }

    public function setID(string $id) : UI_Icon
    {
        $this->id = $id;

        return $this;
    }

    public function setAttribute(string $name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }
    
    private function resolveClasses() : array
    {
        $classes = array();
        
        $type = $this->parseType();
        
        $classes[] = $type['prefix'];
        $classes[] = 'fa-'.$type['type'];
        
        return array_merge($classes, $this->classes);
    }
    
    private function parseType() : array
    {
        $type = $this->types[$this->type];
        
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
        return strval($this->tooltip['text']);
    }

    protected function tooltipify()
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
    public function cursorHelp()
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
    public function setStyle($name, $value)
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
    
   /**
    * Sets the position for the tooltip, if one is used.
    * 
    * @param string $position "top" (default), "left", "right", "bottom"
    * @return UI_Icon
    */
    public function setTooltipPosition($position='top')
    {
        if(!in_array($position, array('top', 'left', 'right', 'bottom'))) {
            $position = 'top';
        }
        
        $this->tooltip['placement'] = $position;
        return $this;
    }
    
    public function makeTooltipTop()
    {
        return $this->setTooltipPosition('top');
    }

    public function makeTooltipLeft()
    {
        return $this->setTooltipPosition('left');
    }

    public function makeTooltipRight()
    {
        return $this->setTooltipPosition('right');
    }

    public function makeTooltipBottom()
    {
        return $this->setTooltipPosition('bottom');
    }
    
    public function getIconTypes()
    {
        return $this->types; 
    }
    
    public function display()
    {
        echo $this->render();
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