<?php

declare(strict_types=1);

namespace Application\NewsCentral\Categories;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\NewsCentral\Admin\CategoryAdminURLs;
use Application\NewsCentral\Admin\Screens\Mode\ViewCategoryMode;
use Application\NewsCentral\Admin\Screens\Mode\ViewCategory\CategorySettingsSubmode;
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
            $this->adminURL()->base()
        );
    }

    public function adminURL() : CategoryAdminURLs
    {
        return new CategoryAdminURLs($this);
    }

    public function getLiveURL(array $params=array()) : string
    {
        return '';
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }
}
