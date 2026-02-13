<?php

declare(strict_types=1);

namespace AppFrameworkTests\Countries;

use Application\AI\Cache\AICacheLocation;
use Application\Countries\AITools\CountryAITools;
use Mistralys\AppFrameworkTests\TestClasses\CountriesTestCase;

final class AITests extends CountriesTestCase
{
    public function test_getCountries() : void
    {
        $this->createTestCountry('fr', 'France');

        $class = new CountryAITools();

        $countries = $class->listCountries();

        $this->assertNotEmpty($countries);
    }

    protected function setUp(): void
    {
        parent::setUp();

        AICacheLocation::getInstance()->clear();
    }
}
