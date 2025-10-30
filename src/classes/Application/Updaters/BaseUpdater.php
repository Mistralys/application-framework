<?php
/**
 * @package Maintenance
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\Updaters;

use Application;
use Application\AppFactory;
use Application_Driver;
use Application_Exception;
use Application_Interfaces_Loggable;
use Application_Request;
use Application_Session;
use Application_Traits_Loggable;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\OutputBuffering_Exception;
use DBHelper;
use UI;
use UI_Page_Section;
use UI_Renderable_Interface;

/**
 * Abstract base class used to implement updaters
 * with the {@see UpdaterInterface} interface.
 *
 * @package Maintenance
 * @subpackage Core
 */
abstract class BaseUpdater implements UpdaterInterface, Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    protected UpdatersCollection $updaters;
    protected Application_Request $request;
    protected Application_Driver $driver;
    protected Application_Session $session;
    protected Application $app;
    protected UI $ui;
    protected static bool $simulationStarted = false;
    protected string $sessionVar;
    private string $logIdentifier;

    public function __construct(UpdatersCollection $updaters)
    {
        $this->updaters = $updaters;
        $this->request = Application_Request::getInstance();
        $this->driver = Application_Driver::getInstance();
        $this->app = $this->driver->getApplication();
        $this->session = Application::getSession();
        $this->sessionVar = 'Updater_' . $this->getID();
        $this->ui = $this->driver->getUI();
        $this->logIdentifier = sprintf('Updater [%s]', $this->getID());

        if (!self::$simulationStarted && $this->isSimulation()) {
            $logger = AppFactory::createLogger();
            $logger->enableHTML();
            $logger->logModeEcho();

            Application::logHeader('Simulation mode');
            Application::log('Request parameters:');
            Application::logData($_REQUEST);

            self::$simulationStarted = true;
            $this->logIdentifier .= ' | [SIMULATION]';
        }
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    /**
     * @param array<string,string|int|float|bool|StringableInterface|NULL> $params
     * @return string
     */
    public function buildURL(array $params = array()): string
    {
        $params[UpdatersCollection::REQUEST_PARAM_UPDATER_ID] = $this->getID();
        return $this->updaters->buildURL($params);
    }

    protected function redirectTo($urlOrParams)
    {
        if (is_array($urlOrParams)) {
            $urlOrParams = $this->buildURL($urlOrParams);
        }

        $urlOrParams = str_replace('&amp;', '&', $urlOrParams);

        header('Location:' . $urlOrParams);
        Application::exit();
    }

    public function hasSpecificVersion(string $version) : bool
    {
        $versions = $this->getValidVersions();
        if ($versions === '*') {
            return false;
        }

        if (!is_array($versions)) {
            $versions = array($versions);
        }

        return in_array($version, $versions, true);
    }

    /**
     * Renders a page with the specified content and optional title.
     *
     * @param string|number|UI_Renderable_Interface $content
     * @param string|number|UI_Renderable_Interface $title
     * @return string
     * @throws Application_Exception
     * @throws OutputBuffering_Exception
     */
    protected function renderPage($content, $title = ''): string
    {
        if (empty($title)) {
            $title = $this->getLabel();
        }

        return $this->updaters->renderPage($title, $content);
    }

    /**
     * Creates the markup for an error message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array $options
     * @return string
     */
    public function renderErrorMessage($message, $options = array())
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_ERROR, $options);
    }

    /**
     * Creates the markup for an informational message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array $options
     * @return string
     */
    public function renderInfoMessage($message, $options = array())
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_INFO, $options);
    }

    /**
     * Creates the markup for a success message and returns the generated HTML code.
     * Use the options array to set any desired options, see the {@link renderMessage()}
     * method for a list of options.
     *
     * @param string $message
     * @param array $options
     * @return string
     */
    public function renderSuccessMessage($message, $options = array())
    {
        return $this->renderMessage($message, UI::MESSAGE_TYPE_SUCCESS, $options);
    }

    /**
     * Creates the markup for a message of the specified type and returns the
     * generated HTML code. You may use the options array to configure the
     * error message further.
     *
     * Available option switches:
     *
     * - dismissable: boolean / Whether the message is dismissable. Default: true
     *
     * @param string $message
     * @param string $type
     * @param array $options
     * @return string
     */
    public function renderMessage($message, $type, $options = array())
    {
        if (!isset($options['dismissable'])) {
            $options['dismissable'] = true;
        }

        // add the missing dot if need be
        $message = trim($message);
        $lastChar = mb_substr($message, -1);
        switch ($lastChar) {
            case '>':
            case '.':
                break;

            default:
                $message .= '.';
        }

        $html =
            '<div class="alert alert-' . $type . '">';
        if ($options['dismissable']) {
            $html .=
                '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        }
        $html .=
            $message .
            '</div>';

        return $html;
    }

    /**
     * Gets the value of a persistent updater-specific application setting.
     *
     * @param string $name
     * @param string|NULL $default
     * @return string
     */
    public function getSetting(string $name, ?string $default = null): ?string
    {
        $name = $this->getSettingName($name);
        return Application_Driver::createSettings()->get($name, $default);
    }

    /**
     * Sets the setting of a persistent updater-specific application setting.
     *
     * @param string $name
     * @param string $value
     */
    protected function setSetting(string $name, string $value): void
    {
        $name = $this->getSettingName($name);
        Application_Driver::createSettings()->set($name, $value);
    }

    /**
     * Deletes an existing updater-specific application setting.
     *
     * @param string $name
     */
    protected function deleteSetting(string $name): void
    {
        $name = $this->getSettingName($name);
        Application_Driver::createSettings()->delete($name);
    }

    /**
     * Resolves the name to use for the updater-specific application setting.
     * Makes the name unique to the updater by adding to updater's ID to it.
     *
     * @param string $name
     * @return string
     */
    protected function getSettingName(string $name): string
    {
        return 'Updater_' . $this->getID() . '_' . $name;
    }

    /**
     * Retrieves a session value of the updater.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    protected function getSessionValue($name, $default = null)
    {
        $data = $this->session->getValue($this->sessionVar, array());
        if (isset($data['values']) && isset($data['values'][$name])) {
            return $data['values'][$name];
        }

        return $default;
    }

    /**
     * Sets a session value for the updater.
     *
     * @param string $name
     * @param mixed $value
     */
    protected function setSessionValue($name, $value)
    {
        $data = $this->session->getValue($this->sessionVar, array());
        if (!isset($data['values'])) {
            $data['values'] = array();
        }

        $data['values'][$name] = $value;
        $this->session->setValue($this->sessionVar, $data);
    }

    /**
     * Handles cleanup operations once the update is done:
     * removes any remaining session variables and the like.
     */
    protected function cleanUp(): void
    {
        $this->session->unsetValue($this->sessionVar);
    }

    protected function isSimulation(): bool
    {
        return Application::isSimulation();
    }

    public function isEnabled(): bool
    {
        return $this->updaters->isEnabled($this);
    }

    public function getCategory(): string
    {
        return t('%1$s system', $this->driver->getAppNameShort());
    }

    public function getListLabel(): string
    {
        return $this->getCategory() . ' - ' . $this->getLabel();
    }

    protected function startTransaction(): void
    {
        DBHelper::startTransaction();
    }

    protected function endTransaction(): void
    {
        if ($this->isSimulation()) {
            DBHelper::rollbackTransaction();
        } else {
            DBHelper::commitTransaction();
        }
    }

    protected function rollbackTransaction(): void
    {
        DBHelper::rollbackTransaction();
    }

    protected function createSection() : UI_Page_Section
    {
        return $this->ui->createPage('dummy')->createSection();
    }
}
