<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode\ViewArticle;

use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Traits\ViewArticleSubmodeInterface;
use Application\NewsCentral\Admin\Traits\ViewArticleSubmodeTrait;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsSettingsManager;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use NewsCentral\Entries\NewsEntry;

/**
 * @property NewsEntry $record
 * @property NewsCollection $collection
 */
class BaseArticleSettingsScreen extends BaseRecordSettingsSubmode implements ViewArticleSubmodeInterface
{
    use ViewArticleSubmodeTrait;

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

    public function getSettingsManager() : NewsSettingsManager
    {
        return $this->createCollection()->createSettingsManager($this, $this->record);
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_ARTICLE_SETTINGS;
    }

    /**
     * @return array<string, string>
     */
    public function getFeatureRights(): array
    {
        return array(
            t('Modify the settings') => NewsScreenRights::SCREEN_ARTICLE_SETTINGS_EDIT
        );
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->can(NewsScreenRights::SCREEN_ARTICLE_SETTINGS_EDIT);
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t('The settings have been saved successfully at %1$s.', sb()->time());
    }
}
