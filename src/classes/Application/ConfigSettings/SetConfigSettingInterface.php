<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\ConfigSettings\SetConfigSettingInterface
 */

declare(strict_types=1);

namespace Application\ConfigSettings;

/**
 * Interface for a class that can set application configuration settings.
 * Use the trait {@see SetAppConfigSettingTrait} to add setters for all
 * core configuration settings.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see SetAppConfigSettingTrait
 */
interface SetConfigSettingInterface
{
    /**
     * @param string $name
     * @param string|int|float|bool|array $value
     * @return $this
     */
    public function setBootDefine(string $name, $value) : self;


    /**
     * @param string $name
     * @param string|int|float|bool|array $value
     * @return $this
     */
    public function setConstant(string $name, $value) : self;
}
