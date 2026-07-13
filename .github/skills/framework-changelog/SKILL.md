---
name: framework-changelog
description: 'Framework: Update changelog.md from all changes since the last tagged version. Use when: preparing a release, documenting changes since the last tag, writing a new changelog entry, updating the developer changelog.'
argument-hint: "Version number and short title (e.g. 7.3.4 - My Feature)"
---

# Changelog Updater

Update `changelog.md` with all changes made since the last Git tag by dispatching to the **Changelog Curator** subagent.

## When to Use

- When preparing a new release and `changelog.md` needs a new entry
- When new commits have accumulated since the last tagged version
- When the changelog is out of sync with the current codebase state

## Inputs

The user provides a **version number and short title** (e.g. `7.3.4 - My Feature`).

The **scope is automatic:** all commits since the last Git tag. Detect it by running:

```bash
git describe --tags --abbrev=0         # → last tag (e.g. 7.3.3)
git log --oneline <last-tag>..HEAD     # → commits in scope
```

> **Note:** Tags in this project use bare semver without a `v` prefix (e.g. `7.3.3`).
> The changelog heading uses a `v` prefix (e.g. `## v7.3.4 - My Feature`).

## Procedure

### Step 1: Detect scope

Run the two commands above to determine the last tag and retrieve the list of commits since that tag.

### Step 2: Dispatch to Changelog Curator

Invoke the **Changelog Curator v1.1.1** subagent with the following prompt:

```
Mode: Generate
Target: changelog.md
Version: v{version and title from user}
Scope: All commits since the last Git tag ({last-tag}..HEAD)

Commits:
{paste full output of: git log --oneline <last-tag>..HEAD}

Project-specific notes:
- Category examples: API, DBHelper, Tests, Docs, Composer, Wizard, LookupItems, etc.
- Insert the new version block at the top of the file, above all existing entries.
```

### Step 3: Verify

After the Changelog Curator writes the entry:

1. Confirm the new version heading (`## vX.Y.Z`) appears at the top of `changelog.md`.
2. Confirm all commits in scope are reflected (directly or combined) in the new entry.
3. Confirm the version string matches the one provided by the user.
4. Report how many commits were processed and how many changelog entries were written.

## Key Rules

- **Delegate fully.** Do not write changelog entries manually — invoke the Changelog Curator agent.
- **Tags have no `v` prefix; changelog headings do.** Tag `7.3.3` → heading `## v7.3.4`.
- **Insert at the top.** The new entry goes above all existing entries, not at the bottom.
- **Merge-commits and maintenance commits** (e.g. `Merge branch ...`) may be skipped or collapsed at the Curator's discretion.
