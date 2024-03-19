<?php
/**
 * @package Application
 * @subpackage Countries
 */

declare(strict_types=1);

namespace Application\Languages;

use Application\AppFactory;
use Application\Languages;
use Application\Locales\Locale;
use Application_Countries_Country;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Holds information about a single language.
 *
 * @package Application
 * @subpackage Countries
 */
class Language implements StringPrimaryRecordInterface
{
    private string $iso;
    private ?string $label = null;

    /**
     * @param string $iso Two-letter ISO code, e.g. "en", "de".
     */
    public function __construct(string $iso)
    {
        $this->iso = strtolower($iso);
    }

    public function getID(): string
    {
        return $this->iso;
    }

    /**
     * @return string Translated language label.
     * @throws LanguageException
     */
    public function getLabel(): string
    {
        if(isset($this->label)) {
            return $this->label;
        }

        $this->label = Languages::getLabelByISO($this->getISO());

        return $this->label;
    }

    /**
     * @return string Lowercase two-letter ISO language code, e.g. "en", "de".
     */
    public function getISO(): string
    {
        return $this->iso;
    }

    /**
     * Fetches all countries available for this language.
     * @return Application_Countries_Country[]
     */
    public function getCountries(): array
    {
        return AppFactory::createCountries()->getByLanguage($this);
    }

    /**
     * Fetches all locales available for this language.
     * @return Locale[]
     */
    public function getLocales() : array
    {
        return AppFactory::createLocales()->getByLanguage($this);
    }
}
