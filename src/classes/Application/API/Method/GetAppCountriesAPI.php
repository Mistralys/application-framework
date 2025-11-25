<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\API\Method;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\AppFactory;
use Application\Countries\API\AppCountryAPIInterface;
use Application\Countries\API\CountriesAPIGroup;
use Application_Countries;
use Application_Countries_Country;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Country\CountryGB;
use AppUtils\ArrayDataCollection;

/**
 * API method to retrieve the list of available tenants.
 *
 * @package Countries
 * @subpackage API
 */
class GetAppCountriesAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'GetAppCountries';
    public const string VERSION_1 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1;
    public const array VERSIONS = array(
        self::VERSION_1
    );

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getVersions() : array
    {
        return self::VERSIONS;
    }

    public function getCurrentVersion() : string
    {
        return self::CURRENT_VERSION;
    }

    public function getGroup(): APIGroupInterface
    {
        return CountriesAPIGroup::create();
    }

    // region: A - Payload

    public const string KEY_COUNTRIES = 'countries';

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $response->setKey(self::KEY_COUNTRIES, $this->collectCountries($this->appCountries->getAll()));
    }

    /**
     * @param Application_Countries_Country[] $countries
     * @return array<string,mixed>
     */
    private function collectCountries(array $countries) : array
    {
        $result = array();

        foreach($countries as $country)
        {
            $result[$country->getISO()] = $this->collectCountry($country);
        }

        ksort($result);

        return $result;
    }

    /**
     * @param Application_Countries_Country $country
     * @return array<string,mixed>
     */
    private function collectCountry(Application_Countries_Country $country) : array
    {
        $currency = $country->getCurrency();
        $locale = $country->getLocalizationLocale();
        $countryDef = $locale->getCountry();

        return array(
            AppCountryAPIInterface::KEY_COUNTRY_ID => $country->getID(),
            AppCountryAPIInterface::KEY_COUNTRY_ISO => $country->getISO(),
            'labelInvariant' => $country->getLabel(),
            'codeAliases' => $countryDef->getAliases(),
            'defaultLocale' => array(
                'localeCode' => $locale->getID(),
                'languageCode' => $locale->getLanguageCode(),
                'labelInvariant' => $locale->getLabelInvariant()
            ),
            'currency' => array(
                'isoCode' => $currency->getISO(),
                'labelSingular' => $currency->getSingular(),
                'labelPlural' => $currency->getPlural(),
                'symbol' => $currency->getSymbol(),
                'preferredSymbol' => $currency->getPreferredSymbol(),
                'thousandsSeparator' => $currency->getThousandsSeparator(),
                'decimalSeparator' => $currency->getDecimalsSeparator(),
                'structuralTemplate' => $currency->getStructuralTemplate($countryDef)
            )
        );
    }

    // endregion

    // region: B - Setup

    private Application_Countries $appCountries;

    protected function init() : void
    {
        $this->appCountries = AppFactory::createCountries();
    }

    protected function collectRequestData(string $version): void
    {
    }

    // endregion


    // region: C - Documentation

    public function getDescription() : string
    {
        return sprintf(
            <<<'MARKDOWN'
Gets information on all known countries available in the 
%1$s application, including their default locale and currency.
MARKDOWN,
            $this->driver->getAppName()
        );
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::KEY_COUNTRIES => $this->collectCountries(array(
                $this->appCountries->getByISO(CountryDE::ISO_CODE),
                $this->appCountries->getByISO(CountryGB::ISO_CODE)
            ))
        );
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }

    // endregion
}
