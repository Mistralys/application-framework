<?php

declare(strict_types=1);

namespace Application;

use Application\Languages\Language;
use Application\Locales\Locale;
use AppLocalize\Localization;
use AppLocalize\Localization\Locale\en_US;
use AppLocalize\Localization\Locales\LocalesCollection;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\Collections\CollectionException;

/**
 * Locale collection class, used to fetch information on the
 * available locales in the system (based on the available
 * countries).
 *
 * > NOTE: The available locales are adjusted automatically
 * > based on changes in the country collection.
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
        $countries->onAfterCreateRecord(array($this, 'reset'));
        $countries->onAfterDeleteRecord(array($this, 'reset'));
    }

    public function reset() : void
    {
        unset($this->items);
    }

    public function getDefaultID(): string
    {
        return en_US::LOCALE_NAME;
    }

    /**
     * @param string $localeCode The locale code, e.g., `en_US`.
     * @return Locale
     * @throws CollectionException
     */
    public function getByCode(string $localeCode) : Locale
    {
        return $this->getByID($localeCode);
    }

    /**
     * @param Language $language
     * @return Locale[]
     */
    public function getByLanguage(Language $language) : array
    {
        $locales = array();
        $iso = $language->getISO();

        foreach(AppFactory::createCountries()->getAll() as $country) {
            if($country->getLanguageCode() === $iso) {
                $locales[] = $country->getLocale();
            }
        }

        return $locales;
    }

    protected function registerItems(): void
    {
        foreach(AppFactory::createCountries()->getAll() as $country) {
            $this->registerItem(new Locale($country->getLocalizationLocale()));
        }
    }
}
