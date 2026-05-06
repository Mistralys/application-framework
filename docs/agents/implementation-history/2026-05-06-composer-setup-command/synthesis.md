# Synthesis Report — Interactive Developer Menu & `composer setup` Command

**Project:** `2026-05-06-composer-setup-command`
**Date:** 2026-05-06
**Status:** ✅ COMPLETE (4/4 Work Packages, 18 pipeline stages passed, 0 failures)

---

## Executive Summary

This session delivered a complete interactive CLI developer workflow for the Application Framework, modelled on the existing pattern from the HCP Editor project. Four work packages were shipped across a full pipeline stack (implementation → QA → security audit → code review → documentation) with zero rework cycles and no blocking issues found.

**What was built:**

- **`tools/include/cli-utilities.php`** — a shared CLI I/O library (`writeln`, `color`, `prompt`, `promptPassword`) with `function_exists()` guards, safe for re-inclusion, and guarded with a SIGINT-aware terminal echo restore.
- **`tools/setup-local.php`** — a standalone, bootstrap-free interactive setup script that prompts for DB and UI settings, generates `test-db-config.php` and `test-ui-config.php` from `.dist.php` templates, creates the database if absent, imports `tests/sql/testsuite.sql`, and runs `composer seed-tests`. Fully idempotent: re-running shows existing values as defaults.
- **`composer setup`** — a new entry in `composer.json` scripts section that invokes `tools/setup-local.php` directly.
- **`tools/menu.php`** + **`menu.sh`** / **`menu.cmd`** — an interactive numbered developer menu (6 options + exit) with automatic `vendor/` pre-flight, safe shell argument escaping, and a looping UX. Platform-specific launchers are thin 2-line wrappers.
- **README.md, AGENTS.md, `tools/include/cli-utilities.php` docblocks** — fully updated to document the new tools, launcher usage, function references, and configuration file commit policy.
- **Context regeneration** — `ctx generate` ran cleanly (exit 0, 91 documents regenerated) after each documentation pass; `context.yaml` updated to source the root `README.md`.

---

## Metrics

| Work Package | Pipeline Stages | Tests Passed | Tests Failed | Security Issues | Rework Cycles |
|---|---|---|---|---|---|
| WP-001 – CLI Utilities Library | impl → qa → review → docs | 7 | 0 | — | 0 |
| WP-002 – Setup Script & `composer setup` | impl → qa → **security-audit** → review → docs | 24 | 0 | 0 Critical / 0 High / 3 Medium / 3 Low | 0 |
| WP-003 – Interactive Developer Menu | impl → qa → review → docs | 7 | 0 | — | 0 |
| WP-004 – AGENTS.md Documentation | docs | — | — | — | 0 |
| **Total** | **18 stages** | **38** | **0** | **0 Critical / 0 High** | **0** |

All 25 acceptance criteria across 4 work packages were marked met.

---

## Security Findings (WP-002)

The security audit returned **0 Critical, 0 High** findings. All three Medium items were reviewed and accepted as contextually appropriate for a local-only developer CLI tool with no remote attack surface:

| Severity | Category | Finding | Disposition |
|---|---|---|---|
| Medium | A03 Injection (SQL) | `$dbName` interpolated into backtick-quoted `USE`/`CREATE DATABASE` statements in `ensureDatabase()` | Accepted — local CLI, trusted developer input. Harden with `/^[a-zA-Z0-9_]+$/` whitelist if ever used in CI. |
| Medium | A02 Cryptographic Failures | `TESTSUITE_DB_PASSWORD` stored as plaintext in generated `test-db-config.php` | Accepted — local dev only, file is in `.gitignore`. README and Developer Tools docs explicitly warn against committing it. |
| Medium | A04 Insecure Design | `shell_exec('stty ...')` used for terminal echo suppression in `promptPassword()` | Accepted — hardcoded literal args only, no injection vector. SIGINT handler correctly restores terminal state. |

---

## Fix-Forward Changes Applied

The Code Review pipeline for WP-002 applied three non-behavioral hardening edits (all QA-verified clean after application):

1. **`tools/include/cli-utilities.php`** — Added a 6-line comment above the `$sttyAvailable` assignment clarifying when `shell_exec` returns null vs. the primary Windows gate via `DIRECTORY_SEPARATOR`, preventing future maintainers from removing the null-check as dead code.
2. **`tools/setup-local.php` `replaceConfigConstant()`** — Added an inline comment on `addslashes()` documenting which characters are escaped and the scope of the assumption, guarding against future value-type widening without awareness of the escaping constraint.
3. **`tools/setup-local.php` `generateDbConfig()` / `generateUiConfig()`** — Added `file_put_contents()` return-value checks: silent `false` returns now produce a red `ERROR` message naming the target file, a hint about directory writability, and `exit(1)`. Previously, write failures produced only a PHP warning with no user-facing feedback.

---

## Documentation Artifacts

| File | Changes |
|---|---|
| `README.md` | New **Developer Tools** section with CLI Utility Library reference table, Setup Script subsection (`.gitignore` warnings, idempotency notes), and Developer Menu subsection (options table, launchers table, test-filter sub-prompt description) |
| `README.md` (WP-002) | Installation section updated: `composer setup` as primary flow, old manual steps preserved in a `<details>` collapse block; Composer Commands section updated with new `### Setup` subsection |
| `AGENTS.md` | Section 9 populated with `### Interactive Developer Menu` and `### Local Environment Setup` subsections covering launch commands, dispatch table, and full setup behaviour |
| `tools/include/cli-utilities.php` docblock | Forward-references to `menu.php` / `setup-local.php` qualified as forward-looking (WP-001), then de-qualified once both scripts existed (WP-003) |
| `context.yaml` + `.context/` regeneration | `README.md` added as source in `framework-core-system-overview`; `ctx generate` passed on every documentation cycle |

---

## Strategic Recommendations

### Gold Nuggets

1. **The `vendor/` pre-flight pattern is reusable.** `tools/menu.php`'s `ensureVendorInstalled()` check is a clean, self-contained pattern. Any future CLI tool that may be run before `composer install` can replicate this check to provide a zero-friction first-run experience.

2. **SQL schema idempotency should be verified.** `ensureDatabase()` always imports `testsuite.sql` on every setup run (even when the DB already exists). This is only safe if all DDL statements in `testsuite.sql` use `CREATE TABLE IF NOT EXISTS` and equivalent guards. This was flagged by QA and the Reviewer but was out of scope for this session — a dedicated audit of `testsuite.sql` DDL idempotency is recommended before the setup script is distributed to a team with an existing populated database.

3. ~~**Database name input should be hardened.**~~ ✅ **IMPLEMENTED.** Added a `/^[a-zA-Z0-9_]+$/` guard with a re-prompt loop in `collectDatabaseSettings()` in `tools/setup-local.php`. Invalid names print a red error and re-prompt until a valid identifier is entered.

4. ~~**Port input validation gap.**~~ ✅ **IMPLEMENTED.** Replaced the silent `(string)(int)` cast with a `ctype_digit()` guard and a re-prompt loop in `collectDatabaseSettings()` in `tools/setup-local.php`. Non-numeric port strings now print a red error and re-prompt; empty input still resolves to `null`.

5. **PHPUnit test suite coverage opportunity.** Neither `tools/include/cli-utilities.php` nor `tools/setup-local.php` have formal PHPUnit unit tests (vendor directory was not present in the sandbox). As standalone CLI utilities with no framework bootstrap dependency, they are straightforward to unit-test in isolation. Adding tests for `color()`, `writeln()`, `replaceConfigConstant()`, and `extractConstantValue()` would harden the library against future regressions.

6. **PDO multi-statement import robustness.** `ensureDatabase()` uses a single `PDO::exec()` call to import the entire `testsuite.sql` schema. If `MYSQL_ATTR_MULTI_STATEMENTS` is not enabled on the driver, a PDO error on any intermediate statement will silently skip subsequent statements. A statement-by-statement import loop (split on `;\n`) with per-statement error reporting would provide clearer diagnostics and safer behaviour in edge cases.

---

## Next Steps for Planner / Manager

| Priority | Recommendation |
|---|---|
| High | Audit `tests/sql/testsuite.sql` DDL for idempotency (`IF NOT EXISTS` guards) before distributing `composer setup` to a team with an existing local database. |
| ~~Medium~~ | ✅ DONE — Add a database name whitelist validator in `collectDatabaseSettings()` (resolves the Security Auditor's Medium SQL injection finding). |
| ~~Medium~~ | ✅ DONE — Add port input validation in `collectDatabaseSettings()` using `ctype_digit()` with a re-prompt on invalid input. |
| Low | Add PHPUnit unit tests for `cli-utilities.php` functions and the `replaceConfigConstant()` / `extractConstantValue()` helpers in `setup-local.php`. |
| Low | Harden `ensureDatabase()` SQL import with statement-by-statement execution and per-statement error reporting. |
| Low | Evaluate replacing `shell_exec('stty ...')` with the PHP `readline` extension or `/dev/tty` stream approach if the setup script is ever adapted for CI/CD pipelines. |
