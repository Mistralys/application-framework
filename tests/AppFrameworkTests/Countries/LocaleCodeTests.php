<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Countries;

use Mistralys\AppFrameworkTests\TestClasses\CountriesTestCase;

/**
 * @package Application
 * @subpackage UnitTests
 */
final class LocaleCodeTests extends CountriesTestCase
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

    public function test_getLanguageLabel() : void
    {
        $this->assertSame('Finnish (Finland)', $this->createTestCountry('fi')->getLanguage()->getLabel());
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->createTestCountry('mx');
    }
}
