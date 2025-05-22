<?php
/**
 * @package Application
 * @subpackage Countries
 */

declare(strict_types=1);

namespace Application;

use Application\Languages\Language;
use Application\Languages\LanguageException;
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
    public const ERROR_UNKNOWN_LANGUAGE_LABEL = 37802;

    public const LANG_DE = 'de';
    public const LANG_EN = 'en';
    public const LANG_FR = 'fr';
    public const LANG_IT = 'it';
    public const LANG_ES = 'es';
    public const LANG_PL = 'pl';
    public const LANG_RO = 'ro';

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
        $countries->onAfterCreateRecord(array($this, 'reset'));
        $countries->onAfterDeleteRecord(array($this, 'reset'));
    }

    public function reset() : void
    {
        unset($this->items);
    }

    public function getDefaultID(): string
    {
        return self::LANG_EN;
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
                $this->registerItem(new Language($iso));
            }
        }
    }

    /**
     * @var array<string,string>|NULL
     */
    protected static ?array $languageLabels = null;

    /**
     * @return array<string,string>
     */
    protected static function getLanguageLabels() : array
    {
        if(isset(self::$languageLabels)) {
            return self::$languageLabels;
        }

        self::$languageLabels = array(
            self::LANG_DE => t('German'),
            self::LANG_EN => t('English'),
            self::LANG_FR => t('French'),
            self::LANG_IT => t('Italian'),
            self::LANG_ES => t('Spanish'),
            self::LANG_PL => t('Polish'),
            self::LANG_RO => t('Romanian')
        );

        return self::$languageLabels;
    }

    /**
     * @param string $iso
     * @return string
     * @throws LanguageException
     */
    public static function getLabelByISO(string $iso) : string
    {
        $iso = strtolower($iso);
        $labels = self::getLanguageLabels();

        if(isset($labels[$iso])) {
            return $labels[$iso];
        }

        throw new LanguageException(
            sprintf('Unknown language label for language [%s]', $iso),
            '',
            self::ERROR_UNKNOWN_LANGUAGE_LABEL
        );
    }
}
