<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode;

use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Traits\ManageNewsModeInterface;
use Application\NewsCentral\Admin\Traits\ManageNewsModeTrait;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application\NewsCentral\Categories\CategorySettingsManager;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property Category|NULL $record
 * @property CategoriesCollection $collection
 */
class CreateCategoryMode extends BaseRecordCreateMode implements ManageNewsModeInterface
{
    use ManageNewsModeTrait;

    public const string URL_NAME = 'create-category';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_CREATE_CATEGORY;
    }

    public function getSettingsManager() : CategorySettingsManager
    {
        return $this->createCollection()->createSettingsManager($this, $this->record);
    }

    /**
     * @return CategoriesCollection
     */
    public function createCollection() : CategoriesCollection
    {
        return AppFactory::createNews()->createCategories();
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The news category %1$s has been created successfully at %2$s.',
            $record->getLabel(),
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getTitle(): string
    {
        return t('Create a news category');
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->t('This lets you add a news category, which can be used to categorize news articles and alerts.');
    }
}
