<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\Environments\BaseEnvironmentsConfig
 */

declare(strict_types=1);

namespace Application\Environments;

use Application\ConfigSettings\BaseConfigSettings;
use Application\Environments\EnvironmentSetup\BaseEnvironmentConfig;
use Application\Environments\Events\IncludesLoaded;
use Application\Environments;
use Application\Environments\Environment;
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
 * The method {@see self::getDevHosts()} facilitates this, as shown
 * in the following example:
 *
 * <pre>
 * $localHosts = $this->getDevHosts();
 * foreach ($localHosts as $host) {
 *     $environment->or()->requireHostNameContains($host);
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
    protected BaseConfigSettings $config;
    protected FolderInfo $environmentsFolder;

    public function __construct(FolderInfo $configFolder)
    {
        $this->configFolder = $configFolder;
        $this->environments = Environments::getInstance();
        $this->config = $this->createCustomSettings();

        $this->configureCoreSettings();
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

    public function getConfig() : BaseConfigSettings
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
    abstract protected function createCustomSettings() : BaseConfigSettings;

    abstract protected function configureDefaultSettings(Environment $environment) : void;
    abstract public function getDefaultEnvironmentID() : string;

    /**
     * @return class-string[]
     */
    abstract protected function getEnvironmentClasses() : array;

    protected function registerEnvironments() : void
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

    /**
     * @return string[]
     */
    public function getDevHosts(): array
    {
        $hostsFile = $this->configFolder . '/dev-hosts.txt';

        if (!file_exists($hostsFile)) {
            return array();
        }

        $hosts = explode("\n", file_get_contents($hostsFile));
        $hosts = array_map('trim', $hosts);

        $result = array();

        foreach ($hosts as $host) {
            if (!empty($host)) {
                $result[] = $host;
            }
        }

        return $result;
    }
}
