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
    }
}
