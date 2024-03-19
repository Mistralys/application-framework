<?php

declare(strict_types=1);

namespace Application;

use Application\Languages\Language;
use Application\Locales\Locale;
use AppUtils\Collections\BaseStringPrimaryCollection;

/**
 * Locale collection class, used to fetch information on the
 * available locales in the system (based on the available
 * countries).
 *
 * NOTE: The available locales are adjusted automatically
 * based on changes in the country collection.
 *
 * @package Application
 * @subpackage Countries
 *
 * @method Locale getByID(string $id)
 * @method Locale getDefault()
 * @method Locale[] getAll()
 */
class Locales extends BaseStringPrimaryCollection
{
    public const LOCALE_EN_US = 'en_US';
    public const LOCALE_EN_GB = 'en_GB';
    public const LOCALE_EN_UK = 'en_UK';
    public const LOCALE_DE_DE = 'de_DE';
    public const LOCALE_DE_AT = 'de_AT';
    public const LOCALE_FR_FR = 'fr_FR';
    public const LOCALE_IT_IT = 'it_IT';
    public const LOCALE_ES_ES = 'es_ES';
    public const LOCALE_ES_MX = 'es_MX';
    public const LOCALE_PL_PL = 'pl_PL';
    public const LOCALE_RO_RO = 'ro_RO';

    private static ?Locales $instance = null;

    public static function getInstance() : Locales
    {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $countries = AppFactory::createCountries();

        // Register events to reset the collection when the countries change
        $countries->onIgnoredCountriesUpdated(array($this, 'reset'));
        $countries->onAfterCreateRecord(array($this, 'reset'));
        $countries->onAfterDeleteRecord(array($this, 'reset'));
    }

    public function reset() : void
    {
        unset($this->items);
    }

    public function getDefaultID(): string
    {
        return self::LOCALE_EN_US;
    }

    /**
     * @param Language $language
     * @return Locale[]
     */
    public function getByLanguage(Language $language) : array
    {
        $locales = array();
        $iso = $language->getISO();

        foreach($this->getAll() as $locale) {
            if($locale->getLangISO() === $iso) {
                $locales[] = $locale;
            }
        }

        return $locales;
    }

    protected function registerItems(): void
    {
        $countries = AppFactory::createCountries()->getAll(false);

        foreach($countries as $country) {
            $this->registerItem(new Locale($country->getLocaleCode()));
        }
    }
}
