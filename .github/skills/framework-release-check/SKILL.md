---
name: framework-release-check
description: 'Framework: Pre-release readiness check. Run all checks before creating and pushing a Git tag. Use when: preparing a release, verifying release readiness, checking if the project is ready to publish.'
---

# Pre-Release Check

Validates that the framework is ready for release. Run all steps in order and report the result of each check before continuing to the next. **All checks must pass before creating the Git tag.**

---

## Release Flow Context

Releases follow this sequence:

1. **Update changelog** (use the `changelog` skill): write the `changelog.md` entry for the new version.
2. **Pre-release (this skill):** All checks below must pass.
3. **Tag and publish:** `git tag X.Y.Z && git push --tags` — the GitHub Actions release workflow creates the GitHub Release automatically from `changelog.md`.

> Tags use bare semver without a `v` prefix (e.g., `7.3.4`). Changelog headings use a `v` prefix (e.g., `## v7.3.4 - My Feature`).  
> `composer.json` has no `version` field — versioning is entirely via Git tags.

---

## Procedure

### 0. Branch check

```bash
git branch --show-current
```

The pre-release check must be run on the **main branch**. Feature branches must never receive `.context/` commits or a release tag.

**Pass:** Current branch is `main` (or the project's canonical default branch).  
**Fail:** Any other branch — merge or rebase to `main` before proceeding.

---

### 1. Changelog version ahead check

Extract the topmost version from `changelog.md` (the first line matching `## vX.Y.Z`):

```bash
grep -m1 '^## v' changelog.md
```

Get the last Git tag:

```bash
git describe --tags --abbrev=0
```

Parse both as semver and verify the changelog version is **strictly greater** than the last tag.

**Pass:** Changelog version > last Git tag (semver).  
**Fail:** They are equal (changelog not updated yet) or the last tag is ahead (version bumped without a changelog entry).

---

### 2. Git tag gap check

```bash
git tag --sort=-v:refname | head -10
```

Confirm that no Git tag already exists for the changelog top version (e.g., if changelog says `v7.3.4`, there must be no tag `7.3.4`). A matching tag means this version was already released.

**Pass:** No Git tag exists for the changelog top version.  
**Fail:** Matching tag found — the changelog entry describes an already-released version.

---

### 3. Composer validate

```bash
composer validate
```

Validates `composer.json` for structural correctness. `--strict` is intentionally **not** used because this project uses unbound version constraints (`>=X.Y.Z`) for all `mistralys/*` internal packages by design.

**Pass:** Exit 0, no errors.  
**Fail:** Any structural validation error reported.

---

### 4. PHPStan

```bash
composer analyze
```

Runs PHPStan against the full codebase at the configured level. Do **not** invoke `vendor/bin/phpstan` directly — the Composer script enforces the required `--memory-limit=900M` flag.

**Pass:** Exit 0, no errors.  
**Fail:** Any PHPStan error reported. Fix all errors before proceeding — pre-existing warnings in unmodified files are still blockers for release.

---

### 5. Full test suite

```bash
composer test
```

Runs the complete PHPUnit suite (`Framework Tests`). All tests must pass.

> **Note:** This is the only context where running `composer test` (the full suite, ~155 unit test files + 2 integration test files) is appropriate. During development, use scoped commands instead.

**Pass:** Exit 0, zero failures and zero errors. Skipped tests are acceptable.  
**Fail:** Any test failure or error. Integration tests may legitimately fail in environments without a configured database — run `composer seed-tests` first if that is the case, or document which integration tests were skipped and why.

---

### 6. Generated documentation freshness check

> **Important:** `.context/` files must **never** be committed on a feature branch. This step is only valid on `main`. Confirm step 0 passed before running this step.

Run the documentation generator and check whether it produces uncommitted changes:

```bash
composer build-docs
git status --short
git diff --name-only
```

Generated documentation under `.context/` and `docs/agents/project-manifest/` must be up to date. If `build-docs` modifies files, commit them to `main` before the release tag is created.

**Pass:** No uncommitted changes after `build-docs`.  
**Fail:** Modified or untracked files remain — commit them on `main` and re-run checks from step 5.

---

### 7. Git working tree

```bash
git status --short
git diff --name-only
```

The working tree must be **fully clean** before creating the release tag. Commit or stash any remaining changes.

**Pass:** `git status --short` produces no output.  
**Fail:** Any uncommitted changes, untracked files, or stashed changes that belong to this release.

---

## Pass Criteria

| Check | Expected result |
|-------|-----------------|
| Branch | Current branch is `main` |
| Changelog version ahead | Changelog top version > last Git tag (semver) |
| Git tag gap | No existing tag for the changelog top version |
| Composer validate | Exit 0, no structural errors (unbound version constraints are by design — `--strict` is not used) |
| PHPStan | Exit 0, no errors |
| Full test suite | All tests pass, exit 0 |
| Generated docs freshness | No uncommitted changes after `composer build-docs` (on `main` only) |
| Git working tree | No uncommitted changes (`git status --short` empty) |

All 8 checks must pass before creating the release tag.

---

## Tagging and Publishing

Once all checks pass:

```bash
git tag X.Y.Z          # bare semver, no v prefix — e.g. git tag 7.3.4
git push --tags
```

The GitHub Actions `Release` workflow triggers on the pushed tag, extracts release notes from `changelog.md`, and creates the GitHub Release automatically.
