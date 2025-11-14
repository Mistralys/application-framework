<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application\NewsCentral\NewsScreenRights;
use Application\NewsCentral\NewsSettingsManager;
use Application\Traits\AllowableMigrationTrait;
use DBHelper\Admin\Screens\Submode\BaseRecordCreateSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @property NewsEntry|NULL $record
 * @property NewsCollection $collection
 */
abstract class BaseCreateArticleScreen extends BaseRecordCreateSubmode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'create-article';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_CREATE_ARTICLE;
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

    public function getSuccessMessage(DBHelperRecordInterface $record): string
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
