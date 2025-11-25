<?php

declare(strict_types=1);

use Application\ConfigSettings\BaseConfigRegistry;
use AppUtils\FileHelper;

class Application_OAuth
{
    public const ERROR_STRATEGY_CLASS_NOT_FOUND = 75301;
    public const ERROR_INVALID_STRATEGY_CLASS = 75302;
    public const ERROR_UNKNOWN_STRATEGY = 75303;
    public const ERROR_AUTH_SALT_NOT_SET = 75304;

    /**
     * @var string
     */
    private $strategiesFolder;

    /**
     * @var string
     */
    private $appStrategiesFolder;

    /**
     * @var string[]
     */
    private $available = array();

    /**
     * @var string[]
     */
    private $enabled = array();

    /**
     * @var array<string,Application_OAuth_Strategy>
     */
    private $loaded = array();

    public function __construct(Application_Driver $driver)
    {
        $this->strategiesFolder = $driver->getApplication()->getClassesFolder().'/Application/OAuth/Strategy';
        $this->appStrategiesFolder = $driver->getClassesFolder().'/OAuth';

        $this->loadStrategies();
        $this->loadAppStrategies();

        if(!boot_defined(BaseConfigRegistry::AUTH_SALT))
        {
            throw new OAuth_Exception(
                'The auth salt setting has not been set',
                sprintf(
                    'The config setting [%s] must be present.',
                    BaseConfigRegistry::AUTH_SALT
                ),
                self::ERROR_AUTH_SALT_NOT_SET
            );
        }
    }

    private function loadStrategies() : void
    {
        $this->available = FileHelper::createFileFinder($this->strategiesFolder)
            ->getPHPClassNames();
    }

    private function loadAppStrategies() : void
    {
        $this->enabled = FileHelper::createFileFinder($this->appStrategiesFolder)
            ->getPHPClassNames();
    }

    /**
     * Creates/returns the instance of the login strategy.
     * Must be enabled in the application, i.e., the matching
     * class file must have been created in the application's
     * `OAuth` class folder.
     *
     * @param string $name The strategy name, e.g. "Google".
     * @return Application_OAuth_Strategy
     * @throws OAuth_Exception
     */
    public function createStrategy(string $name) : Application_OAuth_Strategy
    {
        if(isset($this->loaded[$name]))
        {
            return $this->loaded[$name];
        }

        $baseClass = 'Application_OAuth_Strategy_'.$name;
        $appClass = APP_CLASS_NAME.'_OAuth_'.$name;

        if(!class_exists($appClass))
        {
            throw new OAuth_Exception(
                'OAuth strategy not found',
                sprintf(
                    'The expected class [%s] could not be found.',
                    $appClass
                ),
                self::ERROR_STRATEGY_CLASS_NOT_FOUND
            );
        }

        $strategy = new $appClass();

        if($strategy instanceof $baseClass)
        {
            $this->loaded[$name] = $strategy;

            return $strategy;
        }

        throw new OAuth_Exception(
            'Invalid OAuth strategy',
            sprintf(
                'The OAuth class [%s] does not extend the expected base class [%s].',
                $appClass,
                $baseClass
            ),
            self::ERROR_INVALID_STRATEGY_CLASS
        );
    }

    /**
     * Retrieves the names of all login strategies available in
     * the framework.
     *
     * @return string[]
     */
    public function getAvailableNames() : array
    {
        return $this->available;
    }

    /**
     * Retrieves the names of all login strategies that are enabled
     * for the application.
     *
     * @return string[]
     */
    public function getEnabledNames() : array
    {
        return $this->enabled;
    }

    /**
     * Whether the application has any login strategies enabled.
     *
     * @return bool
     */
    public function hasStrategies() : bool
    {
        return !empty($this->enabled);
    }

    /**
     * Whether the specified login strategy exists in the framework.
     *
     * @param string $name The strategy name, e.g. "Google".
     * @return bool
     */
    public function strategyExists(string $name) : bool
    {
        return in_array($name, $this->available);
    }

    /**
     * Whether the specified login strategy is enabled in
     * the application.
     *
     * @param string $name The strategy name, e.g. "Google".
     * @return bool
     */
    public function isStrategyEnabled(string $name) : bool
    {
        return file_exists($this->appStrategiesFolder.'/'.$name.'.php');
    }

    /**
     * Retrieves all strategies enabled in the application,
     * sorted by label.
     *
     * @return Application_OAuth_Strategy[]
     * @throws OAuth_Exception
     */
    public function getStrategies() : array
    {
        $result = array();

        foreach ($this->enabled as $id)
        {
            $result[] = $this->createStrategy($id);
        }

        usort($result, function(Application_OAuth_Strategy $a, Application_OAuth_Strategy $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        return $result;
    }

    /**
     * Retrieves a specific login strategy by name. Throws
     * an exception if it does not exist.
     *
     * @param string $name
     * @return Application_OAuth_Strategy
     * @throws OAuth_Exception
     */
    public function getByName(string $name) : Application_OAuth_Strategy
    {
        $all = $this->getStrategies();

        foreach ($all as $strategy)
        {
            if($strategy->getName() === $name)
            {
                return $strategy;
            }
        }

        throw new OAuth_Exception(
            'Unknown OAuth strategy',
            sprintf(
                'The strategy [%s] does not exist, or is not enabled in the application. Available strategies are: [%s].',
                $name,
                implode(', ', $this->getEnabledNames())
            ),
            self::ERROR_UNKNOWN_STRATEGY
        );
    }

    public function createConfig() : Application_OAuth_Config
    {
        return new Application_OAuth_Config($this);
    }

    public function createAuthenticator(Application_OAuth_Strategy $strategy) : Hybridauth\Hybridauth
    {
        return new \Hybridauth\Hybridauth($this->createConfig()->toArray($strategy));
    }

    public function isConnected() : ?\Hybridauth\Adapter\AdapterInterface
    {
        $auth = new \Hybridauth\Hybridauth($this->createConfig()->toArray());
        $connected = $auth->getConnectedAdapters();

        if(!empty($connected))
        {
            return array_shift($connected);
        }

        return null;
    }
}
