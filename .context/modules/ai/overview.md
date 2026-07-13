# AI Module - Overview
_SOURCE: AI Module README_
# AI Module README
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── AI/
                └── README.md

```
###  Path: `/src/classes/Application/AI/README.md`

```md
# AI Module

Provides the infrastructure for exposing application logic as **AI tools** — callable units that AI agents (or an MCP server) can discover, execute, and cache. The module itself does not define concrete tools; it supplies the contracts, caching layer, MCP server wrapper, and environment bootstrap that consumer applications build on.

## Key Concepts

- **AI Tool** — A class implementing `AIToolInterface` that returns structured data from `execute()`. Each tool has a string ID (via `StringPrimaryRecordInterface`) and declares its own cache strategy.
- **Tool Container** — An abstract base (`BaseAIToolContainer`) that orchestrates tool execution. It boots the application environment, checks the cache, runs the tool, and stores the result. Consumer applications extend this class to register and expose their concrete tools.
- **Cache Strategy** — Pluggable caching via `AICacheStrategyInterface`. Two strategies ship with the module:
  - `FixedDurationStrategy` — caches tool output for a configurable TTL (default 1 hour).
  - `UncachedStrategy` — always re-executes the tool.
- **MCP Server** — `FrameworkMCPServer` wraps the `php-mcp/server` library to discover tool classes, initialize the application environment, and communicate over stdio. It supports verbose/debug logging and a `--list-tools` dry-run mode.
- **Environment Runner** — `EnvironmentRunner` ensures the Application Framework is bootstrapped before any tool runs, using the `AIToolsBootstrap` screen. It is a no-op when the application is already active.

## Folder Structure

| Directory | Responsibility |
|---|---|
| `./` | Tool container base, environment runner, exception |
| `Tools/` | Tool interface and abstract base class |
| `Cache/` | Cache strategy contracts, base implementation, cache-location registration |
| `Cache/Strategies/` | Concrete cache strategies (fixed-duration, uncached) |
| `Cache/Events/` | Event listener that registers the AI cache location with the framework's cache control system |
| `Server/` | MCP server wrapper and PSR-3 stderr logger |

## Integration Points

- **Inbound:** Consumer applications create concrete tools by implementing `AIToolInterface` (or extending `BaseAITool`) and extend `BaseAIToolContainer` to expose them. The `FrameworkMCPServer` auto-discovers tools annotated with `#[McpTool]` in configured source directories.
- **Outbound:** The `RegisterAIIndexCacheListener` hooks into the framework's `CacheControl` event system so that AI tool caches appear in the global cache management UI and can be cleared alongside other caches.
- **Entry point:** The root-level `mcp-server.php` script instantiates `FrameworkMCPServer` and starts the stdio transport.

```