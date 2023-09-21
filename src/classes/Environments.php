<?php
/**
 * File containing the {@link Environments} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Environments
 */

declare(strict_types=1);

namespace Application;

use Application\ConfigSettings\BaseConfigRegistry;
use Application\Environments\Events\EnvironmentDetected;
use Application_EventHandler_EventableListener;
use Application_Exception;
use Application_Interfaces_Eventable;
use Application_Traits_Eventable;
use Application_Traits_Loggable;
use Application\Environments\Environment;

/**
 * Application\Environments\Environment manager: handles detecting the environment
 * in which the application runs. This is used in the
 * configuration to determine the settings to use based
 * on the environment (local, dev, prod).
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Environments implements Application_Interfaces_Eventable
{
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public const ERROR_NO_ENVIRONMENTS_REGISTERED = 47601;
    public const ERROR_ENVIRONMENT_ALREADY_REGISTERED = 47602;
    public const ERROR_UNREGISTERED_ENVIRONMENT = 47603;

    public const TYPE_DEV = 'dev';
    public const TYPE_PROD = 'prod';
    public const EVENT_ENVIRONMENT_ACTIVATED = 'Activated';
    public const EVENT_ENVIRONMENT_DETECTED = 'EnvironmentDetected';
    public const EVENT_INCLUDES_LOADED = 'IncludesLoaded';

    /**
     * @var array<string,Environment>
     */
    protected array $environments = array();

    protected static ?Environments $instance = null;
    protected ?Environment $detected = null;

    protected function __construct()
    {
    }

    public static function getInstance(): Environments
    {
        if (!isset(self::$instance)) {
            self::$instance = new Environments();
        }

        return self::$instance;
    }

    public function register(string $id, string $type, ?callable $configCallback=null): Environment
    {
        if (isset($this->environments[$id])) {
            throw new Application_Exception(
                'Cannot register the same environment twice',
                sprintf(
                    'Tried registering the environment [%s] although it has already been registered.',
                    $id
                ),
                self::ERROR_ENVIRONMENT_ALREADY_REGISTERED
            );
        }


        $env = new Environment($this, $id, $type, $configCallback);

        $this->environments[$id] = $env;

        $this->log(sprintf('Registered environment [%s].', $id));

        return $env;
    }

    public function registerDev(string $id, ?callable $configCallback=null): Environment
    {
        return $this->register($id, self::TYPE_DEV, $configCallback);
    }

    public function registerProd(string $id, ?callable $configCallback=null): Environment
    {
        return $this->register($id, self::TYPE_PROD, $configCallback);
    }

    public function getDetected(): ?Environment
    {
        return $this->detected;
    }

    public function detect(string $defaultID): Environment
    {
        if (isset($this->detected)) {
            return $this->detected;
        }

        $this->detected = $this->_detect($defaultID);

        boot_define(BaseConfigRegistry::ENVIRONMENT, $this->detected->getID());

        $this->triggerEvent(
            self::EVENT_ENVIRONMENT_DETECTED,
            array($this->detected),
            EnvironmentDetected::class
        );

        return $this->detected;
    }

    /**
     * Adds a listener to the {@see Environments::EVENT_ENVIRONMENT_DETECTED}
     * event, which is triggered when a specific environment has been detected.
     *
     * NOTE: This is not the same as the {@see Environments::EVENT_ENVIRONMENT_ACTIVATED}
     * event, which can be listened to via the {@see Environment::onActivated()}
     * method.
     *
     * @param callable $callback Gets an instance of {@see EnvironmentDetected} as sole parameter.
     * @return Application_EventHandler_EventableListener
     */
    public function onEnvironmentDetected(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_ENVIRONMENT_DETECTED, $callback);
    }

    protected function _detect(string $defaultID): Environment
    {
        $this->log('Detecting current environment.');

        if (empty($this->environments)) {
            throw new Application_Exception(
                'No environments registered',
                '',
                self::ERROR_NO_ENVIRONMENTS_REGISTERED
            );
        }

        /* @var Environment $environment */
        foreach ($this->environments as $environment) {
            if ($environment->isMatch()) {
                $this->log(sprintf('Current environment matches [%s].', $environment->getID()));

                return $environment;
            }
        }

        $this->log(sprintf('None of the environments matched, using default [%s].', $defaultID));

        return $this->getByID($defaultID);
    }

    public function getByID(string $id): Environment
    {
        if (isset($this->environments[$id])) {
            return $this->environments[$id];
        }

        throw new Application_Exception(
            'No such environment registered.',
            sprintf(
                'The environment [%s] has not been registered.',
                $id
            ),
            self::ERROR_UNREGISTERED_ENVIRONMENT
        );
    }

    public static function getEnvironment(): Environment
    {
        return self::getInstance()->detect('');
    }

    public function getLogIdentifier(): string
    {
        return 'Environments';
    }

    /**
     * @return Environment[]
     */
    public function getAll(): array
    {
        return array_values($this->environments);
    }
}
