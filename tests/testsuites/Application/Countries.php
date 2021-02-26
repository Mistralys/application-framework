<?php

use PHPUnit\Framework\TestCase;

final class Application_CountriesTest extends TestCase
{
    public function test_isoExists_UK()
    {
        if($this->skipUKTests())
        {
            return;
        }

        $countries = Application_Countries::getInstance();

        $this->assertTrue($countries->isoExists('GB'));
        $this->assertTrue($countries->isoExists('UK'));
    }

    public function test_getCountryByLocale_UK()
    {
        if($this->skipUKTests())
        {
            return;
        }
        
        $countries = Application_Countries::getInstance();
        
        $countries->getByISO('GB');
        $countries->getByISO('UK');
        
        $this->addToAssertionCount(1);
    }

    private function skipUKTests() : bool
    {
        if($this->hasUK())
        {
            return false;
        }

        $this->markTestSkipped('UK is not in the list of countries.');

        return false;
    }

    private function hasUK() : bool
    {
        $isos = $this->getISOs();

        return in_array('uk', $isos) || in_array('gb', $isos);
    }

    private function getISOs() : array
    {
        return DBHelper::fetchAllKey(
        'iso',
        "SELECT
                `iso`
            FROM
                `countries`"
        );
    }
}
