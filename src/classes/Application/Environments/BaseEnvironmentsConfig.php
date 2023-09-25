<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\Environments\BaseEnvironmentsConfig
 */

declare(strict_types=1);

namespace Application\Environments;

use Application\ConfigSettings\BaseConfigRegistry;
use Application\Environments\EnvironmentSetup\BaseEnvironmentConfig;
use Application\Environments\Events\IncludesLoaded;
use Application\Environments;
use AppUtils\ClassHelper;
use AppUtils\FileHelper\FolderInfo;

/**
 * Utility class for handling environment-specific settings (production,
 * live, local testing). It offers an alternative to setting constants
 * manually, by providing a structure that is easier to maintain.
 *
 * Usage:
 *
 * 1. Create a class that extends this class.
 * 2. Implement all abstract methods.
 * 3. Create an instance of the class in the `config-local.php` file.
 * 4. Call the {@see self::detect()} method to detect the environment.
 *
 * Local development:
 *
 * When registering a local development environment, create the
 * file `dev-hosts.txt` in the config folder, and add all host names
 * to the environment that should be considered as local development
 * hosts.
 *
 * The method {@see BaseEnvironmentConfig::getDevHosts()} facilitates
 * this when used from within the {@see BaseEnvironmentConfig::setUpEnvironment()},
 * as shown in the following example:
 *
 * <pre>
 * protected function setUpEnvironment(): void
 * {
 *     $localHosts = $this->getDevHosts();
 *     foreach ($localHosts as $host) {
 *         $this->environment->or()->requireHostNameContains($host);
 *     }
 * }
 * </pre>
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseEnvironmentsConfig
{
    protected FolderInfo $configFolder;
    protected Environments $environments;
    protected BaseConfigRegistry $config;

    public function __construct(FolderInfo $configFolder)
    {
        // Enable the logging per default to ensure that
        // environment log messages are kept.
        boot_define(BaseConfigRegistry::LOGGING_ENABLED, true);

        $this->configFolder = $configFolder;
        $this->environments = Environments::getInstance();
        $this->config = $this->createCustomSettings();

        $this->configureCoreSettings();
        $this->configureRequiredSettings();
        $this->registerEnvironments();

        $environments = $this->environments->getAll();

        // Ensure that the default settings will be applied only
        // once all include files have been loaded (as these may
        // contain constants needed to fill the settings).
        foreach($environments as $environment) {
            $environment->onIncludesLoaded(function(IncludesLoaded $event) {
                $this->configureDefaultSettings($event->getEnvironment());
            });
        }
    }

    public function getConfig() : BaseConfigRegistry
    {
        return $this->config;
    }

    abstract protected function getClassName() : string;
    abstract protected function getCompanyName() : string;
    abstract protected function getDummyEmail() : string;
    abstract protected function getSystemEmail() : string;
    abstract protected function getSystemName() : string;

    /**
     * @return string[]
     */
    abstract protected function getContentLocales() : array;

    /**
     * @return string[]
     */
    abstract protected function getUILocales() : array;

    /**
     * The driver must implement a class that holds any custom
     * settings the application may have - even if it has none.
     *
     * @return BaseConfigRegistry
     */
    abstract protected function createCustomSettings() : BaseConfigRegistry;

    /**
     * Configure default setting values, as well as settings common
     * to all environments. Use the {@see self::$config} property
     * to access the settings.
     *
     * @param Environment $environment
     * @return void
     */
    abstract protected function configureDefaultSettings(Environment $environment) : void;

    /**
     * Return a list of all configuration settings that are required:
     * If any of these are missing at the end of the boot process, an
     * exception will be thrown.
     *
     * @return string[]
     */
    abstract protected function getRequiredSettingNames() : array;

    abstract public function getDefaultEnvironmentID() : string;

    /**
     * Returns a list of environment classes that should be
     * used to detect the application's environment. These
     * must extend the {@see BaseEnvironmentConfig} class.
     *
     * @return class-string[]
     */
    abstract protected function getEnvironmentClasses() : array;

    private function registerEnvironments() : void
    {
        $classes = $this->getEnvironmentClasses();

        foreach($classes as $class) {
            ClassHelper::requireObjectInstanceOf(
                BaseEnvironmentConfig::class,
                new $class(
                    $this->config,
                    $this->configFolder
                )
            );
        }
    }

    /**
     * Sets all core system settings, which are directly
     * defined as constants, without using {@see boot_define()}.
     *
     * @return void
     */
    private function configureCoreSettings() : void
    {
        $this->config
            ->setClassName($this->getClassName())
            ->setCompanyName($this->getCompanyName())
            ->setDummyEmail($this->getDummyEmail())
            ->setSystemEmail($this->getSystemEmail())
            ->setSystemName($this->getSystemName())
            ->setContentLocales($this->getContentLocales())
            ->setUILocales($this->getUILocales());
    }

    /**
     * Detects the environment on which the application is
     * running, and returns an environment ID string.
     *
     * @return Environment
     */
    public function detect(): Environment
    {
        return $this->environments->detect($this->getDefaultEnvironmentID());
    }

    private function configureRequiredSettings() : void
    {
        $names = $this->getRequiredSettingNames();

        foreach($names as $name) {
            boot_require($name);
        }
    }
}
