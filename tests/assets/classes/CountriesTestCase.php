<?php

declare(strict_types=1);

abstract class CountriesTestCase extends ApplicationTestCase
{
    /**
     * @var Application_Countries
     */
    protected $countries;

    protected function setUp() : void
    {
        $this->countries = Application_Countries::getInstance();

        $this->startTransaction();

        DBHelper::deleteRecords(Application_Countries::TABLE_NAME);

        DBHelper::insertDynamic(
            $this->countries->getRecordTableName(),
            array(
                'iso' => 'mx',
                'label' => 'Mexico'
            )
        );
    }
}
