<?php

declare(strict_types=1);

abstract class CountriesTestCase extends ApplicationTestCase
{
    /**
     * @var Application_Countries
     */
    protected $countries;

    protected static $dbDone = false;

    protected function setUp() : void
    {
        $this->countries = Application_Countries::getInstance();

        if(self::$dbDone)
        {
            return;
        }

        self::$dbDone = true;

        DBHelper::insertDynamic(
            $this->countries->getRecordTableName(),
            array(
                'iso' => 'mx',
                'label' => 'Mexico'
            )
        );
    }
}
