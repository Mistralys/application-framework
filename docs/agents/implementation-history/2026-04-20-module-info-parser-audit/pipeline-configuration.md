# Pipeline Configuration

## Per-WP Stage Configuration

| WP | active_pipeline_stages | Rationale |
|----|------------------------|-----------|
| WP-1 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — replaces `echo` with `BuildMessages` in an internal build-tool class. No security surface, no release artifact. |
| WP-2 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — replaces inline YAML parsing with shared parser delegation. No security surface, no release artifact. |
| WP-3 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — extracts existing logic into a new reusable class. No security surface, no release artifact. |
| WP-4 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — cross-project file move with namespace updates. No security surface, no release artifact. |
| WP-5 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — dead code removal. No security surface, no release artifact. |
| WP-6 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — new workflow class using only internal build-tool components. High complexity but no security surface (no user input, no auth, no external APIs). No release artifact. |
| WP-7 | `["implementation", "qa", "code-review", "documentation"]` | Standard code change — rewrites class to thin subclass of framework base. No security surface, no release artifact. |
| WP-8 | `["qa", "code-review"]` | Verification-only — runs `composer build` / `composer build-dev` and diffs generated files. No code or documentation changes. All referenced methods exist after WP-1–WP-7 complete. |

## Guardrail Notes

- **WP-8 uses verification-only chain:** This is intentional. WP-8 makes no code or documentation changes — it only runs builds and validates output. All methods and classes it exercises will exist because it depends on WP-1 through WP-7. No PM review needed.
- **No `security-audit` assigned to any WP:** All 8 WPs modify internal Composer build scripts for documentation generation. None handle user input, authentication, authorization, external API calls, file uploads, SQL, or secrets. No security surface exists.
- **No `release-engineering` assigned to any WP:** No WP produces a versioned release artifact, introduces a breaking public API change, or requires a version bump. These are internal build-tool refactorings with no user-facing migration steps.
