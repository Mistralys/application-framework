# AI Module - Server Architecture
_SOURCE: MCP server wrapper and stderr logger_
# MCP server wrapper and stderr logger
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── AI/
                └── Server/
                    └── FrameworkMCPServer.php
                    └── StderrLogger.php

```
###  Path: `/src/classes/Application/AI/Server/FrameworkMCPServer.php`

```php
namespace Application\AI\Server;

use Application\AI\EnvironmentRunner as EnvironmentRunner;
use PhpMcp\Server\Server as Server;
use PhpMcp\Server\Transports\StdioServerTransport as StdioServerTransport;
use Throwable as Throwable;

/**
 * @package AI
 * @subpackage Server
 */
class FrameworkMCPServer
{
	public const SERVER_NAME = 'Framework MCP';
	public const SERVER_VERSION = '1.0.0';
	public const ARG_VERBOSE = '-v';
	public const ARG_VERY_VERBOSE = '-vv';
	public const ARG_LIST_TOOLS = '-list-tools';

	/**
	 * Run the MCP server
	 *
	 * @return never
	 */
	public function run(): never
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/AI/Server/StderrLogger.php`

```php
namespace Application\AI\Server;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use Psr\Log\AbstractLogger as AbstractLogger;

class StderrLogger extends AbstractLogger
{
	public function log($level, $message, array $context = []): void
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 1.5 KB
- **Lines**: 73
File: `modules/ai/architecture-server.md`
