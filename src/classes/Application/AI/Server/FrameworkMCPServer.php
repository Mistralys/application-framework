<?php
/**
 * MCP Server for Framework AI Tools
 *
 * Manages the MCP (Model Context Protocol) server lifecycle including
 * configuration, tool discovery, and stdio communication handling.
 *
 * @package AI
 * @subpackage Server
 */

declare(strict_types=1);

namespace Application\AI\Server;

use Application\AI\EnvironmentRunner;
use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;
use Throwable;

/**
 * @package AI
 * @subpackage Server
 */
class FrameworkMCPServer
{
    public const string SERVER_NAME = 'Framework MCP';
    public const string SERVER_VERSION = '1.0.0';

    public const string ARG_VERBOSE = '-v';
    public const string ARG_VERY_VERBOSE = '-vv';
    public const string ARG_LIST_TOOLS = '-list-tools';

    private bool $verbose = false;
    private bool $veryVerbose = false;
    private bool $listTools = false;
    private string $baseDir;
    private Server $server;

    /**
     * @param string $baseDir The base directory for discovery
     * @param array<string> $argv Command line arguments
     */
    public function __construct(string $baseDir, array $argv = [])
    {
        $this->baseDir = $baseDir;
        $this->parseArguments($argv);
    }

    /**
     * Parse command line arguments to set flags
     *
     * @param array<string> $argv
     */
    private function parseArguments(array $argv): void
    {
        $this->veryVerbose = in_array(self::ARG_VERY_VERBOSE, $argv, true);
        $this->verbose = $this->veryVerbose || in_array(self::ARG_VERBOSE, $argv, true);
        $this->listTools = in_array(self::ARG_LIST_TOOLS, $argv, true);
    }

    /**
     * Check if the list-tools flag is set
     */
    private function isListToolsMode(): bool
    {
        return $this->listTools;
    }

    /**
     * Initialize and configure the server
     */
    private function initializeServer(): void
    {
        $this->debugLog('Building server configuration...');

        $builder = Server::make()
            ->withServerInfo(self::SERVER_NAME, self::SERVER_VERSION);

        if ($this->veryVerbose) {
            $builder->withLogger(new StderrLogger());
        }

        $this->server = $builder->build();

        $this->debugLog('Server built successfully');
    }

    /**
     * Discover available tools in the specified directories
     */
    private function discoverTools(): void
    {
        $this->debugLog('Starting discovery in src/classes...');
        $this->server->discover($this->baseDir, ['src/classes']);
        $toolCount = count($this->server->getRegistry()->getTools());
        $this->debugLog("Discovery complete. Found {$toolCount} tools");
    }

    /**
     * List all discovered tools and exit
     *
     * @return never
     */
    private function listToolsAndExit(): never
    {
        echo 'Discovered Tools:' . PHP_EOL;
        echo PHP_EOL;
        foreach ($this->server->getRegistry()->getTools() as $tool) {
            echo "- {$tool->name}" . PHP_EOL;
        }
        exit(0);
    }

    /**
     * Initialize the application environment
     */
    private function initializeEnvironment(): void
    {
        $this->debugLog('Initializing application environment...');

        EnvironmentRunner::run();

        $this->debugLog('Done.');
    }

    /**
     * Create and start the stdio transport
     */
    private function startStdioTransport(): void
    {
        $this->debugLog('Creating StdioServerTransport...');
        $this->logStdioDebugInfo();

        $transport = new StdioServerTransport();
        $this->debugLog('Transport created');

        // Start Listening (This is a BLOCKING call)
        $this->debugLog('Starting to listen on STDIN/STDOUT...');
        $this->server->listen($transport);
        $this->debugLog('Server listener stopped');
    }

    /**
     * Log debug information about stdio streams
     */
    private function logStdioDebugInfo(): void
    {
        $this->debugLog('STDIN is readable: ' . (is_readable('php://stdin') ? 'yes' : 'no'));
        $this->debugLog('STDOUT is writable: ' . (is_writable('php://stdout') ? 'yes' : 'no'));
        $this->debugLog('STDIN stream type: ' . (is_resource(STDIN) ? get_resource_type(STDIN) : 'not a resource'));
        $this->debugLog('STDOUT stream type: ' . (is_resource(STDOUT) ? get_resource_type(STDOUT) : 'not a resource'));
    }

    /**
     * Log a debug message to stderr if verbose mode is enabled
     */
    private function debugLog(string $message): void
    {
        if (!$this->verbose) {
            return;
        }
        fwrite(STDERR, '[DEBUG] ' . date('H:i:s') . ' ' . $message . PHP_EOL);
    }

    /**
     * Handle fatal errors and exit
     *
     * @param Throwable $e
     * @return never
     */
    private function handleFatalError(Throwable $e): never
    {
        fwrite(STDERR, "[MCP SERVER CRITICAL ERROR]\n");
        fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
        fwrite(STDERR, 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n");
        fwrite(STDERR, $e->getTraceAsString() . "\n");
        exit(1);
    }

    /**
     * Run the MCP server
     *
     * @return never
     */
    public function run(): never
    {
        try {
            $this->debugLog('Starting MCP server...');

            $this->initializeServer();
            $this->discoverTools();

            if ($this->isListToolsMode()) {
                $this->listToolsAndExit();
            }

            $this->initializeEnvironment();
            $this->startStdioTransport();

            exit(0);

        } catch (Throwable $e) {
            $this->handleFatalError($e);
        }
    }
}

