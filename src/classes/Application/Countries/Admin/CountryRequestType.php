<?php

declare(strict_types=1);

namespace Application\Countries\Admin;

use Application\AppFactory;
use Application_Countries;
use Application_Countries_Country;
use AppUtils\ClassHelper;
use DBHelper\Admin\Requests\BaseDBRecordRequestType;
use UI\AdminURLs\AdminURLInterface;

class CountryRequestType extends BaseDBRecordRequestType
{
    public function getCollection(): Application_Countries
    {
        return AppFactory::createCountries();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->getCollection()->adminURL()->list();
    }

    public function getRecord(): ?Application_Countries_Country
    {
        $record = parent::getRecord();

        if($record instanceof Application_Countries_Country) {
            return $record;
        }

        return null;
    }

    public function getRecordOrRedirect(): Application_Countries_Country
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Countries_Country::class,
            parent::getRecordOrRedirect()
        );
    }

    public function requireRecord(): Application_Countries_Country
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Countries_Country::class,
            parent::requireRecord()
        );
    }
}
