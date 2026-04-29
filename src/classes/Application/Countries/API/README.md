# Submodule: Countries / API

## Purpose

Reusable API infrastructure for exposing country data through API methods.
Provides two complementary trait-based patterns for country parameter handling:

- **Singular** (`AppCountryAPITrait`) — accepts exactly one country via ID or ISO code, resolving to a single `Application_Countries_Country`.
- **Plural** (`AppCountriesAPITrait`) — accepts one or more countries via a list of IDs or ISO codes, resolving to `Application_Countries_Country[]`.

Both patterns use the `BaseParamsHandlerContainer` / `BaseParamHandler` architecture and enforce mutual exclusivity between the ID and ISO paths via `OrRule`. Both traits can coexist on the same API method when a method needs to handle both a single country and a list of countries.

---

## Key Concepts

- **`AppCountryAPIInterface`** — Contract for single-country API methods. Declares `PARAM_COUNTRY_ID = 'countryID'` and `PARAM_COUNTRY_ISO = 'countryISO'`. Implementations use `AppCountryAPITrait`.
- **`AppCountryAPITrait`** — Mix-in that lazy-initialises `AppCountryParamsContainer`. Apply to any API method that needs a single country parameter.
- **`AppCountryParamsContainer`** — Composes the ID handler, ISO handler, and `OrRule` handler. `resolveValue()` / `requireValue()` return a single `Application_Countries_Country`.
- **`AppCountriesAPIInterface`** — Contract for multi-country API methods. Declares `PARAM_COUNTRY_IDS = 'countryIDs'` and `PARAM_COUNTRY_ISOS = 'countryISOs'`. Implementations use `AppCountriesAPITrait`.
- **`AppCountriesAPITrait`** — Mix-in that lazy-initialises `AppCountriesParamsContainer`. Apply to any API method that needs a list of country parameters.
- **`AppCountriesParamsContainer`** — Composes the IDs handler, ISOs handler, and `OrRule` handler. `resolveValue()` returns `Application_Countries_Country[]` (empty array if nothing resolved); `requireValue()` returns `Application_Countries_Country[]` and terminates the request with an error response if no countries can be resolved.
- **`CountriesAPIGroup`** — `APIGroupInterface` implementation for grouping Countries API methods in documentation and access control.
- **`CountryAPIException`** — Domain-specific exception for Countries API errors.
- **`Params/`** — Individual parameter and validation classes for both single-value (ID, ISO) and multi-value (IDs, ISOs) paths. See [`Params/README.md`](Params/README.md).
- **`ParamSets/`** — `OrRule` components (parameter sets, rule, rule handler) enforcing that callers provide ID **or** ISO (or IDs **or** ISOs) but not both.

---

## Folder Structure

| Directory / File | Contents |
|---|---|
| `AppCountryAPIInterface.php` | Single-country method contract — `PARAM_COUNTRY_ID`, `PARAM_COUNTRY_ISO`, `manageAppCountryParams()`. |
| `AppCountryAPITrait.php` | Single-country mix-in — lazy-initialises `AppCountryParamsContainer`. |
| `AppCountryParamsContainer.php` | Single-country handler container — composes ID/ISO/OrRule handlers, resolves to one `Application_Countries_Country`. |
| `AppCountriesAPIInterface.php` | Multi-country method contract — `PARAM_COUNTRY_IDS`, `PARAM_COUNTRY_ISOS`, `manageAppCountriesParams()`. |
| `AppCountriesAPITrait.php` | Multi-country mix-in — lazy-initialises `AppCountriesParamsContainer`. |
| `AppCountriesParamsContainer.php` | Multi-country handler container — composes IDs/ISOs/OrRule handlers, resolves to `Application_Countries_Country[]`. |
| `CountriesAPIGroup.php` | API method group for documentation and permissions. |
| `CountryAPIException.php` | Domain-specific exception. |
| `Params/` | Parameter and validation classes for single- and multi-country paths. |
| `ParamSets/` | `OrRule` infrastructure for both singular and plural paths. **Singular:** `AppCountryParamSetInterface`, `BaseAppCountryParamSet`, `AppCountryParamRule`, `AppCountryRuleHandler`, `CountryIDSet`, `CountryISOSet`. **Plural:** `AppCountriesParamSetInterface`, `BaseAppCountriesParamSet`, `AppCountriesParamRule`, `AppCountriesRuleHandler`, `CountryIDsSet`, `CountryISOsSet`. |

---

## Usage — Single Country

```php
use Application\Countries\API\AppCountryAPIInterface;
use Application\Countries\API\AppCountryAPITrait;

class GetCountryAPI extends BaseAPIMethod implements AppCountryAPIInterface
{
    use AppCountryAPITrait;

    protected function init(): void
    {
        // Register both ID and ISO params, plus the OrRule
        $this->manageAppCountryParams()
            ->manageID()->register();

        $this->manageAppCountryParams()
            ->manageISO()->register();

        $this->manageAppCountryParams()
            ->manageAllParamsRule()->register();
    }

    protected function processRequest(): void
    {
        // Resolves the country from whichever param was supplied.
        // Terminates with an error response if neither param is present.
        $country = $this->manageAppCountryParams()->requireValue();
    }
}
```

---

## Usage — Multiple Countries

There are two registration patterns for `AppCountriesAPITrait`. Choose the one that
matches your mutual-exclusivity requirements.

### Pattern 1 — Individual registration (no mutual exclusivity)

Register the IDs and ISOs handlers separately. Both parameters are optional and
independent — the caller may supply either, both, or neither. The container uses a
"first non-null wins" strategy: the IDs handler is tried first; if no `countryIDs`
value is present in the request, the ISOs handler is tried next.

> **Key behaviour:** `AppCountryIDsHandler` and `AppCountryISOsHandler` return `null`
> (not `[]`) when the request contains no value for their parameter. This `null`
> sentinel allows the container to fall through to the next handler. Registering both
> handlers individually does **not** enforce mutual exclusivity.

```php
use Application\Countries\API\AppCountriesAPIInterface;
use Application\Countries\API\AppCountriesAPITrait;

class GetMailingsAPI extends BaseAPIMethod implements AppCountriesAPIInterface
{
    use AppCountriesAPITrait;

    protected function init(): void
    {
        // Register each handler individually — no OrRule, no mutual exclusivity.
        // IDs are tried first; ISOs are tried if no IDs are present.
        $this->manageAppCountriesParams()->manageIDs()->register();
        $this->manageAppCountriesParams()->manageISOs()->register();
    }

    protected function processRequest(): void
    {
        // Returns [] if neither param is present; does NOT terminate the request.
        $countries = $this->manageAppCountriesParams()->resolveValue();
    }
}
```

@see `TestGetCountriesAPI` for a complete example of this registration pattern.

---

### Pattern 2 — OrRule registration (mutual exclusivity)

Register the combined `AppCountriesParamRule` via `manageAllParamsRule()`. The OrRule
enforces that the caller supplies **either** `countryIDs` **or** `countryISOs`, but
not both. Supplying both or neither results in an API error response.

```php
use Application\Countries\API\AppCountriesAPIInterface;
use Application\Countries\API\AppCountriesAPITrait;

class GetMailingsBySetAPI extends BaseAPIMethod implements AppCountriesAPIInterface
{
    use AppCountriesAPITrait;

    protected function init(): void
    {
        // Registers the OrRule — enforces mutual exclusivity between IDs and ISOs.
        $this->manageAppCountriesParams()->manageAllParamsRule()->register();
    }

    protected function processRequest(): void
    {
        // Resolves the country list from whichever param was supplied.
        // Terminates with an error response if neither param is present or both are given.
        $countries = $this->manageAppCountriesParams()->requireValue();
    }
}
```

Callers supply either `countryIDs` (comma-separated integers or a JSON array) **or**
`countryISOs` (comma-separated ISO codes or a JSON array), but not both. The `OrRule`
enforces mutual exclusivity and returns an error response if both are present.

@see `TestGetCountriesBySetAPI` for a complete example of this registration pattern.

---

## Validation Strategy — Single vs Multi-Value Parameters

Single-value parameters (`AppCountryIDParam`, `AppCountryISOParam`) use
`validateByValueExistsCallback()` — a one-shot callback that receives the
already-resolved scalar value and returns `true`/`false`.

Multi-value parameters (`AppCountryIDsParam`, `AppCountryISOsParam`) require a
custom `BaseParamValidation` subclass instead. The reason:
`ValueExistsCallbackValidation` passes the **entire** resolved value (an `int[]`
or `string[]` array) to the callback as a single argument — it does not iterate
per item. A custom class gives the validator control over per-item iteration and
lets it report precisely which IDs or ISO codes are invalid in a single error
message.

> **Rule of thumb:** Use `validateByValueExistsCallback()` for scalar parameters.
> Use a `BaseParamValidation` subclass when you need per-item validation on a
> list parameter.

---

## `requireValue()` Termination Semantics

Both `BaseParamsHandlerContainer::requireValue()` and `BaseAPIHandler::requireValue()`
call `->send()` on the error response object when no value can be resolved. The
`->send()` call terminates PHP request execution — no code after the call runs.

The PHP return type on these base methods is `string|int|float|bool|array|object`
(not `never`) because PHP does not allow `never` on interface/abstract methods that
subclasses override with a narrower non-`never` return type. The
`@phpstan-return never` annotation on each base method makes this contract explicit
for static analysis.

**Practical implication for subclass overrides:** When a subclass (e.g.
`AppCountriesParamsContainer::requireValue()`) calls `parent::requireValue()` and
then applies a type-narrowing guard (`is_array()` check + fallback `return array()`),
the fallback branch is never reached at runtime — but it is a valid and necessary
guard from PHP's static type perspective, because the parent return type is not
declared `never`. This is the correct pattern for this codebase.

---

## Integration Points

- **Single-country API methods** implement `AppCountryAPIInterface` and use `AppCountryAPITrait` to gain single-country parameter management.
- **Multi-country API methods** implement `AppCountriesAPIInterface` and use `AppCountriesAPITrait` to gain multi-country parameter management.
- **`BaseParamsHandlerContainer`** (in `Application\API\Parameters\Handlers`) is the base class for both `AppCountryParamsContainer` and `AppCountriesParamsContainer`.
- **`BaseParamHandler`** is the base class for all param handlers in `Params/`.
- **`OrRule`** (in `Application\API\Parameters\Rules`) is composed by the rule classes to enforce mutual exclusivity.
- **`AppFactory::createCountries()`** provides the `Countries` collection used for ID/ISO existence validation.
