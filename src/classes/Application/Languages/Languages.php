<?php
/**
 * @package Application
 * @subpackage Countries
 */

declare(strict_types=1);

namespace Application\Languages;

use Application\AppFactory;
use AppLocalize\Localization\Countries\CountryCollection;
use AppUtils\Collections\BaseStringPrimaryCollection;

/**
 * Language collection used to fetch information on the
 * available languages in the system.
 *
 * NOTE: The available languages are adjusted automatically
 * based on changes in the country collection.
 *
 * @package Application
 * @subpackage Countries
 *
 * @method Language getByID(string $id)
 * @method Language getDefault()
 * @method Language[] getAll()
 */
class Languages extends BaseStringPrimaryCollection
{
    private static ?Languages $instance = null;

    public static function getInstance() : Languages
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
        $countries->onAfterCreateRecord($this->reset(...));
        $countries->onAfterDeleteRecord($this->reset(...));
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }

    public function getByISO(string $iso) : Language
    {
        return $this->getByID(strtolower($iso));
    }

    protected function registerItems(): void
    {
        $countries = AppFactory::createCountries()->getAll(false);
        $languages = array();

        // Build a language collection from the countries
        foreach ($countries as $country)
        {
            $iso = $country->getLanguageCode();

            if(!isset($languages[$iso])) {
                $languages[$iso] = true;
                $this->registerItem(new Language($iso, CountryCollection::getInstance()->getByISO($country->getISO())->getMainLocale()));
            }
        }
    }
}
