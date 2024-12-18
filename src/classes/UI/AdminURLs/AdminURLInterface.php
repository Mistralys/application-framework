<?php
/**
 * @package User Interface
 * @subpackage Admin URLs
 */

declare(strict_types=1);

namespace UI\AdminURLs;

use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\Interfaces\RenderableInterface;

/**
 * Interface for admin URL instances.
 * See {@see AdminURL} for the implementation.
 *
 * @package User Interface
 * @subpackage Admin URLs
 * @see AdminURL
 */
interface AdminURLInterface extends RenderableInterface
{
    /**
     * Removes a parameter if it exists.
     * @param string $name
     * @return $this
     */
    public function remove(string $name) : self;

    /**
     * Imports an array of parameter values.
     * @param array<string,string|int|float|bool|null> $params
     * @return $this
     */
    public function import(array $params) : self;

    /**
     * Imports the dispatcher and parameters from an application-internal URL string.
     *
     * NOTE: The host must match the current application host.
     *
     * @param string $url
     * @return $this
     */
    public function importURL(string $url) : self;

    /**
     * Adds a parameter, automatically determining its type.
     *
     * @param string $name
     * @param string|int|float|bool|null $value
     * @return $this
     */
    public function auto(string $name, $value) : self;

    /**
     * @param string $name
     * @param int $value
     * @return $this
     */
    public function int(string $name, int $value) : self;

    /**
     * @param string $name
     * @param float $value
     * @return $this
     */
    public function float(string $name, float $value) : self;

    /**
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function string(string $name, ?string $value) : self;

    /**
     * @param string $name
     * @param bool $value
     * @param bool $yesNo
     * @return $this
     */
    public function bool(string $name, bool $value, bool $yesNo=false) : self;

    /**
     * Adds an array as a JSON string.
     * @param string $name
     * @param array<int|string,string|int|float|bool|NULL|array> $data
     * @return $this
     * @throws JSONConverterException
     */
    public function arrayJSON(string $name, array $data) : self;

    /**
     * Adds an admin area screen parameter.
     * @param string $name
     * @return $this
     */
    public function area(string $name) : self;

    /**
     * Adds an admin mode screen parameter.
     * @param string $name
     * @return $this
     */
    public function mode(string $name) : self;

    /**
     * Adds an admin submode screen parameter.
     * @param string $name
     * @return $this
     */
    public function submode(string $name) : self;

    /**
     * Adds an admin action screen parameter.
     * @param string $name
     * @return $this
     */
    public function action(string $name) : self;

    /**
     * Add the parameter to enable the application simulation mode.
     * @param bool $enabled
     * @return $this
     */
    public function simulation(bool $enabled=true) : self;

    /**
     * Sets the name of the dispatcher script to use in the URL.
     * @param string $dispatcher
     * @return $this
     */
    public function dispatcher(string $dispatcher) : self;

    /**
     * @return string The generated URL with all parameters.
     */
    public function get() : string;

    /**
     * @return array<string,string>
     */
    public function getParams() : array;
}
