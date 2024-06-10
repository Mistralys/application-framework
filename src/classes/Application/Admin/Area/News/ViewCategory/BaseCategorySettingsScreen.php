<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ViewCategory;

use Application\Admin\Area\Mode\Submode\BaseCollectionEditExtended;
use Application\Admin\Area\News\BaseViewCategoryScreen;
use Application\AppFactory;
use Application\Interfaces\AllowableInterface;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application\NewsCentral\Categories\CategorySettingsManager;
use Application\NewsCentral\NewsScreenRights;
use Application\Traits\AllowableMigrationTrait;
use DBHelper_BaseRecord;

/**
 * @property BaseViewCategoryScreen $mode
 * @property Category $record
 * @property CategoriesCollection $collection
 */
abstract class BaseCategorySettingsScreen extends BaseCollectionEditExtended
{
    use AllowableMigrationTrait;

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

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_CATEGORY_SETTINGS;
    }

    public function getFeatureRights(): array
    {
        return array(
            NewsScreenRights::SCREEN_CATEGORY_SETTINGS_EDIT => t('Modify the settings')
        );
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->can(NewsScreenRights::SCREEN_CATEGORY_SETTINGS_EDIT);
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
