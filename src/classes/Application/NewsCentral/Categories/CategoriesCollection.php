<?php
/**
 * @package Application
 * @subpackage News
 * @see \Application\NewsCentral\Categories\CategoriesCollection
 */

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use Application\AppFactory;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\NewsCentral\Admin\CategoriesAdminURLs;
use Application\NewsCentral\Admin\Screens\Mode\CategoriesListMode;
use Application\NewsCentral\Admin\Screens\Mode\CreateCategoryMode;
use Application_Formable;
use AppUtils\ClassHelper;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseCollection;
use testsuites\DBHelper\RecordTest;

/**
 * @package Application
 * @subpackage News
 *
 * @method Category[] getAll()
 * @method Category|NULL getByRequest()
 * @method CategoriesFilterCriteria getFilterCriteria()
 * @method CategoriesFilterSettings getFilterSettings()
 */
class CategoriesCollection extends DBHelper_BaseCollection
{
    public const string TABLE_NAME = 'app_news_categories';
    public const string PRIMARY_NAME = 'news_category_id';

    public const string COL_LABEL = 'label';
    public const string REQUEST_PRIMARY_NAME = 'news_category_id';
    public const string RECORD_TYPE = 'news_category';

    public function getRecordClassName(): string
    {
        return Category::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return CategoriesFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return CategoriesFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Label')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordRequestPrimaryName(): string
    {
        return self::REQUEST_PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return self::RECORD_TYPE;
    }

    public function getCollectionLabel(): string
    {
        return t('News categories');
    }

    public function getRecordLabel(): string
    {
        return t('News category');
    }


    public function getByID(int $record_id): Category
    {
        return ClassHelper::requireObjectInstanceOf(
            Category::class,
            parent::getByID($record_id)
        );
    }

    public function createNewCategory(string $string) : Category
    {
        return $this->createNewRecord(array(
            self::COL_LABEL => $string
        ));
    }

    public function createNewRecord(array $data = array(), bool $silent = false, array $options = array()): Category
    {
        return ClassHelper::requireObjectInstanceOf(
            Category::class,
            parent::createNewRecord($data, $silent, $options)
        );
    }

    public function createSettingsManager(Application_Formable $formable, ?Category $record=null) : CategorySettingsManager
    {
        return new CategorySettingsManager($formable, $record);
    }

    public function adminURL() : CategoriesAdminURLs
    {
        return new CategoriesAdminURLs();
    }
}
