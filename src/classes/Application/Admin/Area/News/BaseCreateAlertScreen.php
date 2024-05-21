<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsRightsInterface;
use Application\NewsCentral\NewsSettingsManager;
use Application\Traits\AllowableMigrationTrait;
use DBHelper_BaseRecord;

abstract class BaseCreateAlertScreen extends BaseCreateArticleScreen
{
    use AllowableMigrationTrait;

    public const URL_NAME = 'create-alert';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRights(): array
    {
        return array(
             NewsRightsInterface::RIGHT_CREATE_NEWS_ALERTS
        );
    }

    public function getFeatureRights(): array
    {
        return array();
    }

    public function getSettingsManager() : NewsSettingsManager
    {
        return parent::getSettingsManager()->makeAlert();
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
            'The news alert has been created successfully at %1$s.',
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getTitle(): string
    {
        return t('Create a news alert');
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->t('This lets you add a news alert, which will be shown prominently in the user interface.')
            ->note()
            ->t('It will not be published right away after saving, it will be added as a draft.');
    }
}
