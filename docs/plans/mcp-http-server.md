# MCP HTTP Server Implementation Plan

**Created:** January 17, 2026  
**Status:** Planning Phase  
**Context:** Making the MCP server compatible with HTTP requests for deployment in company intranet cluster

## Overview

The current MCP server uses `StdioServerTransport` for CLI-based communication. This plan outlines the requirements to add HTTP transport capability, enabling deployment as a web service in the company's intranet cluster infrastructure.

## Key Requirements

### Authentication
- **SSO Integration:** Full integration with existing CAS (Central Authentication Service) SSO system
- **No Fallback:** Must fully rely on SSO - no API key or alternative authentication mechanisms
- **User Identification:** Users will be identified by the SSO implementation (handled separately)
- **Session Isolation:** Use separate session name (`appframework_mcp`) to prevent conflicts with regular web UI

### Transport & Response Modes
- **Per-Tool Configuration:** Tool response modes (JSON vs SSE streaming) configured via `AIToolInterface` class methods
- **Default Mode:** Most tools will use JSON mode (`enableJsonResponse: true`)
- **Caching System:** Existing caching system available; only select tools affected by response mode
- **MCP Protocol Compliance:** All responses must follow MCP protocol standards (JSON-RPC 2.0 error format)

### Infrastructure
- **Deployment Architecture:** To be determined with operations team (standalone port vs reverse proxy subdirectory)
- **Reverse Proxy:** Company has proxy infrastructure for such cases; specific setup TBD
- **Session Management:** Independent from web application sessions; no conflicts with CAS-based user sessions

## Implementation Steps

### 1. Refactor Server Architecture with Base Class

**Files:**
- `src/classes/Application/AI/Server/BaseMCPServer.php` - New base class with common functionality
- `src/classes/Application/AI/Server/FrameworkMCPServer.php` - Refactor to extend base class (stdio only)
- `src/classes/Application/AI/Server/FrameworkMCPServerHTTP.php` - New HTTP server implementation

**Base Class (`BaseMCPServer`):**
- Tool discovery and registration logic
- Logging configuration
- Command-line argument parsing (verbose flags, list-tools, etc.)
- Common MCP server setup and configuration
- Abstract methods for transport-specific initialization

**Stdio Server (`FrameworkMCPServer`):**
- Extends `BaseMCPServer`
- Implements stdio transport using `StdioServerTransport`
- Maintains current CLI-focused behavior
- No authentication requirements

**HTTP Server (`FrameworkMCPServerHTTP`):**
- Extends `BaseMCPServer`
- Implements HTTP transport using `StreamableHttpServerTransport`
- HTTP configuration properties (host, port, SSL context)
- Per-tool response mode detection (via `AIToolInterface`)
- SSO authentication middleware integration
- CORS and security configuration
- Health check endpoints

### 2. Create Standalone `MCPServerWebBootstrap` Class

**File:** `src/classes/Application/Bootstrap/Screen/MCPServerWebBootstrap.php`

- New bootstrap separate from `AIToolsBootstrap` with **no inheritance**
- Enable UI mode (not script mode, unlike CLI bootstrap)
- Enable authentication (not disabled)
- Create full environment with session support
- Use distinct session name: `appframework_mcp`
- Provide authenticated `Application_User` context for tool execution

**Key Differences from `AIToolsBootstrap`:**
```php
// AIToolsBootstrap (CLI - current)
$this->enableScriptMode();
$this->disableAuthentication();

// MCPServerWebBootstrap (HTTP - new)
// UI mode by default
// Authentication enabled by default
// Session support enabled
```

### 3. Extend `AIToolInterface` with Response Mode Method

**File:** `src/classes/Application/AI/AIToolInterface.php`

- Add method for response mode selection (e.g., `getResponseMode(): string` or `useStreamingResponse(): bool`)
- Implement in base tool class with default JSON mode
- Allow individual tool classes to override for SSE streaming when needed
- Integrate with transport configuration to determine per-tool response format

### 4. Implement SSO-Only Authentication Middleware

**Requirements:**
- Validate SSO tokens/credentials in HTTP headers
- Map validated SSO identity to framework's `Application_User` instances
- Provide user context to MCP tools
- Return MCP-compliant JSON-RPC error responses on authentication failure
- HTTP 401 status with no fallback authentication mechanism

**Integration Point:** User will handle the specific SSO integration details

### 5. Create Dedicated Entry Point `mcp-server-http.php`

**File:** `mcp-server-http.php` (alongside existing `mcp-server.php`)

- Bootstrap via `MCPServerWebBootstrap` instead of `AIToolsBootstrap`
- Support configuration via environment variables (host, port, SSL, session name)
- Include health check endpoints (`/health`, `/ready`) separate from MCP protocol paths (`/mcp`)
- Support verbose logging flags (`-v`, `-vv`) similar to stdio mode
- Follow MCP protocol standards for all responses

### 6. Implement Session/Context Management with EventStore

**Requirements:**
- Use php-mcp/server's `EventStoreInterface` with cache backend
  - Redis for cluster horizontal scaling
  - File-based for single node testing
- Support `stateless: true` mode for simpler cluster deployments
- Store MCP session state independently from framework's `Application_Session`
- Configure CORS via `Application_CORS` class
- Add per-user rate limiting for security
- Ensure all error responses follow MCP JSON-RPC error format

## Technical Architecture

### Current Implementation (Stdio)
```
mcp-server.php
  └─> AIToolsBootstrap
       ├─> enableScriptMode()
       ├─> disableAuthentication()
       └─> FrameworkMCPServer
            └─> StdioServerTransport (stdin/stdout)
```

### Proposed Implementation (HTTP)
```
mcp-server-http.php
  └─> MCPServerWebBootstrap (standalone, no inheritance)
       ├─> UI mode enabled
       ├─> Authentication enabled
       ├─> Session: "appframework_mcp"
       └─> FrameworkMCPServerHTTP (new class)
            ├─> extends BaseMCPServer
            ├─> SSO Authentication Middleware
            ├─> StreamableHttpServerTransport
            │    └─> Per-tool response mode (via AIToolInterface)
            └─> EventStore (Redis/File)
```

### Class Hierarchy
```
BaseMCPServer (abstract)
  ├─> Common functionality
  │    ├─> Tool discovery
  │    ├─> Logging setup
  │    ├─> CLI argument parsing
  │    └─> Server configuration
  │
  ├─> FrameworkMCPServer (stdio)
  │    └─> StdioServerTransport
  │
  └─> FrameworkMCPServerHTTP (http)
       ├─> StreamableHttpServerTransport
       ├─> SSO Authentication
       ├─> CORS Configuration
       └─> Health Check Endpoints
```

## Open Questions

### 1. SSO Token Validation Mechanism

**Question:** What format will the SSO implementation provide?

**Options:**
- JWT tokens in Authorization header
- Session cookies
- CAS service tickets in headers
- Custom bearer tokens

**Impact:** Determines validation logic and whether CAS ticket validation endpoint integration is needed without triggering redirect flow.

**Decision:** Pending - user will handle SSO integration specifics

---

### 2. Tool Response Mode API Design

**Question:** What should the `AIToolInterface` method return?

**Options:**
- Enum values: `ResponseMode::JSON`, `ResponseMode::SSE`
- Boolean flag: `useStreamingResponse(): bool`
- String constants: `'json'`, `'sse'`

**Impact:** API clarity and alignment with php-mcp/server's `enableJsonResponse` parameter and framework coding patterns.

**Reference:** [Coding Patterns](../agents/coding-patterns.md)

**Decision:** Pending

---

### 3. Deployment Architecture Flexibility

**Question:** Should the code support both deployment patterns or optimize for one?

**Scenarios:**
1. **Standalone service:** Separate port (e.g., `:8080`)
   - Direct MCP path: `/mcp/sse`, `/mcp/message`
   - Health checks: `/health`, `/ready`
   
2. **Reverse proxy subdirectory:** Main web server with subdirectory
   - Proxied MCP path: `/mcp/*`
   - Health checks: `/mcp/health`, `/mcp/ready` or separate

**Implementation:** Use configurable `mcpPathPrefix` parameter

**Decision:** Support both patterns; operations team to decide final approach

---

### 4. Health Check Depth

**Question:** How comprehensive should health/readiness checks be?

**Options:**

**Liveness Check (`/health`):**
- Minimal: HTTP listener is running
- Basic: Server process is responsive

**Readiness Check (`/ready`):**
- Basic: All above + dependencies available
- Comprehensive: Database connectivity + cache availability + SSO reachability + tool discovery status

**Impact:** Load balancer behavior, deployment complexity, debugging capabilities

**Consideration:** Kubernetes-style deployments distinguish liveness (restart if failing) vs readiness (remove from load balancing)

**Decision:** Pending - depends on operations team's monitoring requirements

---

### 5. Session Name Configuration Strategy

**Question:** Should the MCP session name be configurable?

**Current Requirement:** Use `appframework_mcp` to prevent conflicts

**Options:**
1. **Hardcoded:** Always `appframework_mcp`
   - Simpler implementation
   - Consistent across environments
   
2. **Configurable:** Environment variable `MCP_SESSION_NAME`
   - Supports multiple MCP server instances
   - Environment-specific names (dev/staging/production)
   - More flexible for testing

**Decision:** Pending

---

### 6. HTTP Configuration Source Priority

**Question:** How should configuration be loaded and prioritized?

**Options:**
1. Environment variables only (12-factor app style)
2. Config file only (traditional)
3. Hybrid with priority: CLI args > env vars > config file > defaults

**Example Variables:**
- `MCP_HOST` / `MCP_PORT`
- `MCP_SSL_CERT` / `MCP_SSL_KEY`
- `MCP_SESSION_NAME`
- `MCP_DEFAULT_RESPONSE_MODE`
- `MCP_ENABLE_CORS`
- `MCP_RATE_LIMIT_PER_USER`

**Decision:** Pending - may depend on operations team's deployment practices

## Related Files

### Existing Files to Modify
- `src/classes/Application/AI/Server/FrameworkMCPServer.php` - Refactor to extend `BaseMCPServer`
- `src/classes/Application/AI/AIToolInterface.php` - Add response mode method

### New Files to Create
- `src/classes/Application/AI/Server/BaseMCPServer.php` - Base class with common functionality
- `src/classes/Application/AI/Server/FrameworkMCPServerHTTP.php` - HTTP server implementation
- `src/classes/Application/Bootstrap/Screen/MCPServerWebBootstrap.php` - HTTP bootstrap
- `mcp-server-http.php` - HTTP entry point
- `src/classes/Application/AI/Server/MCPAuthenticationMiddleware.php` - SSO auth (placeholder for user implementation)

### Reference Files
- `src/classes/Application/Bootstrap/Screen/AIToolsBootstrap.php` - CLI bootstrap (for comparison)
- `src/classes/Application/Bootstrap/Screen/APIBootstrap.php` - API bootstrap pattern
- `src/classes/Application/Session/AuthTypes/CAS.php` - CAS authentication implementation
- `src/classes/Application/CORS.php` - CORS handling
- `vendor/php-mcp/server/src/Transports/StreamableHttpServerTransport.php` - Transport implementation
- `vendor/php-mcp/server/README.md` - MCP server documentation

## Dependencies

### PHP Libraries (Already Available)
- `php-mcp/server` >= 3.3.0 - Provides `StreamableHttpServerTransport` and `EventStoreInterface`
- `apereo/phpcas` 1.6.2 - CAS authentication (already integrated)
- `react/http` - ReactPHP HTTP server (dependency of php-mcp/server)

### Infrastructure (TBD)
- Redis server (recommended for cluster deployment)
- Reverse proxy (nginx/HAProxy) - operations team to configure
- SSL certificates (if not handled by proxy)
- Load balancer configuration

## Next Steps

1. **Finalize SSO Integration Approach** - User to design SSO token validation mechanism
2. **Design Tool Response Mode API** - Choose enum/boolean/string approach for `AIToolInterface`
3. **Meet with Operations Team** - Determine deployment architecture (standalone vs reverse proxy)
4. **Decide Health Check Depth** - Define monitoring requirements with operations
5. **Choose Configuration Strategy** - Environment variables, config file, or hybrid
6. **Refactor Server Architecture** - Extract common functionality to `BaseMCPServer` base class
7. **Update Stdio Server** - Refactor `FrameworkMCPServer` to extend base class
8. **Implement HTTP Server** - Create `FrameworkMCPServerHTTP` with HTTP transport
9. **Create Bootstrap Class** - Implement `MCPServerWebBootstrap` with session support
10. **Add Entry Point** - Create `mcp-server-http.php` with configuration loading
11. **Testing Strategy** - Unit tests, integration tests, cluster deployment tests

## Notes

- Current `mcp-server.php` uses CLI-only initialization path via `AIToolsBootstrap`
- Existing CAS integration in `Application_Session_AuthTypes_CAS` is for interactive web sessions (redirect flow)
- MCP HTTP authentication must work without triggering CAS redirects
- Framework already has infrastructure for API endpoints (`APIBootstrap`), AJAX (`Application_Bootstrap_Screen_Ajax`), which can serve as patterns
- All errors must follow JSON-RPC 2.0 format per MCP specification

## Success Criteria

- [ ] MCP server can run in both stdio mode (CLI) and HTTP mode (web)
- [ ] HTTP mode authenticates users via SSO without fallback mechanisms
- [ ] Each tool can specify its preferred response mode (JSON vs SSE)
- [ ] Sessions are isolated between MCP HTTP and regular web UI
- [ ] Deployment supports both standalone and reverse proxy architectures
- [ ] All responses comply with MCP protocol standards
- [ ] Health checks support load balancer integration
- [ ] Horizontal scaling possible via stateless mode with shared cache
- [ ] No conflicts with existing application functionality

---

**Last Updated:** January 19, 2026  
**Next Review:** After meetings with operations team and SSO integration design
