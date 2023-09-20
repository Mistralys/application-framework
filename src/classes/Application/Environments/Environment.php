<?php
/**
 * File containing the {@link Environment} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Environment
 */

declare(strict_types=1);

namespace Application\Environments;

use Application\Environments;
use Application\Environments\EnvironmentException;
use Application\Environments\Events\EnvironmentActivated;
use Application\Environments\Events\EnvironmentDetected;
use Application\Environments\Events\IncludesLoaded;
use Application_Environments_Environment_Requirement;
use Application_Environments_Environment_Requirement_BoolTrue;
use Application_Environments_Environment_Requirement_CLI;
use Application_Environments_Environment_Requirement_HostNameContains;
use Application_Environments_Environment_Requirement_LocalTest;
use Application_Environments_Environment_Requirement_Windows;
use Application_EventHandler_EventableListener;
use Application_Interfaces_Eventable;
use Application_Traits_Eventable;
use Application_Traits_Loggable;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Container for a single application environment definition.
 *
 * It sets the criteria for this environment to be selected,
 * and configures application settings and additional include
 * files to load when the environment is activated.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Environments
 */
class Environment implements Application_Interfaces_Eventable
{
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public const ERROR_INCLUDE_FILE_NOT_FOUND = 143901;
    public const ERROR_INCLUDE_FILE_NOT_PHP_FILE = 143902;

    public const REQUIRE_CLI = 'cli';

    public const REQUIRE_WIN = 'win';

    public const REQUIRE_HOSTNAME_CONTAINS = 'hostname-contains';

    /**
     * @var array<string,string|int|float|bool|array>
     */
    protected array $globals = array();

    /**
     * @var array<string,string|int|float|bool|array>
     */
    protected array $bootDefines = array();

    /**
     * @var array<string,string|int|float|bool|array>
     */
    protected array $defines = array();

    protected string $id;
    protected string $type;
    protected int $requirementSetCounter = 0;

    /**
     * @var array<int,Application_Environments_Environment_Requirement>
     */
    protected array $requirements = array();

    /**
     * @var string[]
     */
    protected array $includeFiles = array();

    /**
     * @var callable|NULL
     */
    private $configCallback;

    public function __construct(Environments $environments, string $id, string $type, ?callable $configCallback = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->configCallback = $configCallback;

        // Listener to activate the environment when it is detected.
        $environments->onEnvironmentDetected(function (EnvironmentDetected $event) {
            if ($event->getEnvironment() === $this) {
                $this->activate();
            }
        });
    }

    public function isDev(): bool
    {
        return $this->type === Environments::TYPE_DEV;
    }

    public function isProd(): bool
    {
        return $this->type === Environments::TYPE_PROD;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function or(): Environment
    {
        $this->requirementSetCounter++;

        return $this;
    }

    public function requireTrue(bool $condition): Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_BoolTrue($condition));
    }

    public function requireCLI(): Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_CLI());
    }

    public function requireLocalTest(): Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_LocalTest());
    }

    public function requireWindows(): Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_Windows());
    }

    public function requireHostNameContains(string $search): Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_HostNameContains($search));
    }

    protected function addRequirement(Application_Environments_Environment_Requirement $requirement): Environment
    {
        if (!isset($this->requirements[$this->requirementSetCounter])) {
            $this->requirements[$this->requirementSetCounter] = array();
        }

        $this->requirements[$this->requirementSetCounter][] = $requirement;

        return $this;
    }

    /**
     * Adds a listener to the {@see Environments::EVENT_ENVIRONMENT_ACTIVATED}
     * event, which is triggered when this environment has been detected and activated.
     *
     * @param callable $callback Gets an instance of {@see EnvironmentActivated} as sole parameter.
     * @return Application_EventHandler_EventableListener
     */
    public function onActivated(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->addEventListener(Environments::EVENT_ENVIRONMENT_ACTIVATED, $callback);
    }

    /**
     * Adds a listener to the {@see Environments::EVENT_INCLUDES_LOADED}
     * event, which is triggered when all include files have
     * been loaded.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onIncludesLoaded(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->addEventListener(Environments::EVENT_INCLUDES_LOADED, $callback);
    }

    /**
     * Defines a global variable value to set if this environment
     * is activated.
     *
     * @param string $name
     * @param string|int|float|bool|array $value
     * @return $this
     */
    public function setGlobal(string $name, $value): self
    {
        $this->globals[$name] = $value;
        return $this;
    }

    /**
     * Defines a variable value to set via {@see define()}
     * if this environment is activated.
     *
     * @param string $name
     * @param string|int|float|bool|array $value
     * @return $this
     */
    public function setBootDefine(string $name, $value): self
    {
        $this->bootDefines[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param string|int|float|bool|array $value
     * @return $this
     */
    public function setDefine(string $name, $value): self
    {
        $this->defines[$name] = $value;
        return $this;
    }

    /**
     * Adds a PHP file to include if this environment is activated.
     *
     * @param string $path
     * @return $this
     *
     * @throws EnvironmentException
     * @throws FileHelper_Exception
     */
    public function includeFile(string $path, bool $optional = false): self
    {
        if (!$optional && !file_exists($path)) {
            throw new EnvironmentException(
                'Application\Environments\Environment include file does not exist.',
                sprintf(
                    'The include file [%s] for environment [%s] could not be found on disk.',
                    $path,
                    $this->getID()
                ),
                self::ERROR_INCLUDE_FILE_NOT_FOUND
            );
        }

        if (FileHelper::getExtension($path) !== 'php') {
            throw new EnvironmentException(
                'Environments can only include PHP files.',
                sprintf(
                    'The include file [%s] is not a valid PHP file for environment [%s].',
                    $path,
                    $this->getID()
                ),
                self::ERROR_INCLUDE_FILE_NOT_PHP_FILE
            );
        }

        $this->includeFiles[] = $path;

        return $this;
    }

    private function activate(): self
    {
        $this->log('Activating environment.');

        $this->loadIncludeFiles();
        $this->setVariableValues();

        if (isset($this->configCallback)) {
            call_user_func($this->configCallback, $this);
        }

        $this->triggerEvent(
            Environments::EVENT_ENVIRONMENT_ACTIVATED,
            array($this),
            EnvironmentActivated::class
        );

        return $this;
    }

    private function setVariableValues(): void
    {
        foreach ($this->globals as $name => $value) {
            $GLOBALS[$name] = $value;
        }

        foreach ($this->bootDefines as $name => $value) {
            boot_define($name, $value);
        }

        foreach ($this->defines as $name => $value) {
            define($name, $value);
        }
    }

    private function loadIncludeFiles(): void
    {
        if (empty($this->includeFiles)) {
            return;
        }

        $this->log('Include files | Found [%s] files.', count($this->includeFiles));

        foreach ($this->includeFiles as $file) {
            if (file_exists($file)) {
                $this->log('Include files | Including [%s] | Path [%s]', basename($file), $file);
                require_once $file;
            }
        }

        $this->triggerEvent(
            Environments::EVENT_INCLUDES_LOADED,
            array($this),
            IncludesLoaded::class
        );
    }

    public function isMatch(): bool
    {
        if (empty($this->requirements)) {
            $this->log('No requirements defined, skipping.');

            return false;
        }

        // go through all requirement sets: if any
        // of the sets is valid, the environment is
        // a match.
        foreach ($this->requirements as $setID => $set) {
            $setValid = true;

            foreach ($set as $requirement) {
                if (!$requirement->isValid()) {
                    $this->log(sprintf(
                        'Set [%s] | Requirement [%s] | Failed.',
                        $setID,
                        $requirement->getID()
                    ));

                    $setValid = false;
                } else {
                    $this->log(sprintf(
                        'Set [%s] | Requirement [%s] | Passed.',
                        $setID,
                        $requirement->getID()
                    ));
                }
            }

            if ($setValid) {
                $this->log(sprintf('Set [%s] | Passed.', $setID));

                return true;
            }
        }

        return false;
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            'Environments | [%s]',
            $this->getID()
        );
    }
}
