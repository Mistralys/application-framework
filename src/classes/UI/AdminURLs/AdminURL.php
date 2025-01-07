<?php
/**
 * @package User Interface
 * @subpackage Admin URLs
 */

declare(strict_types=1);

namespace UI\AdminURLs;

use Application;
use Application\AppFactory;
use Application\Interfaces\Admin\AdminScreenInterface;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\FileHelper;
use AppUtils\Traits\RenderableTrait;
use AppUtils\URLInfo;
use TestDriver\ClassFactory;
use function AppUtils\parseURL;

/**
 * Helper class used to build admin screen URLs.
 *
 * To create an instance, use the {@see \UI::adminURL()} method,
 * or the {@see AdminURL::create()} method.
 *
 * @package User Interface
 * @subpackage Admin URLs
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

    public function getDispatcher() : string
    {
        return $this->dispatcher;
    }

    public static function create(array $params=array()) : self
    {
        return new self($params);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     * @return $this
     */
    public function inheritParam(string $name): self
    {
        $value = ClassFactory::createRequest()->getParam($name);

        if($value !== null && $value !== '') {
            return $this->auto($name, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     * @return $this
     * @throws AdminURLException {@see AdminURLException::ERROR_INVALID_HOST}
     */
    public function importURL(string $url) : self
    {
        $parsed = parseURL($url);

        $this->checkHost($parsed);

        return $this
            ->dispatcher(ltrim($parsed->getPath(), '/'))
            ->import($parsed->getParams());
    }

    private ?URLInfo $appURL = null;

    private function getAppURL() : URLInfo
    {
        if(!isset($this->appURL)) {
            $this->appURL = parseURL(ClassFactory::createRequest()->getBaseURL());
        }

        return $this->appURL;
    }

    /**
     * Ensures that the specified URL host matches the current application host.
     * @param URLInfo $url
     * @return void
     * @throws AdminURLException {@see AdminURLException::ERROR_INVALID_HOST}
     */
    private function checkHost(URLInfo $url) : void
    {
        $appURL = $this->getAppURL();

        $host = str_replace('www.', '', $url->getHost());
        $expected = str_replace('www.', '', $appURL->getHost());

        if($host === $expected) {
            return;
        }

        throw new AdminURLException(
            'Invalid host in URL.',
            sprintf(
                'Cannot import URL: The host [%s] in the URL does not match the current application host [%s]. '.PHP_EOL.
                'Target URL was: '.PHP_EOL.
                '%s',
                $url->getHost(),
                $appURL->getHost(),
                $url
            ),
            AdminURLException::ERROR_INVALID_HOST
        );
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
     * Adds an array as a JSON string URL parameter.
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
        return $this->string(AdminScreenInterface::REQUEST_PARAM_PAGE, $name);
    }

    /**
     * Adds an admin mode screen parameter.
     * @param string $name
     * @return $this
     */
    public function mode(string $name) : self
    {
        return $this->string(AdminScreenInterface::REQUEST_PARAM_MODE, $name);
    }

    /**
     * Adds an admin submode screen parameter.
     * @param string $name
     * @return $this
     */
    public function submode(string $name) : self
    {
        return $this->string(AdminScreenInterface::REQUEST_PARAM_SUBMODE, $name);
    }

    /**
     * Adds an admin action screen parameter.
     * @param string $name
     * @return $this
     */
    public function action(string $name) : self
    {
        return $this->string(AdminScreenInterface::REQUEST_PARAM_ACTION, $name);
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
        // When importing an application URL, the base URL may already
        // contain a path, so we need to remove it.
        $basePath = trim($this->getAppURL()->getPath(), '/');
        $dispatcher = trim(str_replace($basePath, '', trim($dispatcher, '/')), '/');

        // Enforce that non-file dispatcher paths end with a slash
        if(!empty($dispatcher) && FileHelper::getExtension($dispatcher) === '') {
            $dispatcher .= '/';
        }

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
        ksort($this->params);

        return $this->params;
    }
}
