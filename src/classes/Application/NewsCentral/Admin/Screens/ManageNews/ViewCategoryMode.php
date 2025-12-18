<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode;

use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Screens\Mode\ViewCategory\SettingsSubmode;
use Application\NewsCentral\Admin\Traits\ManageNewsModeInterface;
use Application\NewsCentral\Admin\Traits\ManageNewsModeTrait;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property Category $record
 */
class ViewCategoryMode extends BaseRecordMode implements ManageNewsModeInterface
{
    use ManageNewsModeTrait;

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
        return SettingsSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return SettingsSubmode::URL_NAME;
    }

    protected function createCollection() : CategoriesCollection
    {
        return AppFactory::createNews()->createCategories();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
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
            ->makeLinked($this->createCollection()->adminURL()->list());

        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->adminURL()->base());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();

        $this->subnav->addURL(
            t('Settings'),
            $this->record->adminURL()->settings(),
        )
            ->setIcon(UI::icon()->settings());
    }

    public function getCategory() : Category
    {
        return $this->record;
    }
}
