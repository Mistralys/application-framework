<?php
/**
 * File containing the trait {@see Application_Traits_Loggable}.
 *
 * @package Application
 * @subpackage Logger
 * @see Application_Traits_Loggable
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ClassHelper;

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

    private ?string $selfLogIdentifier = null;

    protected function getIdentifierFromSelf(string $prefix) : string
    {
        if(!isset($this->selfLogIdentifier)) {
            $this->selfLogIdentifier = sprintf(
                '%s [%s]',
                $prefix,
                ClassHelper::getClassTypeName($this)
            );
        }

        return $this->selfLogIdentifier;
    }

    /**
     * @var bool|null
     */
    protected $loggableLoggingEnabled = null;

    public function isLoggingEnabled() : bool
    {
        if($this->loggableLoggingEnabled === null)
        {
            $this->loggableLoggingEnabled = AppFactory::createLogger()->isLoggingEnabled();
        }

        return $this->loggableLoggingEnabled;
    }

    public function getLogger() : Application_Logger
    {
        return AppFactory::createLogger();
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

        AppFactory::createLogger()->logSF(
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

        AppFactory::createLogger()->logSF(
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
        AppFactory::createLogger()->logSeparator();
    }

    protected function logEmptyLine() : void
    {
        AppFactory::createLogger()->logEmptyLine();
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
        AppFactory::createLogger()->logCloseSection($sectionLabel, ...$args);
    }

    protected function logUI(string $message, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        AppFactory::createLogger()->logUI($message, $args);
    }

    protected function logData(array $data) : void
    {
        AppFactory::createLogger()->logData($data);
    }
    
    protected function logError(string $message, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        AppFactory::createLogger()->logError(
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

        AppFactory::createLogger()->logEvent($eventName, $this->getLogIdentifier().$sep.$message, ...$args);
    }

    protected function logHeader(string $message, ...$args) : void
    {
        AppFactory::createLogger()->logHeader($message, ...$args);
    }
}
