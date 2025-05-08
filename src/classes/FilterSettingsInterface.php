<?php
/**
 * @package Application
 * @subpackage Filtering
 */

declare(strict_types=1);

namespace Application;

use Application\FilterSettings\SettingDef;
use Application\Interfaces\FilterCriteriaInterface;
use Application\Interfaces\HiddenVariablesInterface;
use Application_Countries_Country;
use Application_Exception;
use Application_FilterSettings;
use Application_Interfaces_Loggable;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper_Exception;
use AppUtils\Interfaces\StringableInterface;
use HTML_QuickForm2_Container;
use HTML_QuickForm2_Element;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Multiselect;
use HTML_QuickForm2_Element_Select;
use HTML_QuickForm2_Element_Switch;
use HTML_QuickForm2_InvalidArgumentException;
use JsonException;
use UI_Renderable_Interface;
use UI_Themes_Exception;

/**
 * Base class for custom filter setting implementations. This can
 * be used to create a settings form intended to configure a filter
 * criteria instance. The storage of the settings is handled
 * automatically on a per-user basis.
 *
 * Usage:
 *
 * - Extend this class, and implement the abstract methods
 * - Instantiate an instance of the class
 * - Add it to a sidebar using the {@link UI_Page_Sidebar::addFilterSettings()} method
 * - Configure your filter criteria using the {@link Application_FilterSettings::configureFilters()} method
 *
 * @package Application
 * @subpackage Filtering
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
interface FilterSettingsInterface
    extends
    Application_Interfaces_Loggable,
    UI_Renderable_Interface,
    HiddenVariablesInterface
{
    /**
     * Sets the ID to use for saving settings (on a per-user basis).
     * Using the same ID for different instances means they will
     * share stored settings.
     *
     * NOTE: This should be called before the form is rendered, ideally
     * before doing anything, so the settings do not get loaded several
     * times. Settings use lazy loading, but this way the chance is
     * minimized.
     *
     * @param string $id
     * @return $this
     */
    public function setID(string $id): self;

    /**
     * @param string|SettingDef $name
     * @return SettingDef
     * @throws Application_Exception
     */
    public function getSearchSetting($name = null): SettingDef;

    /**
     * Configures the provided filter criteria instance with the
     * current filtering settings.
     *
     * @param FilterCriteriaInterface $filters
     */
    public function configureFilters(FilterCriteriaInterface $filters): void;

    /**
     * Retrieves all settings as an associative array with
     * setting name > value pairs.
     *
     * @return array<string,string|number|array<mixed>|bool|NULL>
     */
    public function getSettings(): array;

    /**
     * Retrieves a single setting's value. If no value
     * has been explicitly set, returns the default value.
     *
     * @param string $name
     * @return string|array<mixed>|number|bool|NULL
     */
    public function getSetting(string $name);

    /**
     * @param string $name
     * @return array<mixed>
     * @deprecated Use {@see getSettingArray()} instead.
     */
    public function getArraySetting(string $name): array;

    public function getSettingArray(string $name): array;

    public function getSettingString(string $name): string;

    public function getSettingBool(string $name): bool;

    public function getSettingInt(string $name): int;

    /**
     * @param string $name
     * @param string|number|array<mixed>|bool|NULL $value
     * @return $this
     * @throws JsonException
     */
    public function setSetting(string $name, $value): self;

    /**
     * @param array<string,mixed>|null $settings
     * @return $this
     */
    public function setSettings(?array $settings): self;

    public function setSettingEnabled(string $name, bool $enabled): self;

    public function isSettingEnabled(string $name): bool;

    /**
     * Adds a "More settings..." button in the form, and hides all
     * elements added after it.
     *
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_InputText
     * @throws Application_Exception
     * @throws BaseClassHelperException
     * @throws UI_Themes_Exception
     * @throws FileHelper_Exception
     */
    public function addMore(HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Adds a multiselect element.
     * @param string $setting
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Multiselect
     */
    public function addMultiselect(string $setting, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Multiselect;

    /**
     * Adds a regular select element.
     * @param string $setting
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Select
     * @deprecated
     */
    public function addSelect(string $setting, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Select;

    /**
     * @param array<string,string|number|StringableInterface|NULL> $vars
     * @return $this
     */
    public function addHiddenVars(array $vars): self;

    /**
     * Adds a hidden var to the filter form.
     * @param string $name
     * @param string|number|StringableInterface|NULL $value
     * @return $this
     */
    public function addHiddenVar(string $name, $value): self;

    public function addElementDateSearch(string $setting, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Adds a country selector element.
     *
     * @param string $setting The name of the setting to which this should be tied
     * @param HTML_QuickForm2_Container|NULL $container
     * @param array $options
     * @return HTML_QuickForm2_Element_Select
     */
    public function addElementCountry(string $setting, ?HTML_QuickForm2_Container $container = null, array $options = array()): HTML_QuickForm2_Element_Select;

    /**
     * Creates and adds an element to the container for
     * a setting, which automatically configures it, so
     * it can be correctly registered clientside as well.
     *
     * @param string $setting
     * @param string $type
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element
     */
    public function addElement(string $setting, string $type, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element;

    /**
     * Adds a select element for the specified filter setting.
     *
     * @param string $setting
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Select
     * @throws BaseClassHelperException
     */
    public function addElementSelect(string $setting, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Select;

    /**
     * @param string $setting
     * @param HTML_QuickForm2_Container|null $container
     * @return HTML_QuickForm2_Element_InputText
     * @throws BaseClassHelperException
     */
    public function addElementText(string $setting, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_InputText;

    /**
     * Adds a switch (boolean) element.
     *
     * @param string $setting
     * @param HTML_QuickForm2_Container|NULL $container
     * @return HTML_QuickForm2_Element_Switch
     * @throws BaseClassHelperException
     */
    public function addElementSwitch(string $setting, ?HTML_QuickForm2_Container $container = null): HTML_QuickForm2_Element_Switch;

    /**
     * Adds a previously created form element that has not been
     * created with the {@link addElement()} method, and configures
     * it to work with the filters.
     *
     * @param HTML_QuickForm2_Element $element
     * @return $this
     * @throws Application_Exception
     * @throws HTML_QuickForm2_InvalidArgumentException
     */
    public function addCustomElement(HTML_QuickForm2_Element $element): self;

    public function getJSID(): string;

    /**
     * Retrieves the name of the clientside javascript variable in
     * which the client object handling these filter settings will
     * be available under.
     *
     * @return string
     */
    public function getJSName(): string;

    /**
     * Checks whether the filter settings are active (if any of
     * the registered settings are not the default value).
     *
     * @return boolean
     */
    public function isActive(): bool;

    /**
     * Resets all filter settings to the default settings.
     */
    public function reset(): void;

    /**
     * Checks whether a setting with the specified name exists
     * within the settings configuration.
     *
     * @param string $name
     * @return boolean
     */
    public function hasSetting(string $name): bool;

    /**
     * Attempts to retrieve the country selected in a country
     * setting. Must have been added using {@link addElementCountry()}.
     *
     * @param string $name The name of the setting
     * @return Application_Countries_Country|NULL
     */
    public function getSettingCountry(string $name): ?Application_Countries_Country;

    public function getJSSubmitHandler(): string;

    /**
     * @return string
     */
    public function getID(): string;

    public function getLogIdentifier(): string;
}