<?php

declare(strict_types=1);

namespace Application\Admin\Area\Devel\News\ViewArticle;

use Application\Admin\Area\Devel\News\BaseViewArticleScreen;
use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application\NewsCentral\NewsSettingsManager;
use Application_Admin_Area_Mode_Submode_Action_CollectionEdit;
use DBHelper_BaseRecord;

/**
 * @property BaseViewArticleScreen $submode
 * @property NewsEntry $record
 * @property NewsCollection $collection
 */
class BaseArticleSettingsScreen extends Application_Admin_Area_Mode_Submode_Action_CollectionEdit
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

    public function getSettingsManager() : NewsSettingsManager
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

    public function createCollection() : NewsCollection
    {
        return AppFactory::createNews();
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
