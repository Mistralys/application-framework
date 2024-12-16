<?php

declare(strict_types=1);

namespace AppFrameworkTests\Countries;

use Application_Countries_Country;
use Application_Countries_FilterCriteria;
use Mistralys\AppFrameworkTests\TestClasses\CountriesTestCase;

final class FilterCriteriaTests extends CountriesTestCase
{
    public function test_includeInvariant() : void
    {
        $this->createInvariantCountry();

        $this->assertFiltersContainISO(
            Application_Countries_Country::COUNTRY_INDEPENDENT_ISO,
            $this->countries->getFilterCriteria()
        );
    }

    public function test_excludeInvariant() : void
    {
        $this->createInvariantCountry();

        $this->assertFiltersNotContainISO(
            Application_Countries_Country::COUNTRY_INDEPENDENT_ISO,
            $this->countries->getFilterCriteria()
                ->excludeInvariant()
        );
    }

    public function assertFiltersContainISO(string $iso, Application_Countries_FilterCriteria $filters) : void
    {
        $this->_assertFiltersContainISO(true, $iso, $filters);
    }

    public function assertFiltersNotContainISO(string $iso, Application_Countries_FilterCriteria $filters) : void
    {
        $this->_assertFiltersContainISO(false, $iso, $filters);
    }

    private function _assertFiltersContainISO(bool $expected, string $iso, Application_Countries_FilterCriteria $filters) : void
    {
        $found = false;
        $ISOs = array();
        foreach($filters->getItemsObjects() as $country) {
            $ISOs[] = $country->getISO();
            if($country->getISO() === $iso) {
                $found = true;
            }
        }

        $this->assertSame(
            $expected,
            $found,
            sprintf(
                'ISO [%s] found: [%s]. Expected: [%s]. '.PHP_EOL.
                'Available ISOs: '.PHP_EOL.
                '- %s',
                $iso,
                bool2string($found),
                bool2string($expected),
                implode(PHP_EOL.'- ', $ISOs)
            )
        );
    }
}
