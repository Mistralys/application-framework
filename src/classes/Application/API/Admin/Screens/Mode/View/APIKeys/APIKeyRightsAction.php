<?php
/**
 * @package API
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace Application\API\Admin\Screens\Mode\View\APIKeys;

use Application\Application;
use Application\API\Admin\APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Admin\Traits\APIKeyActionRecordInterface;
use Application\API\Admin\Traits\APIKeyActionRecordTrait;
use Application\API\Admin\Traits\APIKeyActionTrait;
use Application\API\APIManager;
use Application\API\Clients\Keys\APIKeyRecord;
use Application_User_Rights_Right;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Action\BaseRecordAction;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_DataGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * Rights Overview screen for an API key: reverse mapping from
 * rights to granted methods. Read-only — nothing is persisted.
 * Reports only declared rights (Tier 2 rights exercised inside
 * handlers are not represented).
 *
 * @package API
 * @subpackage Admin Screens
 */
class APIKeyRightsAction extends BaseRecordAction implements APIKeyActionRecordInterface
{
    use APIClientRequestTrait;
    use APIKeyActionTrait;
    use APIKeyActionRecordTrait;

    public const string URL_NAME = 'rights';

    private const string GRID_NAME = 'api-key-rights';
    private const string COL_RIGHT = 'right';
    private const string COL_ORIGIN = 'origin';
    private const string COL_GRANTING_RIGHT = 'granting_right';
    private const string COL_METHODS = 'methods';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Rights');
    }

    public function getTitle(): string
    {
        return t('API Key Rights');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_API_KEYS_RIGHTS;
    }

    protected function _handleActions(): bool
    {
        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getTitle())
            ->makeLinked($this->getCurrentScreenURL());
    }

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        $apiKey = ClassHelper::requireObjectInstanceOf(
            APIKeyRecord::class,
            $this->getRecord()
        );

        $this->renderer->setAbstract(sb()
            ->t('This screen is informational and read-only — nothing is persisted.')
            ->t('It reports only rights that are explicitly declared by API methods via %1$s.', sb()->code('getRequiredRight()'))
            ->t('Tier 2 rights exercised inside method handlers are not represented.')
        );

        $grid = $this->createDataGrid();

        return $this->renderer
            ->appendDataGrid($grid, $this->collectEntries($apiKey, $grid));
    }

    private function createDataGrid(): UI_DataGrid
    {
        $grid = $this->ui->createDataGrid(self::GRID_NAME);
        $grid->addHiddenScreenVars();

        $grid->addColumn(self::COL_RIGHT, t('Right'))
            ->setSortingString();

        $grid->addColumn(self::COL_ORIGIN, t('Origin'));

        $grid->addColumn(self::COL_GRANTING_RIGHT, t('Granting right'));

        $grid->addColumn(self::COL_METHODS, t('Granted methods'));

        $grid->disableFooter();

        return $grid;
    }

    /**
     * @return array<int, \UI_DataGrid_Entry>
     */
    private function collectEntries(APIKeyRecord $apiKey, UI_DataGrid $grid): array
    {
        $index = APIManager::getInstance()->getMethodIndex();
        $rightsManager = Application::getUser()->getRightsManager();

        // Collect granted method names.
        if($apiKey->areAllMethodsGranted()) {
            $grantedMethods = $index->getMethodNames();
        } else {
            $grantedMethods = $apiKey->getMethods()->getMethodNames();
        }

        // Build the reverse map: right ID → list of methods that declare it.
        $declaredRights = array();
        $unregistered = array();

        foreach($grantedMethods as $methodName) {
            $entry = $index->getEntry($methodName);
            $rightID = $entry->getRequiredRight();

            if($rightID === null) {
                continue;
            }

            if(!$rightsManager->rightIDExists($rightID)) {
                $unregistered[$rightID] = true;
                continue;
            }

            if(!isset($declaredRights[$rightID])) {
                $declaredRights[$rightID] = array();
            }

            $declaredRights[$rightID][] = $methodName;
        }

        ksort($declaredRights);

        $entries = array();

        foreach($declaredRights as $rightID => $methods) {
            $right = $rightsManager->getRightByID($rightID);

            // Declared row
            $entries[] = $this->createRightEntry(
                $grid,
                $right,
                t('Declared'),
                '',
                $methods
            );

            // One-level grant expansion
            $grants = $right->getGrants()->getAll();
            foreach($grants as $grantedRight) {
                $entries[] = $this->createRightEntry(
                    $grid,
                    $grantedRight,
                    sb()->add(t('Granted via'))->add(' ')->muted($rightID),
                    $rightID,
                    $methods
                );
            }
        }

        // Warning rows for unregistered rights
        foreach(array_keys($unregistered) as $rightID) {
            $entries[] = $grid->createEntry(array(
                self::COL_RIGHT => sb()->icon(UI::icon()->warning())->add($rightID),
                self::COL_ORIGIN => t('Not registered'),
                self::COL_GRANTING_RIGHT => '-',
                self::COL_METHODS => '-',
            ));
        }

        return $entries;
    }

    /**
     * @param string[] $methods
     */
    private function createRightEntry(
        UI_DataGrid $grid,
        Application_User_Rights_Right $right,
        string|\AppUtils\StringBuilder_Interface $origin,
        string $grantingRight,
        array $methods
    ): \UI_DataGrid_Entry
    {
        $rightLabel = sb()
            ->add($right->getActionIcon())
            ->add($right->getID());

        return $grid->createEntry(array(
            self::COL_RIGHT => $rightLabel,
            self::COL_ORIGIN => $origin,
            self::COL_GRANTING_RIGHT => $grantingRight !== '' ? $grantingRight : '-',
            self::COL_METHODS => implode(', ', $methods),
        ));
    }

    protected function getCurrentScreenURL(): AdminURLInterface
    {
        return $this->getRecord()->adminURL()->rights();
    }
}
