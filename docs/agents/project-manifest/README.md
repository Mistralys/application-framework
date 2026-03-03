# Project Manifest

Canonical "Source of Truth" for AI agent sessions working with the Application Framework codebase.

> **Important:** Due to the size of this codebase (1,545+ class files), a single monolithic manifest
> cannot cover the full API surface and data flows. This manifest handles **cross-cutting concerns**
> (conventions, testing, orientation). Module-specific architecture and API documentation is maintained
> separately in the `/.context/` folder via the CTX Generator — see the Context Documentation section below.

---

## Sections

| Section | File | Description |
|---|---|---|
| **Context Documentation** | [context-documentation.md](context-documentation.md) | How codebase knowledge is structured: the two-tier manifest + CTX module docs strategy, `.context/` folder layout, and agent orientation guide. **Read this first.** |
| **Constraints & Conventions** | [constraints.md](constraints.md) | Coding rules, naming conventions, architectural patterns, and non-obvious gotchas. |
| **Testing** | [testing.md](testing.md) | Test infrastructure, suites, base classes, configuration, and execution commands. |
| **Modules Overview** | [modules-overview.md](modules-overview.md) | Auto-generated overview of all modules in the codebase. Lists module IDs, labels, descriptions, source paths, CTX doc paths, and inter-module relationships. Regenerate with `composer build-dev`. |
| **Module Keyword Glossary** | [module-glossary.md](module-glossary.md) | Auto-generated keyword-to-module lookup for opaque domain terms. Regenerate with `composer build-dev`. |
