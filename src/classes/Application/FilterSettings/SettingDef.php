<?php
/**
 * @package Application
 * @subpackage FilterSettings
 */

declare(strict_types=1);

namespace Application\FilterSettings;

use Application_FilterCriteria;
use Application_FilterSettings;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\TypeFilter\BaseTypeFilter;
use AppUtils\TypeFilter\LenientType;
use UI_Exception;

/**
 * Stores the configuration for individual filter settings.
 *
 * @package Application
 * @subpackage FilterSettings
 */
class SettingDef
{
    public const ERROR_NO_INJECT_CALLBACK = 149201;

    private string $name;
    private string $label;
    private string $id;
    private bool $enabled = true;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var callable|null
     */
    private $injectCallback;

    /**
     * @var callable|null
     */
    private $configureCallback;
    protected Application_FilterSettings $settings;
    private bool $configured = false;
    private bool $injected = false;

    /**
     * @param Application_FilterSettings $settings
     * @param string $name
     * @param string|number|StringableInterface|NULL $label
     * @param mixed $default
     * @throws UI_Exception
     */
    public function __construct(Application_FilterSettings $settings, string $name, $label, $default=null)
    {
        $this->name = $name;
        $this->label = toString($label);
        $this->default = $default;
        $this->settings = $settings;
        $this->id = $settings->getJSID().'-'.$name;
    }

    public function setEnabled(bool $enabled) : self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function getElementID() : string
    {
        return $this->id;
    }

    public function setInjectCallback(?callable $callback): self
    {
        $this->injectCallback = $callback;
        return $this;
    }

    public function setConfigureCallback(?callable $callback): self
    {
        $this->configureCallback = $callback;
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return $this
     * @throws UI_Exception
     */
    public function inject() : self
    {
        if($this->injectCallback !== null) {
            call_user_func($this->injectCallback, $this);
            $this->injected = true;
            return $this;
        }

        return $this;
    }

    public function isInjected() : bool
    {
        return $this->injected;
    }

    /**
     * @return $this
     */
    public function configure(Application_FilterCriteria $filterCriteria) : self
    {
        if($this->configured) {
            return $this;
        }

        $this->configured = true;

        if($this->configureCallback !== null) {
            call_user_func($this->configureCallback, $this, $filterCriteria);
        }

        return $this;
    }

    public function getValue() : LenientType
    {
        return BaseTypeFilter::createLenient($this->settings->getSetting($this->name));
    }
}
