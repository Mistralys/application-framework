<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application\Admin\Area\News\ViewCategory\BaseCategorySettingsScreen;
use Application\AppFactory;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application\NewsCentral\NewsScreenRights;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;

/**
 * @property Category $record
 */
abstract class BaseViewCategoryScreen extends BaseRecordMode
{
    public const string URL_NAME = 'view-category';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_VIEW_CATEGORIES;
    }

    public function getDefaultSubmode(): string
    {
        return BaseCategorySettingsScreen::URL_NAME;
    }

    protected function createCollection() : CategoriesCollection
    {
        return AppFactory::createNews()->createCategories();
    }

    public function getRecordMissingURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getNavigationTitle(): string
    {
        return t('View');
    }

    public function getTitle(): string
    {
        return t('View news category');
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->record->getLabel());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem(t('Categories'))
            ->makeLinked($this->createCollection()->getAdminListURL());

        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->getAdminURL());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();

        $this->subnav->addURL(
            t('Settings'),
            $this->record->getAdminSettingsURL(),
        )
            ->setIcon(UI::icon()->settings());
    }

    public function getCategory() : Category
    {
        return $this->record;
    }
}
