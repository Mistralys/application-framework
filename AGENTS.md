# Agent Guides

The dedicated agent documentation is available under [docs/agents](/docs/agents/readme.md).

## Composer Scripts

| Script | Command | Description |
| --- | --- | --- |
| `analyze` | `composer analyze` | Run PHPStan static analysis. |
| `analyze-save` | `composer analyze-save` | Run PHPStan and save results to `phpstan-result.txt`. |
| `analyze-clear` | `composer analyze-clear` | Clear the PHPStan result cache. |
| `test` | `composer test` | Run all PHPUnit tests. |
| `test-file` | `composer test-file -- <path>` | Run tests in a specific file (no progress output). |
| `test-suite` | `composer test-suite -- <suite>` | Run a named PHPUnit test suite. |
| `test-filter` | `composer test-filter -- <pattern>` | Run tests matching a filter pattern. |
| `test-group` | `composer test-group -- <group>` | Run tests belonging to a specific group. |
