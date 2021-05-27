<?php

declare(strict_types=1);

final class Countries_CoreTest extends CountriesTestCase
{
    public function test_parseLocaleCode() : void
    {
        $parsed = $this->countries->parseLocaleCode('es_MX');

        $this->assertEquals('es_MX', $parsed->getCode());
        $this->assertEquals('mx', $parsed->getCountryISO());
        $this->assertEquals('es', $parsed->getLanguageCode());
        $this->assertSame($this->countries->getByISO('mx'), $parsed->getCountry());
    }

    public function test_getByLocaleCode() : void
    {
        $this->assertSame(
            $this->countries->getByISO('mx'),
            $this->countries->getByLocaleCode('es_MX')
        );
    }
}
