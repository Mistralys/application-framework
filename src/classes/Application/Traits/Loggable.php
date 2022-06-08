<?php
/**
 * File containing the trait {@see Application_Traits_Loggable}.
 *
 * @package Application
 * @subpackage Logger
 * @see Application_Traits_Loggable
 */

declare(strict_types=1);

/**
 * Trait for classes to add logging capability using the
 * application's logger. Adds protected utility methods for
 * logging.
 *
 * Usage:
 *
 * - Implement the interface {@see Application_Interfaces_Loggable}
 * - Use the trait
 * - Implement the `getLogIdentifier()` method
 *
 * @package Application
 * @subpackage Logger
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Loggable
 */
trait Application_Traits_Loggable
{
    abstract public function getLogIdentifier() : string;

    /**
     * @var bool|null
     */
    protected $loggableLoggingEnabled = null;

    public function isLoggingEnabled() : bool
    {
        if($this->loggableLoggingEnabled === null)
        {
            $this->loggableLoggingEnabled = Application::getLogger()->isLoggingEnabled();
        }

        return $this->loggableLoggingEnabled;
    }

    public function getLogger() : Application_Logger
    {
        return Application::getLogger();
    }

    /**
     * Logs a message. If additional parameters are present,
     * they will be injected into the message using the PHP
     * `sprintf` function.
     *
     * @param string $message
     * @param mixed ...$args
     */
    protected function log(string $message, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        Application::getLogger()->logSF(
            sprintf(
            '%s | %s',
                $this->getLogIdentifier(),
                $message
            ),
            Application_Logger::CATEGORY_GENERAL,
            ...$args
        );
    }

    protected function logCategory(string $message, string $category, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        Application::getLogger()->logSF(
            sprintf(
                '%s | %s',
                $this->getLogIdentifier(),
                $message
            ),
            $category,
            ...$args
        );
    }

    /**
     * Adds a separation line to the log.
     * NOTE: Does not include an empty line afterwards.
     */
    protected function logSeparator() : void
    {
        Application::getLogger()->logSeparator();
    }

    protected function logEmptyLine() : void
    {
        Application::getLogger()->logEmptyLine();
    }

    /**
     * Adds a separation line intended for closing a section that has
     * been previously opened by a header.
     *
     * @param string $sectionLabel
     * @param mixed ...$args For filling any placeholders there may be in the label.
     */
    protected function logCloseSection(string $sectionLabel, ...$args) : void
    {
        Application::getLogger()->logCloseSection($sectionLabel, ...$args);
    }

    protected function logUI(string $message, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        Application::getLogger()->logUI($message, $args);
    }

    protected function logData(array $data) : void
    {
        Application::getLogger()->logData($data);
    }
    
    protected function logError(string $message, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        Application::getLogger()->logError(
            sprintf(
                '%s | %s',
                $this->getLogIdentifier(),
                $message
            ),
            ...$args
        );
    }

    protected function logEvent(string $eventName, string $message='', ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        $sep = ' | ';

        if(empty($message))
        {
            $sep = '';
        }

        Application::getLogger()->logEvent($eventName, $this->getLogIdentifier().$sep.$message, ...$args);
    }

    protected function logHeader(string $message, ...$args) : void
    {
        Application::getLogger()->logHeader($message, ...$args);
    }
}
