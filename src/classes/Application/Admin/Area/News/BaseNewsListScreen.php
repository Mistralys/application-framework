<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application\NewsCentral\NewsFilterCriteria;
use Application\NewsCentral\NewsScreenRights;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_CollectionList;
use Application_User;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use Closure;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;
use UI_DataGrid_Action;

/**
 * @property NewsCollection $collection
 * @property NewsFilterCriteria $filters
 */
abstract class BaseNewsListScreen extends Application_Admin_Area_Mode_CollectionList
{
    use AllowableMigrationTrait;

    public const URL_NAME = self::URL_NAME_DEFAULT;
    public const COLUMN_ID = 'id';
    public const COLUMN_LABEL = 'label';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_AUTHOR = 'author';
    public const COLUMN_MODIFIED = 'modified';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_CREATED = 'created';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_NEWS_LIST;
    }

    /**
     * @return NewsCollection
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createNews();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : array
    {
        $newsEntry = ClassHelper::requireObjectInstanceOf(NewsEntry::class, $record);

        return array(
            self::COLUMN_ID => $newsEntry->getID(),
            self::COLUMN_TYPE => $newsEntry->getType()->getIcon(),
            self::COLUMN_LABEL => sb()->add($newsEntry->getLabelLinked())->add($newsEntry->getSchedulingBadge()),
            self::COLUMN_STATUS => $newsEntry->getStatus()->getIconLabel(),
            self::COLUMN_AUTHOR => $newsEntry->getAuthor()->getName(),
            self::COLUMN_CREATED => ConvertHelper::date2listLabel($newsEntry->getDateCreated(), true, true),
            self::COLUMN_MODIFIED => ConvertHelper::date2listLabel($newsEntry->getDateModified(), true, true),
        );
    }

    protected function configureFilters(): void
    {
        $this->filters->selectSchedulingEnabled(false);
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COLUMN_TYPE, t('Type'))
            ->setCompact()
            ->setSortable(false, NewsCollection::COL_NEWS_TYPE);

        $this->grid->addColumn(self::COLUMN_LABEL, t('Title'))
            ->setSortable(false, NewsCollection::COL_LABEL);

        $this->grid->addColumn(self::COLUMN_STATUS, t('Status'))
            ->setSortable(false, NewsCollection::COL_STATUS);

        $this->grid->addColumn(self::COLUMN_AUTHOR, t('Author'));

        $this->grid->addColumn(self::COLUMN_MODIFIED, t('Last modified'))
            ->setSortable(false, NewsCollection::COL_DATE_MODIFIED);

        $this->grid->addColumn(self::COLUMN_CREATED, t('Created'))
            ->setSortable(true, NewsCollection::COL_DATE_CREATED);
    }

    protected function configureActions(): void
    {
        $this->grid->enableLimitOptionsDefault();
        $this->grid->enableMultiSelect(self::COLUMN_ID);

        $this->grid->addAction('delete-news', t('Delete...'))
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
            ->makeConfirm(sb()
                ->para(sb()
                    ->t('This will delete the selected news entries.')
                )
                ->para(sb()
                    ->cannotBeUndone()
                )
            )
            ->setCallback(Closure::fromCallable(array($this, 'handleMultiDelete')));
    }

    private function handleMultiDelete(UI_DataGrid_Action $action) : void
    {
        $collection = $this->createCollection();

        $action->createRedirectMessage($collection->getAdminListURL())
            ->single(t('The news entry %1$s has been deleted successfully at %2$s.', '$label', '$time'))
            ->multiple(t('%1$s news entries have been deleted successfully at %2$s.', '$amount', '$time'))
            ->none(t('No news entries selected that could be deleted.'))
            ->processDeleteDBRecords($collection)
            ->redirect();
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-article', t('Create article...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->collection->getAdminCreateArticleURL())
            ->requireRight(Application_User::RIGHT_CREATE_NEWS);

        $this->sidebar->addButton('create-alert', t('Create news alert...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->collection->getAdminCreateAlertURL())
            ->requireRight(Application_User::RIGHT_CREATE_NEWS_ALERTS);


        parent::_handleSidebar();
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available news articles');
    }
}
