# Submodule: Countries / API / Params

## Purpose

Individual parameter classes, interfaces, and validation classes for Countries API
parameter management. Covers both the **single-value** path (one country per request)
and the **multi-value** path (one or more countries per request).

---

## Class Overview

### Interfaces

| Class | Description |
|---|---|
| `AppCountryParamInterface` | Singular interface — extends `APIParameterInterface`, declares `getCountry(): ?Application_Countries_Country`. Implemented by `AppCountryIDParam` and `AppCountryISOParam`. |
| `AppCountriesParamInterface` | Plural interface — extends `APIParameterInterface`, declares `getCountries(): Application_Countries_Country[]`. Implemented by `AppCountryIDsParam` and `AppCountryISOsParam`. |

### Single-Value Parameters (`AppCountryAPITrait` path)

| Class | Base | Parameter name | Validation |
|---|---|---|---|
| `AppCountryIDParam` | `IntegerParameter` | `countryID` | `validateByValueExistsCallback` — checks `idExists()` |
| `AppCountryISOParam` | `StringParameter` | `countryISO` | `validateByValueExistsCallback` — checks `isoExists()` |

### Multi-Value Parameters (`AppCountriesAPITrait` path)

| Class | Base | Parameter name | Validation |
|---|---|---|---|
| `AppCountryIDsParam` | `IDListParameter` | `countryIDs` | `AppCountryIDsValidation` — per-ID error messages |
| `AppCountryISOsParam` | `StringListParameter` | `countryISOs` | `AppCountryISOsValidation` — per-ISO error messages |

### Handlers

| Class | Bridges |
|---|---|
| `AppCountryIDHandler` | `AppCountryIDParam` → `AppCountryParamsContainer` |
| `AppCountryISOHandler` | `AppCountryISOParam` → `AppCountryParamsContainer` |
| `AppCountryIDsHandler` | `AppCountryIDsParam` → `AppCountriesParamsContainer` |
| `AppCountryISOsHandler` | `AppCountryISOsParam` → `AppCountriesParamsContainer` |

### Validation

| Class | Validates | Error code |
|---|---|---|
| `AppCountryIDsValidation` | Each country ID in an `int[]` list individually | `VALIDATION_COUNTRY_ID_NOT_EXISTS = 184801` |
| `AppCountryISOsValidation` | Each country ISO code in a `string[]` list individually | `VALIDATION_COUNTRY_ISO_NOT_EXISTS = 184802` |

---

## `AppCountryIDsValidation` — Design Note

`AppCountryIDsValidation` extends `BaseParamValidation` rather than using
`validateByValueExistsCallback()`. This is intentional: the callback variant
receives the **entire resolved array** as a single argument, so it can only
report pass/fail for the list as a whole. `AppCountryIDsValidation` iterates
each ID individually, collecting all invalid IDs into a single error message
that identifies exactly which values are rejected. This gives API consumers
complete feedback in one round-trip rather than requiring repeated requests
to discover each invalid ID.

---

## Parameter Constant Ownership Note

`AppCountriesAPIInterface` (delivered in WP-004) is now the canonical owner of
`PARAM_COUNTRY_IDS = 'countryIDs'` and `PARAM_COUNTRY_ISOS = 'countryISOs'`.

`AppCountryIDsParam` and `AppCountryISOsParam` still declare their own local
constants with the same values. These local constants are duplicates and should
be updated to reference `AppCountriesAPIInterface::PARAM_COUNTRY_IDS` /
`AppCountriesAPIInterface::PARAM_COUNTRY_ISOS` to maintain a single source of
truth. This is tracked as a known debt item.

---

## `AppCountriesParamInterface` — Implementations

`AppCountriesParamInterface` is implemented by:
- `AppCountryIDsParam` — resolves countries from integer IDs
- `AppCountryISOsParam` — resolves countries from ISO codes

---

## `AppCountryIDsHandler` / `AppCountryISOsHandler` — Null-Return Contract

`AppCountryIDsHandler::resolveValueFromSubject()` and
`AppCountryISOsHandler::resolveValueFromSubject()` both return `null` (not `[]`)
when the request contains no value for their parameter (i.e. `getValue() === null`).

This is a **correctness requirement**, not a style preference. The
`BaseParamsHandlerContainer` uses a "first non-null wins" iteration: it calls each
registered handler in order and stops at the first non-`null` result. If either
handler returned `[]` instead of `null` when no value was present, the container
would stop there and never reach the next handler — meaning that when both IDs and
ISOs handlers are registered individually (Pattern 1), the ISOs handler would never
be tried if the IDs handler ran first.

**Summary of the contract:**

| Situation | Handler returns |
|---|---|
| Parameter present in request and valid | `Application_Countries_Country[]` (may be empty if all IDs/ISOs are invalid, but never `null`) |
| Parameter absent from request (`getValue() === null`) | `null` — signals "no value; try the next handler" |

This contract only matters for the **individual registration** pattern
(`manageIDs()->register()` + `manageISOs()->register()`). When using the OrRule
pattern (`manageAllParamsRule()->register()`), only one handler is registered, so
fall-through never occurs.

---

## Integration Points

- **Handlers** are composed by `AppCountryParamsContainer` (singular) and `AppCountriesParamsContainer` (plural), which are managed by their respective trait methods.
- **`IDListParameter`** (in `Application\API\Parameters\Type`) is the base for `AppCountryIDsParam`.
- **`StringListParameter`** (in `Application\API\Parameters\Type`) is the base for `AppCountryISOsParam`.
- **`BaseParamValidation`** (in `Application\API\Parameters\Validation`) is the base for `AppCountryIDsValidation`.
- **`AppFactory::createCountries()`** provides the `Countries` collection for all existence checks.
