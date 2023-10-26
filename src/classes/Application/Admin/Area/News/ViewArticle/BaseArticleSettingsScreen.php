<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ViewArticle;

use Application\Admin\Area\Mode\Submode\BaseCollectionEditExtended;
use Application\Admin\Area\News\BaseViewArticleScreen;
use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application\NewsCentral\NewsSettingsManager;
use DBHelper_BaseRecord;

/**
 * @property BaseViewArticleScreen $mode
 * @property NewsEntry $record
 * @property NewsCollection $collection
 */
class BaseArticleSettingsScreen extends BaseCollectionEditExtended
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
