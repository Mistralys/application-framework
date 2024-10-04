<?php

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use Application\Admin\Area\News\BaseViewCategoryScreen;
use Application\Admin\Area\News\ViewCategory\BaseCategorySettingsScreen;
use Application\Interfaces\Admin\AdminScreenInterface;
use DBHelper_BaseRecord;

/**
 * @method CategoriesCollection getCollection()
 */
class Category extends DBHelper_BaseRecord
{
    public function getLabel(): string
    {
        return $this->getRecordStringKey(CategoriesCollection::COL_LABEL);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()->link(
            $this->getLabel(),
            $this->getAdminURL()
        );
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = BaseViewCategoryScreen::URL_NAME;
        $params[CategoriesCollection::PRIMARY_NAME] = $this->getID();

        return $this->getCollection()->getAdminURL($params);
    }

    public function getAdminSettingsURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_SUBMODE] = BaseCategorySettingsScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getLiveURL(array $params=array()) : string
    {
        return '';
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }
}
