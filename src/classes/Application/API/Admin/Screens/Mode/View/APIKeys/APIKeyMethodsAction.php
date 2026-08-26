<?php
/**
 * @package API
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace Application\API\Admin\Screens\Mode\View\APIKeys;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Admin\Traits\APIKeyActionRecordInterface;
use Application\API\Admin\Traits\APIKeyActionRecordTrait;
use Application\API\Admin\Traits\APIKeyActionTrait;
use Application\API\APIManager;
use Application\API\Clients\Keys\APIKeyRecord;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Action\BaseRecordAction;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_DataGrid;
use UI_DataGrid_Action;
use UI_Themes_Theme_ContentRenderer;

/**
 * Methods Selection screen for an API key: lists every available
 * API method with its declared right, group, and granted state.
 * Diff-based save: only newly selected and deselected methods
 * within the submitted (shown) view are mutated.
 *
 * @package API
 * @subpackage Admin Screens
 */
class APIKeyMethodsAction extends BaseRecordAction implements APIKeyActionRecordInterface
{
    use APIClientRequestTrait;
    use APIKeyActionTrait;
    use APIKeyActionRecordTrait;

    public const string URL_NAME = 'methods';

    private const string GRID_NAME = 'api-key-methods';
    private const string COL_METHOD_KEY = 'method_key';
    private const string COL_METHOD = 'method';
    private const string COL_RIGHT = 'right';
    private const string COL_GROUP = 'group';
    private const string COL_GRANTED = 'granted';
    private const string ACTION_SAVE = 'save-methods';
    private const string HIDDEN_SHOWN_METHODS = 'shown_methods';
    private const string SHOWN_SEPARATOR = ',';

    private UI_DataGrid $grid;
    private APIKeyRecord $apiKey;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Methods');
    }

    public function getTitle(): string
    {
        return t('API Key Methods');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_API_KEYS_METHODS;
    }

    public function getFeatureRights(): array
    {
        return array(
            t('Edit API Key Methods') => APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT
        );
    }

    protected function _handleActions(): bool
    {
        $this->apiKey = ClassHelper::requireObjectInstanceOf(
            APIKeyRecord::class,
            $this->getRecord()
        );

        $this->grid = $this->createDataGrid();

        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getTitle())
            ->makeLinked($this->getCurrentScreenURL());
    }

    protected function _handleSidebar(): void
    {
        if($this->apiKey->areAllMethodsGranted()) {
            return;
        }

        if(!$this->user->can(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)) {
            return;
        }

        $this->sidebar->addButton('save-methods', t('Save now'))
            ->makePrimary()
            ->setIcon(UI::icon()->save())
            ->makeClickable($this->grid->clientCommands()->submitAction(self::ACTION_SAVE));
    }

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        if($this->apiKey->areAllMethodsGranted()) {
            $this->renderer->appendContent(
                $this->renderInfoMessage(
                    t('This API key has all methods granted. Individual method selection is disabled.')
                )
            );
        }

        return $this->renderer
            ->appendDataGrid($this->grid, $this->collectEntries());
    }

    private function createDataGrid(): UI_DataGrid
    {
        $grid = $this->ui->createDataGrid(self::GRID_NAME);
        $grid->addHiddenScreenVars();

        // Screen params alone do not identify the record chain; the grid's own POST needs them too.
        $grid->addHiddenVar(
            $this->apiKey->getClient()->getCollection()->getRecordRequestPrimaryName(),
            $this->apiKey->getClient()->getID()
        );
        $grid->addHiddenVar(
            $this->apiKey->getCollection()->getRecordRequestPrimaryName(),
            $this->apiKey->getID()
        );

        $grid->addColumn(self::COL_METHOD, t('Method'))
            ->setSortingString();

        $grid->addColumn(self::COL_RIGHT, t('Required right'));

        $grid->addColumn(self::COL_GROUP, t('API group'))
            ->setSortingString();

        $grid->addColumn(self::COL_GRANTED, t('Granted'));

        if($this->apiKey->areAllMethodsGranted()) {
            $grid->disableMultiSelect();
        } else {
            $grid->enableMultiSelect(self::COL_METHOD_KEY);
            $grid->enableLimitOptionsDefault();

            if($this->user->can(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)) {
                $grid->addConfirmAction(
                    self::ACTION_SAVE,
                    t('Save method selection'),
                    sb()->t('The method grants will be updated to match your selection.')
                )
                    ->setIcon(UI::icon()->save())
                    ->setCallback($this->handle_saveMethodSelection(...));
            }
        }

        return $grid;
    }

    /**
     * @return array<int, \UI_DataGrid_Entry>
     */
    private function collectEntries(): array
    {
        $entries = array();
        $index = APIManager::getInstance()->getMethodIndex();
        $shownMethods = array();

        foreach($index->getMethodNames() as $methodName) {
            $entry_data = $index->getEntry($methodName);
            $isGranted = $this->apiKey->getMethods()->hasMethod($methodName);

            $entry = $this->grid->createEntry(array(
                self::COL_METHOD_KEY => $methodName,
                self::COL_METHOD => $methodName,
                self::COL_RIGHT => $entry_data->getRequiredRight() ?? '-',
                self::COL_GROUP => $entry_data->getGroupID(),
                self::COL_GRANTED => $isGranted ? t('Yes') : t('No'),
            ));

            if(!$this->apiKey->areAllMethodsGranted()) {
                $entry->setColumnValue(
                    self::COL_METHOD,
                    $entry->renderCheckboxLabel($methodName)
                );

                if($isGranted) {
                    $entry->select();
                }
            }

            $shownMethods[] = $methodName;
            $entries[] = $entry;
        }

        if(!$this->apiKey->areAllMethodsGranted()) {
            $this->grid->addHiddenVar(
                self::HIDDEN_SHOWN_METHODS,
                implode(self::SHOWN_SEPARATOR, $shownMethods)
            );
        }

        return $entries;
    }

    private function handle_saveMethodSelection(UI_DataGrid_Action $action): void
    {
        if(!$this->user->can(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)) {
            $this->redirectWithErrorMessage(
                t('You do not have the required rights to save method grants.'),
                $this->getCurrentScreenURL()
            );
        }

        if($this->apiKey->areAllMethodsGranted()) {
            $this->redirectWithInfoMessage(
                t('This API key has all methods granted. Individual method selection is not available.'),
                $this->getCurrentScreenURL()
            );
        }

        $selectedMethods = array_map('strval', $action->getSelectedValues());
        $shownMethods = $this->parseShownMethods();
        $currentlyGranted = $this->apiKey->getMethods()->getMethodNames();

        // Diff: only mutate methods that were in the submitted view.
        $toAdd = array_diff(
            array_intersect($selectedMethods, $shownMethods),
            $currentlyGranted
        );

        $toRemove = array_intersect(
            array_diff($shownMethods, $selectedMethods),
            $currentlyGranted
        );

        $methods = $this->apiKey->getMethods();

        if(!empty($toAdd)) {
            $methods->addMethods($toAdd);
        }

        if(!empty($toRemove)) {
            $methods->removeMethods($toRemove);
        }

        $total = count($toAdd) + count($toRemove);

        if($total === 0) {
            $this->redirectWithInfoMessage(
                t('No changes to method grants.'),
                $this->getCurrentScreenURL()
            );
        }

        $this->redirectWithSuccessMessage(
            t(
                'Method grants updated: %1$s added, %2$s removed at %3$s.',
                (string)count($toAdd),
                (string)count($toRemove),
                sb()->time()
            ),
            $this->getCurrentScreenURL()
        );
    }

    /**
     * @return string[]
     */
    private function parseShownMethods(): array
    {
        $raw = $this->request->getParam(self::HIDDEN_SHOWN_METHODS);

        if(!is_string($raw) || $raw === '') {
            return array();
        }

        $available = $this->apiKey->getMethods()->getAvailableMethods();
        $parsed = explode(self::SHOWN_SEPARATOR, $raw);

        return array_values(array_intersect($parsed, $available));
    }

    protected function getCurrentScreenURL(): AdminURLInterface
    {
        return $this->getRecord()->adminURL()->methods();
    }
}
