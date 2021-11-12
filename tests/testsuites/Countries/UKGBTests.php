<?php

declare(strict_types=1);

final class Countries_UKGBTests extends CountriesTestCase
{
    public function test_isoExists_UK() : void
    {
        $this->countries->createNewCountry('gb', 'Great Britain');

        $this->assertTrue($this->countries->isoExists('GB'));
        $this->assertTrue($this->countries->isoExists('UK'));
    }

    public function test_isoExists_GB() : void
    {
        $this->countries->createNewCountry('uk', 'United Kingdom');

        $this->assertTrue($this->countries->isoExists('GB'));
        $this->assertTrue($this->countries->isoExists('UK'));
    }

    public function test_getCountryByLocale_UK() : void
    {
        $this->countries->createNewCountry('uk', 'United Kingdom');
        
        $this->countries->getByISO('GB');
        $this->countries->getByISO('UK');
        
        $this->addToAssertionCount(1);
    }

    /**
     * Using createNewRecord to bypass the automatic ISO conversion
     * must throw an exception.
     */
    public function test_bypassCreateMethod() : void
    {
        $this->expectExceptionCode(Application_Countries::ERROR_INVALID_ISO_CODE);

        $this->countries->createNewRecord(array(
            Application_Countries_Country::COL_ISO => 'gb',
            Application_Countries_Country::COL_LABEL => 'Great Britain'
        ));
    }
}
