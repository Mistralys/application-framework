<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application\AppFactory;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application\NewsCentral\NewsScreenRights;
use Application\Traits\AllowableMigrationTrait;
use AppUtils\ClassHelper;
use Closure;
use DBHelper\Admin\Screens\Mode\BaseRecordListMode;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;
use UI_DataGrid_Action;

abstract class BaseCategoriesListScreen extends BaseRecordListMode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'categories-list';

    public const string COL_LABEL = 'label';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    /**
     * @return CategoriesCollection
     */
    protected function createCollection(): CategoriesCollection
    {
        return AppFactory::createNews()->createCategories();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry)
    {
        $category = ClassHelper::requireObjectInstanceOf(Category::class, $record);

        return array(
            self::COL_LABEL => $category->getLabelLinked()
        );
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_LABEL, t('Label'));
    }

    protected function configureActions(): void
    {
        $this->grid->addAction('delete', t('Delete...'))
            ->makeDangerous()
            ->makeConfirm(sb()
                ->para(sb()
                    ->t('This will delete the selected news categories.')
                    ->t('Connected news articles will be left untouched, but will no longer be available through the categories.')
                )
                ->para(sb()->cannotBeUndone())
            )
            ->setCallback(Closure::fromCallable(array($this, 'handleMultiDelete')));
    }

    private function handleMultiDelete(UI_DataGrid_Action $action) : void
    {
        $action->createRedirectMessage($this->createCollection()->getAdminListURL())
            ->single(t('The news category %1$s has been deleted successfully at %2$s.', '$label', '$time'))
            ->multiple(t('%1$s news categories have been deleted successfully at %2$s.', '$amount', '$time'))
            ->none(t('No news categories were selected that could be deleted.'))
            ->processDeleteDBRecords($this->createCollection())
            ->redirect();
        ;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem(t('Categories'))
            ->makeLinked($this->createCollection()->getAdminListURL());

        parent::_handleBreadcrumb();
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-category', t('Create new category...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->createCollection()->getAdminCreateURL());

        parent::_handleSidebar();
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminURL();
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available categories');
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_CATEGORIES_LIST;
    }
}
