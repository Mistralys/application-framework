<?php
/**
 * @package API
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\API\Admin\Screens;

use Application\API\Admin\APIScreenRights;
use Application\API\Clients\APIClientRecord;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use Application\Traits\AllowableMigrationTrait;
use DBHelper\Admin\Screens\Mode\BaseRecordListMode;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;
use UI_DataGrid_Action;
use UI_DataGrid_Entry;

/**
 * Abstract base class for the API Clients overview screen.
 *
 * @package API
 * @subpackage Admin
 */
abstract class BaseClientsListMode extends BaseRecordListMode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'list';
    public const string COL_LABEL = 'label';
    public const string COL_FOREIGN_ID = 'foreign_id';
    public const string COL_DATE_CREATED = 'date_created';
    public const string COL_IS_ACTIVE = 'is_active';
    public const string COL_ID = 'id';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Overview');
    }

    public function getNavigationTitle() : string
    {
        return t('Overview');
    }

    public function getRequiredRight() : string
    {
        return APIScreenRights::SCREEN_CLIENTS_LIST;
    }

    public function getFeatureRights(): array
    {
        return array(
            t('Multi-delete API Clients') => APIScreenRights::SCREEN_CLIENTS_LIST_MULTI_DELETE,
        );
    }

    /**
     * @return APIClientsCollection
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createAPIClients();
    }

    /**
     * @param APIClientRecord $record
     * @param DBHelper_BaseFilterCriteria_Record $entry
     * @return UI_DataGrid_Entry
     */
    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : UI_DataGrid_Entry
    {
        return $this->grid->createEntry(array(
            self::COL_ID => $record->getID(),
            self::COL_LABEL => $record->getLabelLinked(),
            self::COL_FOREIGN_ID => sb()->code($record->getForeignID()),
            self::COL_DATE_CREATED => $record->getDateCreated(),
            self::COL_IS_ACTIVE => UI::prettyBool($record->isActive())->makeActiveInactive(),
        ));
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_ID, t('ID'))
            ->setCompact()
            ->setSortable(false, APIClientsCollection::PRIMARY_NAME);

        $this->grid->addColumn(self::COL_LABEL, t('Label'))
            ->setSortable(true, APIClientsCollection::COL_LABEL);

        $this->grid->addColumn(self::COL_FOREIGN_ID, t('Foreign ID'))
            ->setSortable(true, APIClientsCollection::COL_FOREIGN_ID);

        $this->grid->addColumn(self::COL_DATE_CREATED, t('Date Created'))
            ->setSortable(true, APIClientsCollection::COL_DATE_CREATED);

        $this->grid->addColumn(self::COL_IS_ACTIVE, t('Active?'));
    }

    protected function configureActions(): void
    {
        $this->grid->addAction('delete', t('Delete').'...')
            ->makeDangerous()
            ->setIcon(UI::icon()->delete())
            ->setCallback($this->handleMultiDelete(...))
            ->makeConfirm(sb()
                ->para(sb()
                    ->warning(t('This will delete the selected API client(s) and all related API keys.'))
                )
                ->para(sb()
                    ->t('If any applications are still using these clients, they will no longer be able to access the APIs.')
                )
                ->para(sb()
                    ->cannotBeUndone()
                )
            );
    }

    private function handleMultiDelete(UI_DataGrid_Action $action) : void
    {
        $collection = $this->createCollection();

        $action->createRedirectMessage($collection->adminURL()->list())
            ->processDeleteDBRecords($collection)
            ->redirect();
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create', t('Create new client').'...')
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->createCollection()->adminURL()->create());

        $this->sidebar->addSeparator();

        parent::_handleSidebar();
    }

    public function getBackOrCancelURL(): string
    {
        return APP_URL;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setAbstract(sb()
            ->t('This is an overview of all API Clients that have been registered in the system.')
            ->t('It enables access to the APIs provided by the application through API keys specific to each client.')
        );
    }
}
