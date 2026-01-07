<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Screens\Submode;

use Application\AppSets\AppSet;
use Application\AppSets\AppSetsCollection;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordListSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record;
use UI;
use UI_DataGrid_Entry;

class SetsListSubmode extends BaseRecordListSubmode implements SubmodeInterface
{
    use SubmodeTrait;

    public const string URL_NAME = 'list';

    public const string COL_ID = 'id';
    public const string COL_ALIAS = 'alias';
    public const string COL_ACTIVE = 'active';
    public const string COL_DEFAULT_AREA = 'default';
    public const string COL_ENABLED = 'enabled';
    public const string COL_LABEL = 'label';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_LIST;
    }

    public function getTitle(): string
    {
        return t('List of application sets');
    }

    public function getNavigationTitle(): string
    {
        return t('List');
    }

    protected function _handleSidebar(): void
    {
        $btnCreate = $this->sidebar->addButton('create-set', t('Create a set'))
            ->link($this->createCollection()->getAdminCreateURL())
            ->setIcon(UI::icon()->add());

        if($this->createCollection()->countRecords() === 0) {
            $btnCreate->makePrimary();
        }

        $this->sidebar->addSeparator();

        parent::_handleSidebar();

        $this->sidebar->addSeparator();

        $this->sidebar->addHelp(
            t('Using application sets'),
            '<p>' . t('An application set can be selected by adding the %1$s application configuration setting, and specifying the ID of the application set as its value.', '<code>APP_APPSET</code>') . '</p>' .
            '<p>' . t('When no application set is specified, it is assumed all areas are enabled, and the default area is the first in the list.') . '</p>'
        );
    }


    private function renderEnabled(AppSet $set) : string
    {
        if($set->areAllAreasEnabled()) {
            return t('All areas enabled');
        }

        return implode(', ', $set->getEnabledAreaLabels());
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_ID, t('ID'))->setNowrap()->setCompact();
        $this->grid->addColumn(self::COL_LABEL, t('Label'));
        $this->grid->addColumn(self::COL_ALIAS, t('Alias'))->setNowrap();
        $this->grid->addColumn(self::COL_ACTIVE, t('Current?'))->setCompact()->alignCenter();
        $this->grid->addColumn(self::COL_DEFAULT_AREA, t('Default area'));
        $this->grid->addColumn(self::COL_ENABLED, t('Enabled areas'));
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    protected function createCollection(): AppSetsCollection
    {
        return AppSetsCollection::getInstance();
    }

    protected function getEntryData(DBHelperRecordInterface $record, DBHelper_BaseFilterCriteria_Record $entry): UI_DataGrid_Entry
    {
        $set = $this->resolveAppSet($record);

        return $this->grid->createEntry(array(
            self::COL_ID => $set->getID(),
            self::COL_ALIAS => sb()->code($set->getAlias()),
            self::COL_LABEL => $set->getLabelLinked(),
            self::COL_ACTIVE => $set->getActiveBadge(),
            self::COL_DEFAULT_AREA => $set->getDefaultArea()->getTitle(),
            self::COL_ENABLED => $this->renderEnabled($set)
        ));
    }

    protected function configureActions(): void
    {
    }
}
