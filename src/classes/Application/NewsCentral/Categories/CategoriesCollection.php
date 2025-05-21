<?php
/**
 * @package Application
 * @subpackage News
 * @see \Application\NewsCentral\Categories\CategoriesCollection
 */

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use Application\Admin\Area\News\BaseCategoriesListScreen;
use Application\Admin\Area\News\BaseCreateCategoryScreen;
use Application\AppFactory;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Formable;
use DBHelper_BaseCollection;

/**
 * @package Application
 * @subpackage News
 *
 * @method Category getByID(int $record_id)
 * @method Category[] getAll()
 * @method Category|NULL getByRequest()
 * @method CategoriesFilterCriteria getFilterCriteria()
 * @method CategoriesFilterSettings getFilterSettings()
 * @method Category createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class CategoriesCollection extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'app_news_categories';
    public const PRIMARY_NAME = 'news_category_id';

    public const COL_LABEL = 'label';

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

    public function getRecordTypeName(): string
    {
        return 'news_category';
    }

    public function getCollectionLabel(): string
    {
        return t('News categories');
    }

    public function getRecordLabel(): string
    {
        return t('News category');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    public function createNewCategory(string $string) : Category
    {
        return $this->createNewRecord(array(
            self::COL_LABEL => $string
        ));
    }

    public function createSettingsManager(Application_Formable $formable, ?Category $record=null) : CategorySettingsManager
    {
        return new CategorySettingsManager($formable, $record);
    }

    public function getAdminListURL(array $params=array()): string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = BaseCategoriesListScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminURL(array $params=array()) : string
    {
        return AppFactory::createNews()->getAdminURL($params);
    }

    public function getAdminCreateURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = BaseCreateCategoryScreen::URL_NAME;

        return $this->getAdminURL($params);
    }
}
