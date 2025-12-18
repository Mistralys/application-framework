<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode;

use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Traits\ManageNewsModeInterface;
use Application\NewsCentral\Admin\Traits\ManageNewsModeTrait;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsFilterCriteria;
use Application_User;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use Closure;
use DBHelper\Admin\Screens\Mode\BaseRecordListMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseFilterCriteria_Record;
use NewsCentral\Entries\NewsEntry;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_DataGrid_Action;

/**
 * @property NewsCollection $collection
 * @property NewsFilterCriteria $filters
 */
class NewsListMode extends BaseRecordListMode implements ManageNewsModeInterface
{
    use ManageNewsModeTrait;

    public const string URL_NAME = self::URL_NAME_DEFAULT;
    public const string COLUMN_ID = 'id';
    public const string COLUMN_LABEL = 'label';
    public const string COLUMN_TYPE = 'type';
    public const string COLUMN_AUTHOR = 'author';
    public const string COLUMN_MODIFIED = 'modified';
    public const string COLUMN_STATUS = 'status';
    public const string COLUMN_CREATED = 'created';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_NEWS_LIST;
    }

    protected function createCollection(): NewsCollection
    {
        return AppFactory::createNews();
    }

    protected function getEntryData(DBHelperRecordInterface $record, DBHelper_BaseFilterCriteria_Record $entry) : array
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

        $action->createRedirectMessage($collection->adminURL()->manage()->list())
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
            ->makeLinked($this->collection->adminURL()->manage()->createArticle())
            ->requireRight(Application_User::RIGHT_CREATE_NEWS);

        $this->sidebar->addButton('create-alert', t('Create news alert...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->collection->adminURL()->manage()->createAlert())
            ->requireRight(Application_User::RIGHT_CREATE_NEWS_ALERTS);


        parent::_handleSidebar();
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->manage()->list();
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
