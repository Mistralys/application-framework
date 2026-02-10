<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode;

use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Traits\ManageNewsModeInterface;
use Application\NewsCentral\Admin\Traits\ManageNewsModeTrait;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsSettingsManager;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use NewsCentral\Entries\NewsEntry;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property NewsEntry|NULL $record
 * @property NewsCollection $collection
 */
class CreateArticleScreen extends BaseRecordCreateMode implements ManageNewsModeInterface
{
    use ManageNewsModeTrait;

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

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->manage()->list();
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
