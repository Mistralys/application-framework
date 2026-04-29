# Module: Countries

## Purpose

Country management for the Application Framework. Provides the `Application_Countries` collection and `Application_Countries_Country` record following the DBHelper Collection/Record pattern, handling country creation, lookup by ID or ISO code, filtering, and locale code parsing. Countries are identified by their two-letter ISO 3166-1 alpha-2 codes and support a special "invariant" country (`zz`) for language-independent content.

---

## Key Concepts

- **Collection/Record Pattern** — `Application_Countries` (collection singleton) manages `Application_Countries_Country` (record) instances via the DBHelper infrastructure. Lookup by ID (`getByID`) or ISO code (`getByISO`).
- **Invariant Country** — The special ISO code `zz` represents language-independent/country-neutral content. Many UI components and filters support excluding it via `excludeInvariant()`.
- **ISO Aliases** — The collection supports ISO aliases (e.g., `uk` → `gb`). Aliases are resolved transparently during lookup but are rejected during country creation to ensure canonical storage.
- **CountriesCollection** — A utility wrapper around a set of resolved `Application_Countries_Country` instances, providing convenience methods for working with country sets (first, contains, filter by ISO, etc.). Distinct from the DBHelper collection.
- **LocaleCode** — Parses locale strings like `de_DE` into their language and country components.
- **FilterCriteria** — DBHelper filter criteria for querying countries by ID list, with optional invariant exclusion.
- **CountrySettingsManager** — Formable-based settings manager for country record editing (label management).

---

## Folder Structure

| Directory | Contents |
|---|---|
| `./` | Core domain classes: collection, record, filter criteria, settings manager, navigator, button bar, selector, locale code parser, exception. |
| `Admin/` | Admin UI layer: screens (list, create, view with settings/status sub-screens), admin URL builders, request type handling, and mode/view traits for screen composition. |
| `AI/` | MCP tool container and tool implementations for AI agent access (list countries, get country config). |
| `API/` | **Submodule.** Reusable trait-based infrastructure for country parameter handling in API methods. Has its own `module-context.yaml`. |
| `Country/` | Country-specific UI components (flag icon rendering via `lipis/flag-icons`). |
| `Event/` | Domain events (`IgnoredCountriesUpdatedEvent`). |
| `Rights/` | User permission constants for country admin screens. |

---

## Integration Points

- **DBHelper** — `Application_Countries` extends `DBHelper_BaseCollection`; `Application_Countries_Country` extends `DBHelper_BaseRecord`. All persistence flows through the DBHelper infrastructure.
- **Localization** — Countries bridge to `AppLocalize\Localization\Countries\CountryInterface` for locale-aware labels, currency data, and ISO code resolution.
- **API Layer** — The `API/` submodule provides reusable traits that any API method can mix in to accept country parameters (singular or plural, by ID or ISO).
- **AI Tools** — The `AI/` directory registers MCP tools (`ListCountries`, `GetCountryConfig`) via the framework's AI tool container system.
- **Event Handler** — Emits `IgnoredCountriesUpdatedEvent` when the set of ignored countries changes.
- **UI** — Navigator (button bar country switcher), Selector (form element), ButtonBar (persistent country selection widget), and Icon (flag rendering) integrate with the framework's UI layer.
