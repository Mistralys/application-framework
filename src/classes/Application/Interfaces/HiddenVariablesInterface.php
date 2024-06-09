<?php

declare(strict_types=1);

namespace Application\Interfaces;

use Application\Driver\DriverException;
use AppUtils\Interfaces\StringableInterface;
use UI_Exception;

interface HiddenVariablesInterface
{
    /**
     * @param array<string,string|number|StringableInterface|NULL> $vars
     * @return $this
     */
    public function addHiddenVars(array $vars) : self;

    /**
     * @param string $name
     * @param string|number|StringableInterface|NULL $value
     * @return $this
     * @throws UI_Exception
     */
    public function addHiddenVar(string $name, $value) : self;

    /**
     * @return array<string,string>
     */
    public function getHiddenVars() : array;

    /**
     * Adds all page-related variables (page / mode / submode...) for
     * the current admin screen.
     *
     * NOTE: Only possible when in admin mode.
     *
     * @return $this
     * @throws DriverException
     */
    public function addHiddenScreenVars() : self;

    /**
     * @param string[] $classes
     * @return string
     */
    public function renderHiddenInputs(array $classes=array()) : string;
}
