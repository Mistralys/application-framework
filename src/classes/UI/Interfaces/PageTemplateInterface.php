<?php
/**
 * File containing the interface {@see \UI\Interfaces\PageTemplateInterface}.
 *
 * @package Application
 * @subpackage UserInterface
 * @see \UI\Interfaces\PageTemplateInterface
 */

declare(strict_types=1);

namespace UI\Interfaces;

/**
 * Template class: this class is instantiated for each
 * template file, and is the context of the template
 * in $this, when not using a class based template.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see \UI_Page_Template
 */
interface PageTemplateInterface
{
    /**
     * @param array<string,mixed> $vars
     * @return $this
     */
    public function setVars(array $vars) : self;

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setVar(string $name, $value) : self;

    public function getVar(string $name, $default = null);

    /**
     * Retrieves the variable, and ensures that it is an instance
     * of the specified class.
     *
     * @template ClassInstanceType
     * @param string $name
     * @param class-string<ClassInstanceType> $className
     * @return ClassInstanceType
     */
    public function getObjectVar(string $name, string $className);

    public function getBoolVar(string $name) : bool;

    public function getArrayVar(string $name) : array;

    public function getStringVar(string $name) : string;

    public function printVar(string $name, $default = null) : self;

    public function getLogoutURL() : string;

    /**
     * @param array<string,string|int|float> $params
     * @return string
     */
    public function buildURL(array $params=array()) : string;

    public function getImageURL(string $imageName) : string;

    /**
     * Checks if the specified variable has been set.
     */
    public function hasVar(string $name) : bool;

    public function hasVarNonEmpty(string $name) : bool;

    public function getAppNameShort() : string;

    public function getAppName() : string;
}
