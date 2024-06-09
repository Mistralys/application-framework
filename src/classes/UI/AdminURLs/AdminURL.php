<?php
/**
 * @package User Interface
 */

declare(strict_types=1);

namespace UI\AdminURLs;

use Application;
use Application\AppFactory;
use Application_Admin_ScreenInterface;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\Traits\RenderableTrait;

/**
 * Helper class used to build admin screen URLs.
 *
 * To create an instance, use the {@see \UI::adminURL()} method,
 * or the {@see AdminURL::create()} method.
 *
 * @package User Interface
 * @see AdminURLInterface
 */
class AdminURL implements AdminURLInterface
{
    use RenderableTrait;

    /**
     * @var array<string,string>
     */
    private array $params;

    private string $dispatcher = '';

    /**
     * @param array<string,string|int|float|bool|null> $params
     */
    public function __construct(array $params=array())
    {
        $this->import($params);
    }

    public static function create(array $params=array()) : self
    {
        return new self($params);
    }

    /**
     * Removes a parameter if it exists.
     * @param string $name
     * @return $this
     */
    public function remove(string $name) : self
    {
        if(isset($this->params[$name])) {
            unset($this->params[$name]);
        }

        return $this;
    }

    /**
     * Imports an array of parameter values.
     * @param array<string,string|int|float|bool|null> $params
     * @return $this
     */
    public function import(array $params) : self
    {
        foreach($params as $param => $value) {
            $this->auto($param, $value);
        }

        return $this;
    }

    /**
     * Adds a parameter, automatically determining its type.
     *
     * @param string $name
     * @param string|int|float|bool|null $value
     * @return $this
     */
    public function auto(string $name, $value) : self
    {
        if(is_bool($value)) {
            return $this->bool($name, $value);
        }

        if(is_string($value) || is_int($value) || is_float($value)) {
            return $this->string($name, (string)$value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param int $value
     * @return $this
     */
    public function int(string $name, int $value) : self
    {
        return $this->string($name, (string)$value);
    }

    /**
     * @param string $name
     * @param float $value
     * @return $this
     */
    public function float(string $name, float $value) : self
    {
        return $this->string($name, (string)$value);
    }

    /**
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function string(string $name, ?string $value) : self
    {
        if(!empty($value)) {
            $this->params[$name] = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param bool $value
     * @param bool $yesNo
     * @return $this
     */
    public function bool(string $name, bool $value, bool $yesNo=false) : self
    {
        return $this->string($name, bool2string($value, $yesNo));
    }

    /**
     * Adds an array as a JSON string.
     * @param string $name
     * @param array<int|string,string|int|float|bool|NULL|array> $data
     * @return $this
     * @throws JSONConverterException
     */
    public function arrayJSON(string $name, array $data) : self
    {
        return $this->string($name, JSONConverter::var2json($data));
    }

    /**
     * Adds an admin area screen parameter.
     * @param string $name
     * @return $this
     */
    public function area(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE, $name);
    }

    /**
     * Adds an admin mode screen parameter.
     * @param string $name
     * @return $this
     */
    public function mode(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_MODE, $name);
    }

    /**
     * Adds an admin submode screen parameter.
     * @param string $name
     * @return $this
     */
    public function submode(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE, $name);
    }

    /**
     * Adds an admin action screen parameter.
     * @param string $name
     * @return $this
     */
    public function action(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION, $name);
    }

    /**
     * Add the parameter to enable the application simulation mode.
     * @param bool $enabled
     * @return $this
     */
    public function simulation(bool $enabled=true) : self
    {
        return $this->bool(Application::REQUEST_VAR_SIMULATION, $enabled, true);
    }

    /**
     * Sets the name of the dispatcher script to use in the URL.
     * @param string $dispatcher
     * @return $this
     */
    public function dispatcher(string $dispatcher) : self
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @return string The generated URL with all parameters.
     */
    public function get() : string
    {
        return AppFactory::createRequest()
            ->buildURL($this->params, $this->dispatcher);
    }

    public function render(): string
    {
        return $this->get();
    }

    /**
     * @return array<string,string>
     */
    public function getParams() : array
    {
        return $this->params;
    }
}
