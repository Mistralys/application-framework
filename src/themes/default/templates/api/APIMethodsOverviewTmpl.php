<?php
/**
 * @package API
 * @subpackage UI
 */

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Groups\APIGroupInterface;
use AppUtils\OutputBuffering;
use UI;
use UI_DataGrid;
use UI_DataGrid_Entry;
use UI_Page_Template_Custom;

/**
 * Renders the API Methods overview page.
 *
 * @package API
 * @subpackage UI
 */
class APIMethodsOverviewTmpl extends UI_Page_Template_Custom
{
    public const string PARAM_METHODS = 'methods';
    public const string ROW_FILTER_ATTRIBUTE = 'data-filter-text';
    public const string ROW_CLASS_NAME = 'api-method-entry';
    public const string VIEW_FLAT = 'flat';
    public const string VIEW_GROUPED = 'grouped';
    public const string REQUEST_PARAM_TAB = 'tab';

    private string $activeTab;

    protected function preRender(): void
    {
        $activeTab = $this->request->registerParam(self::REQUEST_PARAM_TAB)->setEnum(array(self::VIEW_FLAT, self::VIEW_GROUPED))->getString();

        if(empty($activeTab)) {
            $activeTab = self::VIEW_FLAT;
            $this->request->setParam(self::REQUEST_PARAM_TAB, $activeTab);
        }

        $this->activeTab = $activeTab;
    }

    protected function generateOutput(): void
    {
        $this->page->setTitle(t('%1$s API documentation', $this->driver->getAppNameShort()));

        OutputBuffering::start();

        $this->getPage()->createTemplate(APIMethodsMetaNav::class)->display();

        ?>
        <h1><?php echo t('%1$s API documentation', $this->driver->getAppNameShort()) ?></h1>
        <div class="method-abstract">
            <p>
            <?php
                pts('Below is a list of all API methods available in the system.');
                pts('Click on a method name to view detailed documentation.');
            ?>
            </p>
        </div>
        <?php

        $this->displayTabNavigation();

        if($this->activeTab === self::VIEW_GROUPED) {
            $this->displayGrouped();
        } else {
            $this->displayFlat();
        }

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function displayTabNavigation() : void
    {
        $manager = APIManager::getInstance();
        $tabs = $this->getUI()->createTabs();

        $tabs->appendTab(t('Flat view'), self::VIEW_FLAT)
            ->setIcon(UI::icon()->flat())
            ->makeLinked($manager->adminURL()->documentationOverview()->string(self::REQUEST_PARAM_TAB, self::VIEW_FLAT));

        $tabs->appendTab(t('Grouped view'), self::VIEW_GROUPED)
            ->setIcon(UI::icon()->grouped())
            ->makeLinked($manager->adminURL()->documentationOverview()->string(self::REQUEST_PARAM_TAB, self::VIEW_GROUPED));

        $tabs->selectByRequestVar(self::REQUEST_PARAM_TAB);

        echo $tabs;
    }

    private function displayFlat() : void
    {
        $ui = $this->getUI();
        $objName = 'MO'.nextJSID();
        $elID = nextJSID();

        $ui->addJavascript('api/methods-overview.js');
        $ui->addJavascriptHead(sprintf(
            "const %s = new MethodsOverview('%s', '%s', '%s');",
            $objName,
            $elID,
            self::ROW_CLASS_NAME,
            self::ROW_FILTER_ATTRIBUTE
        ));
        $ui->addJavascriptOnload(sprintf('%s.Start()', $objName));

        ?>
            <form class="form-inline">
                <label for="<?php echo $elID ?>" hidden="hidden">Filter</label>
                <input id="<?php echo $elID; ?>" type="search"/>
                <?php
                echo UI::button('')
                    ->setIcon(UI::icon()->delete())
                    ->setTooltip('Clear the filter')
                    ->click(sprintf('%s.ClearFilter();', $objName));
                ?>
            </form>
        <?php

        $grid = $this->createDataGrid();
        echo $grid->render($this->compileEntries($grid));
    }

    private function displayGrouped() : void
    {
        foreach($this->compileGroupedEntries() as $def) {
            $group = $def['group'];

            $this->getUI()->createSection()
                ->setTitle($group->getLabel())
                ->setAbstract($group->getDescription())
                ->collapse()
                ->setContent($def['grid']->render($def['entries']))
                ->display();
        }
    }

    private function createDataGrid(?string $idSuffix=null) : UI_DataGrid
    {
        $grid = $this->getUI()->createDataGrid('api-methods-list'.$idSuffix);

        $grid->disableFooter();

        $grid->addColumn('name', t('Method name'));
        $grid->addColumn('accepts', t('Accepts'));
        $grid->addColumn('returns', t('Returns'));
        $grid->addColumn('version', t('Version'))->alignRight();

        return $grid;
    }

    private function compileEntries(UI_DataGrid $grid) : array
    {
        $entries = array();

        foreach($this->resolveMethods() as $method) {
            $entries[] = $this->compileEntry($grid, $method);
        }

        return $entries;
    }

    private function compileEntry(UI_DataGrid $grid, APIMethodInterface $method) : UI_DataGrid_Entry
    {
        return $grid->createEntry(array(
            'name' => sb()->link($method->getMethodName(), $method->getDocumentationURL()),
            'version' => $method->getCurrentVersion(),
            'accepts' => $method->getRequestMime(),
            'returns' => $method->getResponseMime()
        ))
            ->addClass(self::ROW_CLASS_NAME)
            ->attr(self::ROW_FILTER_ATTRIBUTE, $method->getFilterText());
    }

    /**
     * @return APIMethodInterface[]
     */
    private function resolveMethods() : array
    {
        $result = array();

        foreach($this->getArrayVar(self::PARAM_METHODS) as $method) {
            if($method instanceof APIMethodInterface) {
                $result[] = $method;
            }
        }

        return $result;
    }

    /**
     * @return array<string, array{group: APIGroupInterface, grid: UI_DataGrid, entries: array<int, array<string, mixed>>}>
     */
    private function compileGroupedEntries() : array
    {
        $grouped = array();
        foreach($this->resolveMethods() as $method) {
            $group = $method->getGroup();
            $groupLabel = $group->getLabel();

            if(!isset($grouped[$groupLabel])) {
                $grouped[$groupLabel] = array(
                    'group' => $group,
                    'grid' => $this->createDataGrid('-'.$group->getID()),
                    'entries' => array()
                );
            }

            $grouped[$groupLabel]['entries'][] = $this->compileEntry($grouped[$groupLabel]['grid'], $method);
        }

        uksort($grouped, 'strnatcasecmp');

        return $grouped;
    }
}
