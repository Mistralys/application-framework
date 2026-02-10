#!/usr/bin/env php
<?php
/**
 * Endpoint to run an MCP server over stdio (stdin/stdout)
 * for framework AI tools.
 *
 * ## Command line parameters
 *
 * - `-v` : Enable verbose debug logging to stderr
 * - `-vv` : Enable very verbose debug logging to stderr (includes MCP server logging)
 * - `-list-tools` : Dump discovered tools and exit
 *
 * ## Configuration
 *
 * The server is also registered for CTX use via the {@see context.yaml} file.
 *
 * @package AI
 * @subpackage Server
 * @see FrameworkMCPServer
 */

declare(strict_types=1);

chdir(__DIR__);

// Show all errors except deprecated warnings
error_reporting(E_ALL & ~E_DEPRECATED);

// Ensure no output buffering interferes with stdio
while (ob_get_level() > 0) {
    ob_end_clean();
}

require_once __DIR__ . '/vendor/autoload.php';

use Application\AI\Server\FrameworkMCPServer;

// Create and run the MCP server
new FrameworkMCPServer(__DIR__, $argv ?? [])->run();
