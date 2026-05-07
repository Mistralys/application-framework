# Countries - Core Architecture (Public API)
_SOURCE: Application_Countries, Application_Countries_Country, CountriesCollection, CountrySettingsManager, FilterCriteria, FilterSettings, LocaleCode, CountryException, Navigator, Selector, ButtonBar, Icon_
# Application_Countries, Application_Countries_Country, CountriesCollection, CountrySettingsManager, FilterCriteria, FilterSettings, LocaleCode, CountryException, Navigator, Selector, ButtonBar, Icon
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Countries/
                └── ButtonBar.php
                └── Countries.php
                └── CountriesCollection.php
                └── Country.php
                └── Country/
                    ├── Icon.php
                └── CountryException.php
                └── CountrySettingsManager.php
                └── FilterCriteria.php
                └── FilterSettings.php
                └── LocaleCode.php
                └── Navigator.php
                └── Selector.php

```
###  Path: `/src/classes/Application/Countries/ButtonBar.php`

```php
namespace ;

use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\Interfaces\ClassableInterface as ClassableInterface;
use AppUtils\Interfaces\OptionableInterface as OptionableInterface;
use AppUtils\Traits\ClassableTrait as ClassableTrait;
use AppUtils\Traits\OptionableTrait as OptionableTrait;
use AppUtils\URLInfo as URLInfo;
use Application\Disposables\DisposableDisposedException as DisposableDisposedException;

/**
 * UI widget to create and display a country selection
 * that persists the user's choice in their user settings.
 *
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_ButtonBar extends UI_Renderable implements ClassableInterface, OptionableInterface
{
	use ClassableTrait;
	use OptionableTrait;

	public const ERROR_INVALID_COUNTRY_FOR_LINK = 54601;
	public const REQUEST_PARAM_SELECT_COUNTRY = 'select_country';
	public const OPTION_ENABLE_STORAGE = 'enableStorage';
	public const OPTION_DISPLAY_THRESHOLD = 'displayThreshold';
	public const OPTION_ENABLE_LABEL = 'enableLabel';
	public const OPTION_LABEL = 'label';

	/**
	 * Gets the setting name under which the country is stored in the user's settings.
	 * @param string $barID The button bar ID.
	 * @return string
	 */
	public static function getStorageName(string $barID): string
	{
		/* ... */
	}


	public function getDefaultOptions(): array
	{
		/* ... */
	}


	/**
	 * Sets the minimum number of items that have to be
	 * present for the bar to be displayed. Below this
	 * number, it will not be displayed.
	 *
	 * @param int $amount Set to 0 to disable hiding.
	 * @return Application_Countries_ButtonBar
	 */
	public function setDisplayThreshold(int $amount): Application_Countries_ButtonBar
	{
		/* ... */
	}


	/**
	 * Whether to display the label next to the buttons.
	 * Defaults to false.
	 *
	 * @param bool $enable
	 * @return Application_Countries_ButtonBar
	 */
	public function enableLabel(bool $enable = true): Application_Countries_ButtonBar
	{
		/* ... */
	}


	/**
	 * Sets the label of the selector, which is shown beside the buttons.
	 *
	 * @param string|number|UI_Renderable_Interface $label
	 * @return Application_Countries_ButtonBar
	 * @throws UI_Exception
	 */
	public function setLabel($label): Application_Countries_ButtonBar
	{
		/* ... */
	}


	/**
	 * Sets whether the storage of the selected country is enabled.
	 *
	 * If turned off, the country will not be stored in the user's
	 * settings and must be selected manually via {@see self::selectCountry()}
	 * or the request variable {@see self::REQUEST_PARAM_SELECT_COUNTRY}.
	 *
	 * @param bool $enabled
	 * @return self
	 */
	public function setStorageEnabled(bool $enabled): self
	{
		/* ... */
	}


	public function isStorageEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the button bar ID, as specified on its creation.
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the country filter settings to be
	 * able to customize the selection as needed.
	 *
	 * @return Application_Countries_FilterCriteria
	 */
	public function getFilters(): Application_Countries_FilterCriteria
	{
		/* ... */
	}


	/**
	 * Retrieves the selected country ID.
	 * @return int
	 */
	public function getCountryID(): int
	{
		/* ... */
	}


	/**
	 * Retrieves the selected country.
	 * @return Application_Countries_Country|NULL
	 */
	public function getCountry(): ?Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Checks whether the specified country is the currently selected one.
	 *
	 * @param Application_Countries_Country $country
	 * @return boolean
	 */
	public function isSelected(Application_Countries_Country $country): bool
	{
		/* ... */
	}


	public function getIDFromUser(): ?int
	{
		/* ... */
	}


	public function getIDFromRequest(): ?int
	{
		/* ... */
	}


	/**
	 * Retrieves all countries selectable in the button bar.
	 *
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the URL that can be used to select the specified country.
	 *
	 * @param Application_Countries_Country $country
	 * @throws Application_Exception
	 * @return string
	 *
	 * @see Application_Countries_ButtonBar::ERROR_INVALID_COUNTRY_FOR_LINK
	 */
	public function getCountryLink(Application_Countries_Country $country): string
	{
		/* ... */
	}


	/**
	 * Checks whether the specified country is selectable
	 * in the button bar.
	 *
	 * @param Application_Countries_Country $country
	 * @return bool
	 */
	public function isSelectable(Application_Countries_Country $country): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the IDs of all countries selectable in the button bar.
	 *
	 * @return int[]
	 */
	public function getCountryIDs(): array
	{
		/* ... */
	}


	public function isLabelEnabled(): bool
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	/**
	 * Manually selects the country, overriding the user's settings
	 * and the current request.
	 *
	 * @param Application_Countries_Country $country
	 * @return $this
	 */
	public function selectCountry(Application_Countries_Country $country): self
	{
		/* ... */
	}


	/**
	 * Saves the currently selected country in the user's settings.
	 *
	 * NOTE: Only used when storage is enabled. This is called
	 * automatically when the bar is rendered.
	 *
	 * @return $this
	 */
	public function save(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Countries.php`

```php
namespace ;

use AppLocalize\Localization\Countries\CountryCollection as CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface as CountryInterface;
use AppLocalize\Localization\Country\CountryGB as CountryGB;
use Application\Countries\Admin\MainAdminURLs as MainAdminURLs;
use Application\Countries\CountriesCollection as CountriesCollection;
use Application\Countries\CountryException as CountryException;
use Application\Countries\CountrySettingsManager as CountrySettingsManager;
use Application\Countries\FilterSettings as FilterSettings;
use Application\Languages\Language as Language;

/**
 * Country management class, used to retrieve information
 * about available countries and add or delete individual
 * countries.
 *
 * @package Maileditor
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method Application_Countries_Country getByID(int $country_id)
 * @method Application_Countries_FilterCriteria getFilterCriteria()
 * @method Application_Countries_Country|NULL getByRequest()
 * @method Application_Countries_Country createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class Application_Countries extends DBHelper_BaseCollection
{
	public const ERROR_UNKNOWN_ISO_CODE = 21901;
	public const ERROR_INVALID_COUNTRY_ID = 21902;
	public const ERROR_CANNOT_USE_ALIAS_FOR_CREATION = 21903;
	public const ERROR_ISO_ALREADY_EXISTS = 21904;
	public const PRIMARY_NAME = 'country_id';
	public const TABLE_NAME = 'countries';
	public const REQUEST_PARAM_ID = self::PRIMARY_NAME;
	public const RECORD_TYPE_NAME = 'country';

	public function getRecordDefaultSortKey(): string
	{
		/* ... */
	}


	public function getRecordRequestPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordClassName(): string
	{
		/* ... */
	}


	public function getRecordFiltersClassName(): string
	{
		/* ... */
	}


	public function getRecordFilterSettingsClassName(): string
	{
		/* ... */
	}


	public function getRecordSearchableColumns(): array
	{
		/* ... */
	}


	public function getRecordPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordTypeName(): string
	{
		/* ... */
	}


	public function getRecordTableName(): string
	{
		/* ... */
	}


	/**
	 * Returns the global instance of the country manager,
	 * creating it as needed.
	 *
	 * @return Application_Countries
	 */
	public static function getInstance(): Application_Countries
	{
		/* ... */
	}


	/**
	 * Retrieves a country by its ID.
	 *
	 * @param integer $country_id
	 * @return Application_Countries_Country
	 */
	public function getCountryByID(int $country_id): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Resolves the country from the specified subject, which can be
	 * a country ID, ISO code, or a country instance.
	 *
	 * @param Application_Countries_Country|CountryInterface|int|string|mixed $subject
	 * @return Application_Countries_Country
	 * @throws CountryException
	 */
	public function resolveCountry(mixed $subject): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Retrieves a country by its localization country instance.
	 *
	 * @param CountryInterface $country
	 * @return Application_Countries_Country
	 */
	public function getByLocalizationCountry(CountryInterface $country): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Retrieves the country independent meta-entry.
	 * @return Application_Countries_Country
	 */
	public function getInvariantCountry(): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Creates and adds a country selection element to the specified form,
	 * and returns the created form element.
	 *
	 * @param UI_Form $form
	 * @param string|null $fieldName
	 * @param string|null $fieldLabel
	 * @param bool $required
	 * @param bool $pleaseSelect
	 * @param bool $withInvariant
	 * @return HTML_QuickForm2_Element_Select
	 * @throws HTML_QuickForm2_Exception
	 * @throws HTML_QuickForm2_InvalidArgumentException
	 * @throws UI_Exception
	 */
	public function injectCountrySelector(
		UI_Form $form,
		?string $fieldName = null,
		?string $fieldLabel = null,
		bool $required = true,
		bool $pleaseSelect = true,
		bool $withInvariant = true,
	): HTML_QuickForm2_Element_Select
	{
		/* ... */
	}


	/**
	 * @return Application_Countries_Country[]
	 */
	public function getAll(bool $includeInvariant = true): array
	{
		/* ... */
	}


	public function getCollection(): CountriesCollection
	{
		/* ... */
	}


	/**
	 * @param bool $includeInvariant
	 * @return int[]
	 */
	public function getIDs(bool $includeInvariant = true): array
	{
		/* ... */
	}


	public function getByLanguage(Language $language): array
	{
		/* ... */
	}


	public function injectJS(): void
	{
		/* ... */
	}


	/**
	 * Checks whether the two-letter country ISO code
	 * exists. In the case of the UK, both "uk" and "gb"
	 * are supported.
	 *
	 * @param string $iso
	 * @return bool
	 */
	public function isoExists(string $iso): bool
	{
		/* ... */
	}


	/**
	 * Gets a list of all country ISO codes supported by
	 * the country management.
	 *
	 * @param bool $includeInvariant
	 * @return string[]
	 */
	public function getSupportedISOs(bool $includeInvariant = true): array
	{
		/* ... */
	}


	public function getCollectionLabel(): string
	{
		/* ... */
	}


	public function getRecordLabel(): string
	{
		/* ... */
	}


	public function getRecordProperties(): array
	{
		/* ... */
	}


	/**
	 * Retrieves a country by its two-letter ISO code, e.g. "de".
	 *
	 * @param string $iso
	 * @throws CountryException
	 * @return Application_Countries_Country
	 *
	 * @see Application_Countries::ERROR_UNKNOWN_ISO_CODE
	 */
	public function getByISO(string $iso): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * @param string $code The locale code, e.g. "de_DE"
	 * @return Application_Countries_Country
	 * @throws CountryException
	 */
	public function getByLocaleCode(string $code): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Parses a locale code to access information on its
	 * constituent parts.
	 *
	 * @param string $code The locale code, e.g. "de_DE"
	 * @return Application_Countries_LocaleCode
	 * @throws CountryException
	 */
	public function parseLocaleCode(string $code): Application_Countries_LocaleCode
	{
		/* ... */
	}


	/**
	 * The navigator can be used to create a navigation
	 * element to switch between countries.
	 *
	 * @return Application_Countries_Navigator
	 */
	public function createCountryNavigator(): Application_Countries_Navigator
	{
		/* ... */
	}


	/**
	 * For a list of string or integer IDs, returns
	 * all matching countries by their ID.
	 *
	 * @param string[]|int[] $ids
	 * @throws CountryException
	 * @return Application_Countries_Country[]
	 */
	public function getInstancesByIDs(array $ids): array
	{
		/* ... */
	}


	/**
	 * Creates a country selector helper instance: this can
	 * be used to add a country select element to the target
	 * formable, with a number of customization options.
	 *
	 * @param Application_Formable $formable
	 * @return Application_Countries_Selector
	 */
	public static function createSelector(Application_Formable $formable): Application_Countries_Selector
	{
		/* ... */
	}


	/**
	 * Creates a button bar to select a country,
	 * whose selection is persisted in the user
	 * settings.
	 *
	 * @param string $id A freeform ID to tie the country selection to: This is used to namespace the setting under which the country is stored.
	 * @param string $baseURL The base URL to use, to which the country selection parameter will be appended.
	 * @param int[] $limitToCountries List of country IDs to limit the selection to.
	 * @return Application_Countries_ButtonBar
	 */
	public static function createButtonBar(
		string $id,
		string $baseURL,
		array $limitToCountries = [],
	): Application_Countries_ButtonBar
	{
		/* ... */
	}


	public function createInvariantCountry(): Application_Countries_Country
	{
		/* ... */
	}


	public function createNewCountry(string $iso, string $label): Application_Countries_Country
	{
		/* ... */
	}


	public function convertISO(string $iso): string
	{
		/* ... */
	}


	public function isValidISO(string $iso): bool
	{
		/* ... */
	}


	public function adminURL(): MainAdminURLs
	{
		/* ... */
	}


	public function createSettingsManager(
		Application_Formable $formable,
		?Application_Countries_Country $country,
	): CountrySettingsManager
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/CountriesCollection.php`

```php
namespace Application\Countries;

use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\AppFactory as AppFactory;
use Application_Countries as Application_Countries;
use Application_Countries_Country as Application_Countries_Country;

/**
 * Utility class for working with collections of countries,
 * with helper methods to easily access the countries.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CountriesCollection
{
	public const ERROR_CANNOT_GET_FIRST_COUNTRY = 105201;
	public const ERROR_CANNOT_GET_BY_ISO = 105202;
	public const ERROR_CANNOT_GET_BY_ID = 105203;

	public static function create(array $countries = []): CountriesCollection
	{
		/* ... */
	}


	/**
	 * @param Application_Countries_Country[] $countries
	 * @return $this
	 */
	public function addCountries(array $countries): self
	{
		/* ... */
	}


	/**
	 * @param Application_Countries_Country $country
	 * @return $this
	 */
	public function addCountry(Application_Countries_Country $country): self
	{
		/* ... */
	}


	public static function getAPIMethodsFolder(): FolderInfo
	{
		/* ... */
	}


	public function hasCountries(): bool
	{
		/* ... */
	}


	public function countCountries(): int
	{
		/* ... */
	}


	/**
	 * @param Application_Countries_Country $country
	 * @return $this
	 */
	public function removeCountry(Application_Countries_Country $country): self
	{
		/* ... */
	}


	/**
	 * @param Application_Countries_Country[] $countries
	 * @return $this
	 */
	public function removeCountries(array $countries): self
	{
		/* ... */
	}


	/**
	 * @param int[] $ids
	 * @return $this
	 */
	public function addIDs(array $ids): self
	{
		/* ... */
	}


	public function addID(int $id): self
	{
		/* ... */
	}


	/**
	 * @param string[] $ISOs
	 * @return $this
	 */
	public function addISOs(array $ISOs): self
	{
		/* ... */
	}


	public function addISO(string $iso): self
	{
		/* ... */
	}


	/**
	 * @return int[]
	 */
	public function getIDs(): array
	{
		/* ... */
	}


	public function hasID(int $id): bool
	{
		/* ... */
	}


	public function hasISO(string $iso): bool
	{
		/* ... */
	}


	public function getFirst(): Application_Countries_Country
	{
		/* ... */
	}


	public function getByISO(string $ISO): Application_Countries_Country
	{
		/* ... */
	}


	public function getByID(int $id): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getISOs(): array
	{
		/* ... */
	}


	/**
	 * @return Application_Countries_Country[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	public function excludeInvariant(bool $exclude = true): self
	{
		/* ... */
	}


	/**
	 * @return Application_Countries_Country[]
	 */
	public function getSortedByISO(): array
	{
		/* ... */
	}


	/**
	 * @return Application_Countries_Country[]
	 */
	public function getSortedByLabel(): array
	{
		/* ... */
	}


	public function hasInvariant(): bool
	{
		/* ... */
	}


	public function hasCountry(Application_Countries_Country $country): bool
	{
		/* ... */
	}


	/**
	 * Checks whether the collection contains the specified country ID.
	 * @param int $id
	 * @return bool
	 */
	public function idExists(int $id): bool
	{
		/* ... */
	}


	/**
	 * Attempts to find a country available in the collection
	 * from the current request, using the standard request
	 * parameter {@see Application_Countries::REQUEST_PARAM_ID}.
	 *
	 * @return Application_Countries_Country|null
	 */
	public function getByRequest(): ?Application_Countries_Country
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Country.php`

```php
namespace ;

use AppLocalize\Localization as Localization;
use AppLocalize\Localization\Countries\CountryCollection as CountryCollection;
use AppLocalize\Localization\Countries\CountryInterface as CountryInterface;
use AppLocalize\Localization\Country\CountryGB as CountryGB;
use AppLocalize\Localization\Currencies\CountryCurrencyInterface as CountryCurrencyInterface;
use Application\AppFactory as AppFactory;
use Application\Countries\Admin\CountryAdminURLs as CountryAdminURLs;
use Application\Countries\Rights\CountryScreenRights as CountryScreenRights;
use Application\Languages\Language as Language;
use Application\Languages\LanguageException as LanguageException;

/**
 * Country data type; handles an individual country and its information.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_Country extends DBHelper_BaseRecord
{
	/** @deprecated Use the ISO instead, which is more reliable: {@see self::COUNTRY_INDEPENDENT_ISO} */
	public const COUNTRY_INDEPENDENT_ID = 9999;
	public const COUNTRY_INDEPENDENT_ISO = 'zz';
	public const COL_ISO = 'iso';
	public const COL_LABEL = 'label';

	public function getLabel(): string
	{
		/* ... */
	}


	public function getLocalizedLabel(): string
	{
		/* ... */
	}


	public function getIconLabel(bool $linked = false, bool $localized = false): string
	{
		/* ... */
	}


	/**
	 * @return Application_Countries_Country_Icon
	 */
	public function getIcon(): Application_Countries_Country_Icon
	{
		/* ... */
	}


	/**
	 * The lowercase two-letter country iso string, e.g. "us", "de".
	 *
	 * @param boolean $emptyIfInvariant Whether to return an empty string when this is the country independent entry.
	 * @return string
	 */
	public function getISO(bool $emptyIfInvariant = false): string
	{
		/* ... */
	}


	/**
	 * Retrieves the Alpha 2 ISO code for the country.
	 * @return string
	 * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
	 */
	public function getAlpha2(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the lowercase two-letter language code for
	 * the country. Note that this only returns the main
	 * language used in the country if it has several
	 * official ones.
	 *
	 * @return string
	 */
	public function getLanguageCode(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the full locale code with country and language codes.
	 *
	 * @return string The locale code, e.g. "en_US".
	 */
	public function getLocaleCode(): string
	{
		/* ... */
	}


	public function getLocale(): Application\Locales\Locale
	{
		/* ... */
	}


	public function getLocalizationLocale(): Localization\Locales\LocaleInterface
	{
		/* ... */
	}


	/**
	 * Retrieves the human-readable label of the country's
	 * main language (translated to the current app locale).
	 *
	 * @throws LanguageException
	 * @return string
	 */
	public function getLanguageLabel(): string
	{
		/* ... */
	}


	public function getLanguage(): Language
	{
		/* ... */
	}


	/**
	 * The currency used in this country.
	 * @return CountryCurrencyInterface
	 */
	public function getCurrency(): CountryCurrencyInterface
	{
		/* ... */
	}


	/**
	 * Whether this is the country-independent country.
	 * Alias for the {@link isInvariant()} method.
	 *
	 * @return boolean
	 */
	public function isInvariant(): bool
	{
		/* ... */
	}


	/**
	 * Whether this is the country-independent country.
	 * @return boolean
	 */
	public function isCountryIndependent(): bool
	{
		/* ... */
	}


	public function adminURL(): CountryAdminURLs
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Country/Icon.php`

```php
namespace ;

class Application_Countries_Country_Icon extends UI_Renderable
{
}


```
###  Path: `/src/classes/Application/Countries/Country/Icon.php`

```php
namespace ;

class Application_Countries_Country_Icon extends UI_Renderable
{
}


```
###  Path: `/src/classes/Application/Countries/CountryException.php`

```php
namespace Application\Countries;

use Application_Exception as Application_Exception;

class CountryException extends Application_Exception
{
}


```
###  Path: `/src/classes/Application/Countries/CountrySettingsManager.php`

```php
namespace Application\Countries;

use Application\AppFactory as AppFactory;
use Application_Countries_Country as Application_Countries_Country;
use Application_Formable as Application_Formable;
use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting as Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable as Application_Interfaces_Formable;
use Closure as Closure;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use HTML_QuickForm2_Node as HTML_QuickForm2_Node;
use UI\CSSClasses as CSSClasses;

class CountrySettingsManager extends Application_Formable_RecordSettings_Extended
{
	public const SETTING_LABEL = 'label';

	public function getDefaultSettingName(): string
	{
		/* ... */
	}


	public function isUserAllowedEditing(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/FilterCriteria.php`

```php
namespace ;

/**
 * Filter criteria handler for countries.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method Application_Countries_Country[] getItemsObjects()
 */
class Application_Countries_FilterCriteria extends DBHelper_BaseFilterCriteria
{
	/**
	 * Limits the list to the specified country IDs.
	 * @param integer[] $ids
	 * @return Application_Countries_FilterCriteria
	 */
	public function selectCountryIDs(array $ids): Application_Countries_FilterCriteria
	{
		/* ... */
	}


	/**
	 * Limits the list to the specified country by its ID.
	 * @param integer $country_id
	 * @return Application_Countries_FilterCriteria
	 */
	public function selectCountryID(int $country_id): Application_Countries_FilterCriteria
	{
		/* ... */
	}


	/**
	 * Excludes the invariant country from the results.
	 *
	 * @param bool $exclude
	 * @return Application_Countries_FilterCriteria
	 */
	public function excludeInvariant(bool $exclude = true): Application_Countries_FilterCriteria
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/FilterSettings.php`

```php
namespace Application\Countries;

use DBHelper_BaseFilterSettings as DBHelper_BaseFilterSettings;

class FilterSettings extends DBHelper_BaseFilterSettings
{
	public const SETTING_SEARCH = 'search';
}


```
###  Path: `/src/classes/Application/Countries/LocaleCode.php`

```php
namespace ;

use AppUtils\ConvertHelper as ConvertHelper;
use Application\Countries\CountryException as CountryException;

class Application_Countries_LocaleCode
{
	public const ERROR_CODE_CANNOT_BE_PARSED = 87701;

	public function getCode(): string
	{
		/* ... */
	}


	public function getCountryISO(): string
	{
		/* ... */
	}


	public function getLanguageCode(): string
	{
		/* ... */
	}


	public function getCountry(): Application_Countries_Country
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Navigator.php`

```php
namespace ;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

/**
 * Utility class used to render a country navigation element,
 * using a button bar to easily select a language.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Countries_Navigator extends UI_Renderable
{
	public const REQUEST_PARAM_COUNTRY_ID = Application_Countries::PRIMARY_NAME;

	public function getCollection(): Application_Countries
	{
		/* ... */
	}


	public function getFilterCriteria(): Application_Countries_FilterCriteria
	{
		/* ... */
	}


	/**
	 * @return Application_Countries_Country[]
	 */
	public function getCountries(): array
	{
		/* ... */
	}


	public function setAutoSelect(bool $enabled = true): self
	{
		/* ... */
	}


	public function setActiveCountry(Application_Countries_Country $country): self
	{
		/* ... */
	}


	/**
	 * Allows overriding the dispatcher file used in the generated
	 * URL. By default, this is empty to serve everything via the
	 * current executing script (typically `index.php`).
	 *
	 * @param string $dispatcher
	 * @return $this
	 */
	public function setURLDispatcher(string $dispatcher): self
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @param string|int|float|bool|StringableInterface|NULL $value
	 * @return $this
	 */
	public function setURLParam(string $name, string|int|float|bool|StringableInterface|null $value): self
	{
		/* ... */
	}


	public function setURLParamsByScreen(AdminScreenInterface $area): self
	{
		/* ... */
	}


	public function setURLParamByRequest(string $varName): self
	{
		/* ... */
	}


	public function setURLParamMode(): self
	{
		/* ... */
	}


	public function setURLParamSubmode(): self
	{
		/* ... */
	}


	public function setURLParamAction(): self
	{
		/* ... */
	}


	/**
	 * @param array<string,string|int|float|bool|StringableInterface|NULL> $params
	 * @return $this
	 */
	public function setURLParams(array $params): self
	{
		/* ... */
	}


	public function getActiveCountry(): Application_Countries_Country
	{
		/* ... */
	}


	/**
	 * Enables storing the active country ID to restore it
	 * automatically if no country is specifically selected
	 * in the request parameters.
	 *
	 * @param string|NULL $storageName Used to identify the navigator: all instances using the same name share the stored country setting. Set to NULL to disable storage again.
	 * @return $this
	 */
	public function enableCountryStorage(?string $storageName): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Countries/Selector.php`

```php
namespace ;

use Application\Countries\CountriesCollection as CountriesCollection;

/**
 * Form countries selector element used to create and
 * handle a select element to choose countries.
 *
 * @package Application
 * @subpackage Countries
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method Application_Countries_Selector setName($name)
 * @method Application_Countries_FilterCriteria getFilters()
 * @property Application_Countries $collection
 * @property Application_Countries_FilterCriteria $filters
 */
class Application_Countries_Selector extends Application_Formable_RecordSelector
{
	public function excludeInvariant(): Application_Countries_Selector
	{
		/* ... */
	}


	public function createCollection(): Application_Countries
	{
		/* ... */
	}


	public function useCustomCollection(CountriesCollection $collection): self
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 29.31 KB
- **Lines**: 1477
File: `modules/countries/architecture-core.md`
