<?php
/**
 * @package Application
 * @subpackage Logging
 * @see \Application\Logger\PSRLogger
 */

declare(strict_types=1);

namespace Application\Logger;

use Application\AppFactory;
use Application_Logger;
use Hybridauth\Logger\Logger;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * PSR-3 logger implementation for the application logger.
 * Redirects all logging messages to the main logger.
 *
 * @package Application
 * @subpackage Logging
 * @see Application_Logger
 */
class PSRLogger extends AbstractLogger
{
    /** Mapping of PSR-3 log levels to application logger levels.
     * @var array<string,string>
     */
    public const array LEVEL_MAPPINGS = array(
        LogLevel::ALERT => Logger::ERROR,
        LogLevel::CRITICAL => Logger::ERROR,
        LogLevel::EMERGENCY => Logger::ERROR,
        LogLevel::INFO => Logger::INFO,
        LogLevel::DEBUG => Logger::DEBUG,
        LogLevel::NOTICE => Logger::INFO,
        LogLevel::WARNING => Logger::ERROR
    );

    private Application_Logger $logger;
    private string $logIdentifier;

    /**
     * @param string $logIdentifier The identifier prepended to all log entries.
     */
    public function __construct(string $logIdentifier)
    {
        $this->logIdentifier = $logIdentifier.' | ';
        $this->logger = AppFactory::createLogger();
    }

    public function log($level, $message, array $context = array()) : void
    {
        $level = self::LEVEL_MAPPINGS[$level] ?? Logger::DEBUG;
        $message = $this->logIdentifier.$message;

        if($level === Logger::ERROR) {
            $this->logger->logError($message);
        } else {
            $this->logger->log($message);
        }
    }
}
