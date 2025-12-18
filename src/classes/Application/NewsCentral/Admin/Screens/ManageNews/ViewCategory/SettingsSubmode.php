<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode\ViewCategory;

use Application\Admin\ClassLoaderScreenInterface;
use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Screens\Mode\ViewCategoryMode;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application\NewsCentral\Categories\CategorySettingsManager;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property ViewCategoryMode $mode
 * @property Category $record
 * @property CategoriesCollection $collection
 */
class SettingsSubmode extends BaseRecordSettingsSubmode implements ClassLoaderScreenInterface
{
    public const string URL_NAME = 'settings';

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

    public function getParentScreenClass(): string
    {
        return ViewCategoryMode::class;
    }

    public function getSettingsManager() : CategorySettingsManager
    {
        return $this->createCollection()->createSettingsManager($this, $this->record);
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_CATEGORY_SETTINGS;
    }

    /**
     * @return array<string, string>
     */
    public function getFeatureRights(): array
    {
        return array(
            t('Modify the settings') => NewsScreenRights::SCREEN_CATEGORY_SETTINGS_EDIT
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

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t('The settings have been saved successfully at %1$s.', sb()->time());
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }
}
