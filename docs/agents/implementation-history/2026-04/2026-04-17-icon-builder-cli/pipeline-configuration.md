# Pipeline Configuration

## Per-WP Stage Configuration

| WP | active_pipeline_stages | Rationale |
|----|------------------------|-----------|
| WP-1 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — new value object, no security surface |
| WP-2 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — singleton loads internal JSON from hardcoded constant-based paths, no user-supplied input |
| WP-3 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — build-time data object, no security surface |
| WP-4 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — parses internal JSON files, no external input |
| WP-5 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — abstract base class, no security surface |
| WP-6 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — concrete renderers generating code from trusted internal data |
| WP-7 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — build-time orchestrator writing to known files from trusted JSON, no runtime exposure |
| WP-8 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — wiring a method call into existing build sequence |
| WP-9 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — composer.json script registration, additive (not breaking) |
| WP-10 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — one-time marker insertion in PHP file |
| WP-11 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — one-time marker insertion in JS file |
| WP-12 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — wiring method calls into HCP Editor build sequence |
| WP-13 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — composer.json script registration, additive (not breaking) |
| WP-14 | `["qa", "code-review"]` | Verification-only — runs autoload dump and verifies idempotency/integration; no source code changes; all referenced classes exist from WP-1–WP-13 |
| WP-15 | `["qa", "code-review"]` | Verification-only — runs PHPStan static analysis; no source code changes; all referenced classes exist from prior WPs |
| WP-16 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — creates a new test file with test logic |

## Guardrail Notes

1. **WP-14 and WP-15 use the verification-only chain `["qa", "code-review"]`.** These WPs perform no source code modifications — they run tooling (`composer dump-autoload`, `composer analyze`) and verify output. All classes and methods they reference are created by their dependency WPs (WP-1–WP-13), which must complete first per the dependency graph. This satisfies the pre-requisite for the verification-only chain.

2. **No WPs require `security-audit`.** All file I/O in this project is build-time only, reading from internal JSON files at hardcoded paths derived from bootstrap constants (`APP_ROOT`, `APP_INSTALL_FOLDER`). No WP handles user-supplied input, authentication, external APIs, cryptography, or SQL. The generated code is written to known target files during `composer build`, not at runtime.

3. **No WPs require `release-engineering`.** The migration adds new classes and Composer scripts but does not break any existing public API, does not produce a standalone release artifact, and does not require a version bump per-WP. The overall release (version bump, changelog) is expected to be handled outside this WP set.
