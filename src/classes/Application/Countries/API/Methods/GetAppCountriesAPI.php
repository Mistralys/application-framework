<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\Methods;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\API\Utilities\KeyDescription;
use Application\AppFactory;
use Application\Countries\API\AppCountryAPIInterface;
use Application\Countries\API\CountriesAPIGroup;
use Application\Locales\API\Methods\GetAppLocalesAPI;
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
    public const string KEY_LABEL_INVARIANT = 'labelInvariant';
    public const string KEY_CODE_ALIASES = 'codeAliases';
    public const string KEY_DEFAULT_LOCALE = 'defaultLocale';
    public const string KEY_DEFAULT_LOCALE_CODE = 'localeCode';
    public const string KEY_DEFAULT_LOCALE_LANGUAGE_CODE = 'languageCode';
    public const string KEY_CURRENCY = 'currency';
    public const string KEY_CURRENCY_ISO_CODE = 'isoCode';
    public const string KEY_CURRENCY_LABEL_SINGULAR = 'labelSingular';
    public const string KEY_CURRENCY_LABEL_PLURAL = 'labelPlural';
    public const string KEY_CURRENCY_SYMBOL = 'symbol';
    public const string KEY_CURRENCY_PREFERRED_SYMBOL = 'preferredSymbol';
    public const string KEY_CURRENCY_THOUSANDS_SEP = 'thousandsSeparator';
    public const string KEY_CURRENCY_DECIMAL_SEP = 'decimalSeparator';
    public const string KEY_CURRENCY_STRUCTURAL_TEMPLATE = 'structuralTemplate';

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
    public static function collectCountry(Application_Countries_Country $country) : array
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
        return array(
            GetAppLocalesAPI::METHOD_NAME
        );
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
        return array(
            KeyDescription::create(
                self::KEY_COUNTRIES,
                'Object keyed by country ISO code; each value is a country details object.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . AppCountryAPIInterface::KEY_COUNTRY_ID,
                'Numeric database identifier of the country.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . AppCountryAPIInterface::KEY_COUNTRY_ISO,
                'Two-letter ISO 3166-1 alpha-2 code identifying the country.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_LABEL_INVARIANT,
                'Locale-invariant display name of the country.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CODE_ALIASES,
                'Array of alternative ISO codes or aliases recognised for this country.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_DEFAULT_LOCALE,
                'Default locale configuration associated with this country.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_DEFAULT_LOCALE . '.' . self::KEY_DEFAULT_LOCALE_CODE,
                'Compound locale identifier (e.g. de_DE).'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_DEFAULT_LOCALE . '.' . self::KEY_DEFAULT_LOCALE_LANGUAGE_CODE,
                'Two-letter ISO 639-1 language code.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_DEFAULT_LOCALE . '.' . self::KEY_LABEL_INVARIANT,
                'Locale-invariant display name of the language.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY,
                'Currency details for the country.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_ISO_CODE,
                'Three-letter ISO 4217 currency code (e.g. EUR).'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_LABEL_SINGULAR,
                'Singular display name of the currency (e.g. Euro).'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_LABEL_PLURAL,
                'Plural display name of the currency (e.g. Euros).'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_SYMBOL,
                'Primary currency symbol (e.g. €).'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_PREFERRED_SYMBOL,
                'Preferred display symbol for the currency; may differ from the primary symbol in some locales.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_THOUSANDS_SEP,
                'Character used to separate thousands in formatted currency amounts.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_DECIMAL_SEP,
                'Character used to separate decimal digits in formatted currency amounts.'
            ),
            KeyDescription::create(
                self::KEY_COUNTRIES . '.' . self::KEY_CURRENCY . '.' . self::KEY_CURRENCY_STRUCTURAL_TEMPLATE,
                'Template string describing the structural position of symbol, integer, and decimal parts.'
            ),
        );
    }

    // endregion
}
