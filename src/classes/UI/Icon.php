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
    protected string $name = '';

    /**
     * @var string[]
     */
    protected array $classes = array();

    /**
     * @var array{text:string,placement:string}
     */
    protected array $tooltip = array(
        'text' => '',
        'placement' => 'top'
    );

    /**
     * @var string
     */
    protected string $id;

    /**
     * @var array<string,string|number>
     */
    protected array $attributes = array();

    /**
     * @var string
     */
    protected string $prefix = 'fa';

    public function __construct()
    {
        $this->id = 'ic'.nextJSID();
    }

    /* START METHODS */

    // region: Icon type methods
    
    /**
     * @return $this
     */
    public function actioncode() : self { return $this->setType('rocket'); }
    /**
     * @return $this
     */
    public function activate() : self { return $this->setType('sun', 'far'); }
    /**
     * @return $this
     */
    public function activity() : self { return $this->setType('bullhorn'); }
    /**
     * @return $this
     */
    public function add() : self { return $this->setType('plus-circle'); }
    /**
     * @return $this
     */
    public function attentionRequired() : self { return $this->setType('exclamation-triangle'); }
    /**
     * @return $this
     */
    public function audience() : self { return $this->setType('podcast'); }
    /**
     * @return $this
     */
    public function back() : self { return $this->setType('arrow-circle-left'); }
    /**
     * @return $this
     */
    public function backToCurrent() : self { return $this->setType('level-down-alt', 'fas'); }
    /**
     * @return $this
     */
    public function backup() : self { return $this->setType('recycle'); }
    /**
     * @return $this
     */
    public function box() : self { return $this->setType('archive'); }
    /**
     * @return $this
     */
    public function browse() : self { return $this->setType('folder-open'); }
    /**
     * @return $this
     */
    public function bugreport() : self { return $this->setType('bug'); }
    /**
     * @return $this
     */
    public function build() : self { return $this->setType('magic'); }
    /**
     * @return $this
     */
    public function business() : self { return $this->setType('university'); }
    /**
     * @return $this
     */
    public function button() : self { return $this->setType('external-link-square-alt'); }
    /**
     * @return $this
     */
    public function calendar() : self { return $this->setType('calendar'); }
    /**
     * @return $this
     */
    public function campaigns() : self { return $this->setType('flag'); }
    /**
     * @return $this
     */
    public function cancel() : self { return $this->setType('ban'); }
    /**
     * @return $this
     */
    public function caretDown() : self { return $this->setType('caret-down'); }
    /**
     * @return $this
     */
    public function caretUp() : self { return $this->setType('caret-up'); }
    /**
     * @return $this
     */
    public function category() : self { return $this->setType('bars'); }
    /**
     * @return $this
     */
    public function changeOrder() : self { return $this->setType('bars'); }
    /**
     * @return $this
     */
    public function changelog() : self { return $this->setType('edit'); }
    /**
     * @return $this
     */
    public function check() : self { return $this->setType('check-double'); }
    /**
     * @return $this
     */
    public function code() : self { return $this->setType('code'); }
    /**
     * @return $this
     */
    public function collapse() : self { return $this->setType('minus-circle'); }
    /**
     * @return $this
     */
    public function collapseLeft() : self { return $this->setType('caret-square-left'); }
    /**
     * @return $this
     */
    public function collapseRight() : self { return $this->setType('caret-square-right'); }
    /**
     * @return $this
     */
    public function colors() : self { return $this->setType('palette'); }
    /**
     * @return $this
     */
    public function combination() : self { return $this->setType('object-group'); }
    /**
     * @return $this
     */
    public function combine() : self { return $this->setType('link'); }
    /**
     * @return $this
     */
    public function commandDeck() : self { return $this->setType('dice-d20', 'fas'); }
    /**
     * @return $this
     */
    public function commands() : self { return $this->setType('terminal'); }
    /**
     * @return $this
     */
    public function comment() : self { return $this->setType('comment'); }
    /**
     * @return $this
     */
    public function comtypes() : self { return $this->setType('broadcast-tower'); }
    /**
     * @return $this
     */
    public function contentTypes() : self { return $this->setType('elementor', 'fab'); }
    /**
     * @return $this
     */
    public function convert() : self { return $this->setType('cogs'); }
    /**
     * @return $this
     */
    public function copy() : self { return $this->setType('copy'); }
    /**
     * @return $this
     */
    public function countdown() : self { return $this->setType('clock', 'far'); }
    /**
     * @return $this
     */
    public function countries() : self { return $this->setType('flag', 'far'); }
    /**
     * @return $this
     */
    public function csv() : self { return $this->setType('file-alt', 'far'); }
    /**
     * @return $this
     */
    public function customVariables() : self { return $this->setType('project-diagram'); }
    /**
     * @return $this
     */
    public function deactivate() : self { return $this->setType('moon', 'far'); }
    /**
     * @return $this
     */
    public function deactivated() : self { return $this->setType('moon', 'far'); }
    /**
     * @return $this
     */
    public function delete() : self { return $this->setType('times'); }
    /**
     * @return $this
     */
    public function deleteSign() : self { return $this->setType('times-circle', 'far'); }
    /**
     * @return $this
     */
    public function deleted() : self { return $this->setType('times'); }
    /**
     * @return $this
     */
    public function deselectAll() : self { return $this->setType('minus-square', 'far'); }
    /**
     * @return $this
     */
    public function destroy() : self { return $this->setType('exclamation-triangle'); }
    /**
     * @return $this
     */
    public function developer() : self { return $this->setType('asterisk'); }
    /**
     * @return $this
     */
    public function disabled() : self { return $this->setType('ban'); }
    /**
     * @return $this
     */
    public function discard() : self { return $this->setType('trash-alt', 'far'); }
    /**
     * @return $this
     */
    public function disconnect() : self { return $this->setType('unlink'); }
    /**
     * @return $this
     */
    public function download() : self { return $this->setType('download'); }
    /**
     * @return $this
     */
    public function draft() : self { return $this->setType('puzzle-piece'); }
    /**
     * @return $this
     */
    public function drag() : self { return $this->setType('bars'); }
    /**
     * @return $this
     */
    public function dropdown() : self { return $this->setType('caret-down'); }
    /**
     * @return $this
     */
    public function edit() : self { return $this->setType('pencil-alt', 'fas'); }
    /**
     * @return $this
     */
    public function editor() : self { return $this->setType('cubes'); }
    /**
     * @return $this
     */
    public function email() : self { return $this->setType('at'); }
    /**
     * @return $this
     */
    public function enabled() : self { return $this->setType('check-circle', 'fas'); }
    /**
     * @return $this
     */
    public function expand() : self { return $this->setType('plus-circle'); }
    /**
     * @return $this
     */
    public function expandLeft() : self { return $this->setType('caret-square-left'); }
    /**
     * @return $this
     */
    public function expandRight() : self { return $this->setType('caret-square-right'); }
    /**
     * @return $this
     */
    public function export() : self { return $this->setType('bolt', 'fas'); }
    /**
     * @return $this
     */
    public function exportArchive() : self { return $this->setType('archive'); }
    /**
     * @return $this
     */
    public function featuretables() : self { return $this->setType('server'); }
    /**
     * @return $this
     */
    public function feedback() : self { return $this->setType('thumbs-up', 'far'); }
    /**
     * @return $this
     */
    public function file() : self { return $this->setType('file-alt'); }
    /**
     * @return $this
     */
    public function filter() : self { return $this->setType('filter'); }
    /**
     * @return $this
     */
    public function first() : self { return $this->setType('step-backward'); }
    /**
     * @return $this
     */
    public function flat() : self { return $this->setType('th'); }
    /**
     * @return $this
     */
    public function forward() : self { return $this->setType('arrow-circle-right'); }
    /**
     * @return $this
     */
    public function generate() : self { return $this->setType('bolt', 'fas'); }
    /**
     * @return $this
     */
    public function global() : self { return $this->setType('globe-europe'); }
    /**
     * @return $this
     */
    public function globalContent() : self { return $this->setType('cube'); }
    /**
     * @return $this
     */
    public function grouped() : self { return $this->setType('layer-group'); }
    /**
     * @return $this
     */
    public function help() : self { return $this->setType('question-circle'); }
    /**
     * @return $this
     */
    public function hide() : self { return $this->setType('eye-slash'); }
    /**
     * @return $this
     */
    public function home() : self { return $this->setType('home'); }
    /**
     * @return $this
     */
    public function html() : self { return $this->setType('code'); }
    /**
     * @return $this
     */
    public function id() : self { return $this->setType('key'); }
    /**
     * @return $this
     */
    public function image() : self { return $this->setType('image'); }
    /**
     * @return $this
     */
    public function import() : self { return $this->setType('briefcase'); }
    /**
     * @return $this
     */
    public function inactive() : self { return $this->setType('moon', 'far'); }
    /**
     * @return $this
     */
    public function information() : self { return $this->setType('info-circle'); }
    /**
     * @return $this
     */
    public function itemActive() : self { return $this->setType('circle'); }
    /**
     * @return $this
     */
    public function itemInactive() : self { return $this->setType('circle', 'far'); }
    /**
     * @return $this
     */
    public function jumpTo() : self { return $this->setType('arrow-alt-circle-right', 'far'); }
    /**
     * @return $this
     */
    public function jumpUp() : self { return $this->setType('arrow-up'); }
    /**
     * @return $this
     */
    public function keyword() : self { return $this->setType('bookmark'); }
    /**
     * @return $this
     */
    public function last() : self { return $this->setType('step-forward'); }
    /**
     * @return $this
     */
    public function link() : self { return $this->setType('link'); }
    /**
     * @return $this
     */
    public function list() : self { return $this->setType('server'); }
    /**
     * @return $this
     */
    public function load() : self { return $this->setType('folder-open', 'far'); }
    /**
     * @return $this
     */
    public function locked() : self { return $this->setType('lock'); }
    /**
     * @return $this
     */
    public function logIn() : self { return $this->setType('sign-in-alt', 'fas'); }
    /**
     * @return $this
     */
    public function logOut() : self { return $this->setType('power-off'); }
    /**
     * @return $this
     */
    public function lookup() : self { return $this->setType('ellipsis-h'); }
    /**
     * @return $this
     */
    public function mailHeaderTitle() : self { return $this->setType('heading', 'fas'); }
    /**
     * @return $this
     */
    public function mailHeaders() : self { return $this->setType('crosshairs'); }
    /**
     * @return $this
     */
    public function mailTests() : self { return $this->setType('envelope-open-text'); }
    /**
     * @return $this
     */
    public function mails() : self { return $this->setType('envelope'); }
    /**
     * @return $this
     */
    public function maximize() : self { return $this->setType('expand'); }
    /**
     * @return $this
     */
    public function media() : self { return $this->setType('image'); }
    /**
     * @return $this
     */
    public function menu() : self { return $this->setType('bars'); }
    /**
     * @return $this
     */
    public function merge() : self { return $this->setType('level-down-alt', 'fas'); }
    /**
     * @return $this
     */
    public function message() : self { return $this->setType('comment-alt', 'far'); }
    /**
     * @return $this
     */
    public function minus() : self { return $this->setType('minus'); }
    /**
     * @return $this
     */
    public function money() : self { return $this->setType('money-check-alt'); }
    /**
     * @return $this
     */
    public function move() : self { return $this->setType('arrows-alt'); }
    /**
     * @return $this
     */
    public function moveLeftRight() : self { return $this->setType('arrows-alt-h', 'fas'); }
    /**
     * @return $this
     */
    public function moveTo() : self { return $this->setType('sign-out-alt', 'fas'); }
    /**
     * @return $this
     */
    public function moveUpDown() : self { return $this->setType('arrows-alt-v', 'fas'); }
    /**
     * @return $this
     */
    public function next() : self { return $this->setType('chevron-right'); }
    /**
     * @return $this
     */
    public function no() : self { return $this->setType('times'); }
    /**
     * @return $this
     */
    public function notAvailable() : self { return $this->setType('ban'); }
    /**
     * @return $this
     */
    public function notRequired() : self { return $this->setType('minus'); }
    /**
     * @return $this
     */
    public function notepad() : self { return $this->setType('sticky-note', 'far'); }
    /**
     * @return $this
     */
    public function off() : self { return $this->setType('power-off'); }
    /**
     * @return $this
     */
    public function ok() : self { return $this->setType('check'); }
    /**
     * @return $this
     */
    public function oms() : self { return $this->setType('telegram', 'fab'); }
    /**
     * @return $this
     */
    public function on() : self { return $this->setType('dot-circle', 'far'); }
    /**
     * @return $this
     */
    public function options() : self { return $this->setType('dot-circle', 'far'); }
    /**
     * @return $this
     */
    public function page() : self { return $this->setType('file'); }
    /**
     * @return $this
     */
    public function pagemodel() : self { return $this->setType('newspaper', 'far'); }
    /**
     * @return $this
     */
    public function pause() : self { return $this->setType('pause'); }
    /**
     * @return $this
     */
    public function pin() : self { return $this->setType('thumbtack'); }
    /**
     * @return $this
     */
    public function play() : self { return $this->setType('play'); }
    /**
     * @return $this
     */
    public function plus() : self { return $this->setType('plus'); }
    /**
     * @return $this
     */
    public function positionAny() : self { return $this->setType('sort', 'fas'); }
    /**
     * @return $this
     */
    public function positionBottom() : self { return $this->setType('arrow-circle-down'); }
    /**
     * @return $this
     */
    public function positionTop() : self { return $this->setType('arrow-circle-up'); }
    /**
     * @return $this
     */
    public function presets() : self { return $this->setType('server'); }
    /**
     * @return $this
     */
    public function preview() : self { return $this->setType('file-code', 'far'); }
    /**
     * @return $this
     */
    public function previous() : self { return $this->setType('chevron-left'); }
    /**
     * @return $this
     */
    public function price() : self { return $this->setType('money-check-alt'); }
    /**
     * @return $this
     */
    public function print() : self { return $this->setType('print'); }
    /**
     * @return $this
     */
    public function printer() : self { return $this->setType('print'); }
    /**
     * @return $this
     */
    public function product() : self { return $this->setType('shopping-basket'); }
    /**
     * @return $this
     */
    public function proms() : self { return $this->setType('database'); }
    /**
     * @return $this
     */
    public function proofing() : self { return $this->setType('check-square', 'far'); }
    /**
     * @return $this
     */
    public function properties() : self { return $this->setType('cogs', 'fas'); }
    /**
     * @return $this
     */
    public function publish() : self { return $this->setType('sign-out-alt', 'fas'); }
    /**
     * @return $this
     */
    public function published() : self { return $this->setType('check'); }
    /**
     * @return $this
     */
    public function rating() : self { return $this->setType('star'); }
    /**
     * @return $this
     */
    public function recordType() : self { return $this->setType('bezier-curve'); }
    /**
     * @return $this
     */
    public function refresh() : self { return $this->setType('sync', 'fas'); }
    /**
     * @return $this
     */
    public function required() : self { return $this->setType('exclamation-circle'); }
    /**
     * @return $this
     */
    public function reset() : self { return $this->setType('minus-square'); }
    /**
     * @return $this
     */
    public function restore() : self { return $this->setType('share'); }
    /**
     * @return $this
     */
    public function revert() : self { return $this->setType('history'); }
    /**
     * @return $this
     */
    public function review() : self { return $this->setType('user-edit'); }
    /**
     * @return $this
     */
    public function save() : self { return $this->setType('save'); }
    /**
     * @return $this
     */
    public function search() : self { return $this->setType('search'); }
    /**
     * @return $this
     */
    public function selectAll() : self { return $this->setType('plus-square', 'far'); }
    /**
     * @return $this
     */
    public function selected() : self { return $this->setType('list'); }
    /**
     * @return $this
     */
    public function send() : self { return $this->setType('envelope'); }
    /**
     * @return $this
     */
    public function settings() : self { return $this->setType('wrench'); }
    /**
     * @return $this
     */
    public function shop() : self { return $this->setType('shopping-cart'); }
    /**
     * @return $this
     */
    public function snowflake() : self { return $this->setType('snowflake', 'far'); }
    /**
     * @return $this
     */
    public function sort() : self { return $this->setType('sort'); }
    /**
     * @return $this
     */
    public function sortAsc() : self { return $this->setType('angle-up'); }
    /**
     * @return $this
     */
    public function sortDesc() : self { return $this->setType('angle-down'); }
    /**
     * @return $this
     */
    public function sorting() : self { return $this->setType('sort-amount-down', 'fas'); }
    /**
     * @return $this
     */
    public function status() : self { return $this->setType('shield-alt'); }
    /**
     * @return $this
     */
    public function stop() : self { return $this->setType('pause'); }
    /**
     * @return $this
     */
    public function structural() : self { return $this->setType('cubes'); }
    /**
     * @return $this
     */
    public function suggest() : self { return $this->setType('lightbulb'); }
    /**
     * @return $this
     */
    public function switch() : self { return $this->setType('retweet'); }
    /**
     * @return $this
     */
    public function switchCampaign() : self { return $this->setType('exchange-alt', 'fas'); }
    /**
     * @return $this
     */
    public function switchMode() : self { return $this->setType('compass'); }
    /**
     * @return $this
     */
    public function table() : self { return $this->setType('table'); }
    /**
     * @return $this
     */
    public function tariffMatrix() : self { return $this->setType('table'); }
    /**
     * @return $this
     */
    public function task() : self { return $this->setType('tasks'); }
    /**
     * @return $this
     */
    public function template() : self { return $this->setType('file-alt', 'far'); }
    /**
     * @return $this
     */
    public function tenant() : self { return $this->setType('award'); }
    /**
     * @return $this
     */
    public function text() : self { return $this->setType('font'); }
    /**
     * @return $this
     */
    public function time() : self { return $this->setType('clock'); }
    /**
     * @return $this
     */
    public function toggle() : self { return $this->setType('retweet'); }
    /**
     * @return $this
     */
    public function tools() : self { return $this->setType('tools'); }
    /**
     * @return $this
     */
    public function translation() : self { return $this->setType('globe'); }
    /**
     * @return $this
     */
    public function transmission() : self { return $this->setType('satellite-dish'); }
    /**
     * @return $this
     */
    public function uncombine() : self { return $this->setType('unlink'); }
    /**
     * @return $this
     */
    public function uncombined() : self { return $this->setType('object-ungroup'); }
    /**
     * @return $this
     */
    public function undelete() : self { return $this->setType('reply'); }
    /**
     * @return $this
     */
    public function unlock() : self { return $this->setType('unlock'); }
    /**
     * @return $this
     */
    public function unlocked() : self { return $this->setType('unlock'); }
    /**
     * @return $this
     */
    public function upload() : self { return $this->setType('upload'); }
    /**
     * @return $this
     */
    public function user() : self { return $this->setType('user'); }
    /**
     * @return $this
     */
    public function users() : self { return $this->setType('users'); }
    /**
     * @return $this
     */
    public function utils() : self { return $this->setType('first-aid', 'fas'); }
    /**
     * @return $this
     */
    public function validate() : self { return $this->setType('check-circle'); }
    /**
     * @return $this
     */
    public function variables() : self { return $this->setType('code-branch'); }
    /**
     * @return $this
     */
    public function variations() : self { return $this->setType('sitemap'); }
    /**
     * @return $this
     */
    public function view() : self { return $this->setType('eye'); }
    /**
     * @return $this
     */
    public function waiting() : self { return $this->setType('clock', 'far'); }
    /**
     * @return $this
     */
    public function warning() : self { return $this->setType('exclamation-triangle', 'fas'); }
    /**
     * @return $this
     */
    public function whitelist() : self { return $this->setType('star', 'far'); }
    /**
     * @return $this
     */
    public function wordwrap() : self { return $this->setType('terminal', 'fas'); }
    /**
     * @return $this
     */
    public function workflow() : self { return $this->setType('sitemap'); }
    /**
     * @return $this
     */
    public function xml() : self { return $this->setType('code'); }
    /**
     * @return $this
     */
    public function yes() : self { return $this->setType('check'); }
    
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
     * @param string $name
     * @param string $prefix
     * @return UI_Icon
     */
    public function setType(string $name, string $prefix='') : UI_Icon
    {
        if(empty($prefix)) {
            $prefix = 'fa';
        }

        $this->name = strtolower($name);
        $this->prefix = strtolower($prefix);

        return $this;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->name;
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
     */
    private function resolveClasses() : array
    {
        $classes = array();

        if(empty($this->name)) {
            $this->name = 'exclamation-triangle';
        }

        if(!empty($this->prefix))
        {
            $classes[] = $this->prefix;
        }

        $classes[] = 'fa-'.$this->name;

        if(isset($this->colorStyle))
        {
            $classes[] = self::$colorClasses[$this->colorStyle];
        }
        
        return array_merge($classes, $this->classes);
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