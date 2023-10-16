<?php

declare(strict_types=1);

namespace Application\Admin\Area\Devel\News;

use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsSettingsManager;
use Application_Admin_Area_Mode_Submode_CollectionEdit;
use DBHelper_BaseRecord;

class BaseEditArticleScreen extends Application_Admin_Area_Mode_Submode_CollectionEdit
{
    public const URL_NAME = 'edit';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->canEditNews();
    }

    public function isEditable(): bool
    {
        return true;
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
}
