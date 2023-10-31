<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ViewCategory;

use Application\Admin\Area\Mode\Submode\BaseCollectionEditExtended;
use Application\Admin\Area\News\BaseViewCategoryScreen;
use Application\AppFactory;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application\NewsCentral\Categories\CategorySettingsManager;
use DBHelper_BaseRecord;

/**
 * @property BaseViewCategoryScreen $mode
 * @property Category $record
 * @property CategoriesCollection $collection
 */
class BaseCategorySettingsScreen extends BaseCollectionEditExtended
{
    public const URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Settings');
    }

    public function getTitle(): string
    {
        return t('Settings');
    }

    public function getSettingsManager() : CategorySettingsManager
    {
        return $this->createCollection()->createSettingsManager($this, $this->record);
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->canEditNews();
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function createCollection() : CategoriesCollection
    {
        return AppFactory::createNews()->createCategories();
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t('The settings have been saved successfully at %1$s.', sb()->time());
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }
}
