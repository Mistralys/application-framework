<?php

declare(strict_types=1);

namespace Application\Admin\Area\Devel\News;

use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsSettingsManager;
use Application_Admin_Area_Mode_Submode_CollectionCreate;
use DBHelper_BaseRecord;

abstract class BaseCreateArticleScreen extends Application_Admin_Area_Mode_Submode_CollectionCreate
{
    public const URL_NAME = 'create-article';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canCreateNews();
    }

    public function getSettingsManager() : NewsSettingsManager
    {
        return $this->createCollection()->createSettingsManager($this, $this->record);
    }

    /**
     * @return NewsCollection
     */
    public function createCollection() : NewsCollection
    {
        return AppFactory::createNews();
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return t(
            'The news article has been created successfully at %1$s.',
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getTitle(): string
    {
        return t('Create a news article');
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->t('This lets you compose a news article.')
            ->note()
            ->t('It will not be published right away after saving, it will be added as a draft.');
    }
}
