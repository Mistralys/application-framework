<?php
/**
 * Logger that writes to stderr for MCP server diagnostics
 *
 * @package Application
 * @subpackage AI\Server
 */

declare(strict_types=1);

namespace Application\AI\Server;

use AppUtils\ConvertHelper\JSONConverter;
use Psr\Log\AbstractLogger;

class StderrLogger extends AbstractLogger
{
    public function log($level, $message, array $context = []): void
    {
        fwrite(
            STDERR,
            sprintf(
                "[%s][%s] %s %s\n",
                date('Y-m-d H:i:s'),
                strtoupper($level),
                (string)$message,
                empty($context) ? '' : JSONConverter::var2json($context)
            )
        );
    }
}
