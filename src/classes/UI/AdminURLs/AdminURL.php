<?php
/**
 * @package User Interface
 */

declare(strict_types=1);

namespace Maileditor\UI\AdminURLs;

use Application\AppFactory;
use Application_Admin_ScreenInterface;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\Traits\RenderableTrait;

/**
 * @package User Interface
 */
class AdminURL implements RenderableInterface
{
    use RenderableTrait;

    /**
     * @var array<string,string>
     */
    private array $params;

    private string $dispatcher = '';

    public function __construct(array $params=array())
    {
        $this->import($params);
    }

    public function remove(string $name) : self
    {
        if(isset($this->params[$name])) {
            unset($this->params[$name]);
        }

        return $this;
    }

    public function import(array $params) : self
    {
        foreach($params as $param => $value) {
            $this->params[$param] = $value;
        }

        return $this;
    }

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

    public function int(string $name, int $value) : self
    {
        return $this->string($name, (string)$value);
    }

    public function float(string $name, float $value) : self
    {
        return $this->string($name, (string)$value);
    }

    public function string(string $name, string $value) : self
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function bool(string $name, bool $value, bool $yesNo=false) : self
    {
        return $this->string($name, bool2string($value, $yesNo));
    }

    public function arrayJSON(string $name, array $data) : self
    {
        return $this->string($name, JSONConverter::var2json($data));
    }

    public function area(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE, $name);
    }

    public function mode(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_MODE, $name);
    }

    public function submode(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE, $name);
    }

    public function action(string $name) : self
    {
        return $this->string(Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION, $name);
    }

    public function setDispatcher(string $dispatcher) : self
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function get() : string
    {
        return AppFactory::createRequest()
            ->buildURL($this->params, $this->dispatcher);
    }

    public function render(): string
    {
        return $this->get();
    }
}
