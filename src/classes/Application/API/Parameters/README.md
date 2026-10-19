# Submodule: API / Parameters

## Purpose

Complete parameter type system for API methods. Provides typed parameter
classes, a fluent registration API, cross-parameter validation rules, value
validation, selectable value lookups, and the handler pipeline that
bridges parameters and rules into the API method lifecycle.

API methods register parameters through `APIParamManager.addParam()` which
returns a `ParamTypeSelector` for choosing the parameter type. Each typed
parameter extends `BaseAPIParameter` and implements `APIParameterInterface`.

---

## Key Concepts

- **Typed Parameters:** Concrete parameter classes for common data types:
  `StringParameter`, `ClearableStringParameter`, `IntegerParameter`,
  `BooleanParameter`, `JSONParameter`, `IDListParameter`, `StringListParameter`.
  Each enforces type-specific parsing and default values.
  `ClearableStringParameter` extends `StringParameter` with three-state
  resolution semantics (`null` = absent, `''` = explicitly cleared, string =
  value), which is useful for Update-style API methods that need to distinguish
  between "not submitted" and "submitted as empty". Use
  `ParamTypeSelector::clearableString()` to register this parameter type.
- **Common Types:** Reusable domain-specific parameter types built on top of
  the base types: `AliasParameter`, `AlphabeticalParameter`,
  `AlphanumericParameter`, `DateParameter`, `EmailParameter`, `LabelParameter`,
  `MD5Parameter`, `NameOrTitleParameter`.
- **ParamTypeSelector:** Fluent builder returned by `APIParamManager::addParam()`
  that lets the API method choose the parameter type and configure it.
- **Flavors:** Cross-cutting parameter behaviors via interface/trait pairs:
  `APIHeaderParameterInterface`/`APIHeaderParameterTrait` (parameter read from
  HTTP headers), `RequiredOnlyParamInterface`/`RequiredOnlyParamTrait`
  (parameter that is always required).
- **Validation:** Per-parameter validations implementing
  `ParamValidationInterface`: `RequiredValidation`, `EnumValidation`,
  `RegexValidation`, `CallbackValidation`, `ValueExistsCallbackValidation`.
  Results are collected in `ParamValidationResults`.
  `BaseAPIParameter::validateByCallback(callable $callback, ...$args)`
  forwards its extra `$args` to `$callback` after the standard
  `(value, result, param)` triplet.
- **Rules:** Cross-parameter constraints implementing `RuleInterface`:
  `OrRule` (at least one of N params must be present), `RequiredIfOtherIsSetRule`
  (param required when another is set), `RequiredIfOtherValueEquals` (param
  required when another has a specific value). Rules are registered via
  `APIParamManager::addRule()` through a `RuleTypeSelector`.
- **Value Lookups:** `SelectableValueParamInterface`/`SelectableValueParamTrait`
  allow parameters to declare a fixed set of selectable values
  (`SelectableParamValue`). Used in documentation and validation.
- **Handlers:** `ParamHandlerInterface`, `BaseParamHandler`, and
  `BaseRuleHandler` provide the internal pipeline that executes parameter
  reading and rule evaluation during API method processing.
  `APIHandlerInterface`/`BaseAPIHandler` and
  `ParamsHandlerContainerInterface`/`BaseParamsHandlerContainer` compose
  multiple handlers.
- **Reserved Parameters:** `APIMethodParameter` and `APIVersionParameter`
  implement `ReservedParamInterface` and are automatically registered by
  `BaseAPIMethod`. Application code cannot register parameters with these names.
- **Value Sources:** `ParamValueSourceInterface` (`hasKey()`, `getRawValue()`,
  `getKeys()`) abstracts where a parameter's raw value comes from.
  `RequestValueSource` wraps the request (query string/POST), with exactly the
  semantics `BaseAPIParameter::getRequestParam()->get()` had before this seam
  existed. `JSONBodyValueSource` resolves from a JSON method's parsed request
  body first, falling back to a `RequestValueSource` for any key the body does
  not carry — the body value wins when both are present, and `getKeys()`
  returns the union of body and request keys. `APIParamManager::getValueSource()`
  creates the right one lazily (`JSONBodyValueSource` for a
  `JSONRequestInterface` method, `RequestValueSource` otherwise) and injects it
  into every parameter at `registerParam()` time, except header and reserved
  parameters. `BaseAPIParameter::setValueSource()`/`getRawRequestValue()` are
  the injection point and the per-type read seam, used by all six core typed
  parameters (string, integer, boolean, JSON, ID list, string list). The same
  `getKeys()` also serves as `BaseAPIMethod::rejectRemovedParams()`'s
  removed-parameter key source, so a declared removed name is detected in a
  JSON body exactly as it would be in the query string.
- **Base64 File Parameter:** `Base64FileParameter` (`ParamTypeSelector::base64File()`)
  accepts file content as a base64-encoded string, either plain or with an
  optional `data:<mime>;base64,` URI prefix, with whitespace stripped. The
  decoded size is computed from the encoded length and checked against the
  configured limit — `AppConfig::getAPIMaxFileContentSize()`
  (`APP_API_MAX_FILE_CONTENT_SIZE`, 10 MiB by default) unless overridden with
  `setMaxSize()` — before any decoding, so an oversized value is rejected
  with `VALIDATION_FILE_SIZE_EXCEEDED` (183509) without ever being decoded.
  A value that fails strict base64 decoding is rejected with
  `VALIDATION_INVALID_BASE64` (183508). `getValue()`/`getDecodedContent()`
  return the decoded binary content, and the parameter retains no base64
  copy of it.
- **Removed Parameters:** `RemovedParameter` and `RemovedParamCollection`
  let an API method declare a request parameter name it has stopped
  accepting, via `APIParamManager::addRemovedParam()`. A removed
  declaration cannot share a name with an active parameter in either
  registration order, and `RemovedParamCollection::detectUsed()` lets
  the lifecycle reject requests that still send the old name with a
  composed error message (`RemovedParameter::getErrorMessage()`),
  instead of the application hand-writing a guard for it.

---

## Folder Structure

| Directory | Contents |
|---|---|
| Root | `APIParamManager`, `APIParameterInterface`, `BaseAPIParameter`, `ParamTypeSelector`, `APIParameterException`, `ReservedParamInterface` — core contracts and manager. |
| `CommonTypes/` | Reusable domain-specific parameter types (alias, date, email, etc.). |
| `Flavors/` | Cross-cutting parameter behavior interfaces and traits (header params, required-only). |
| `Handlers/` | Internal pipeline: param handlers, rule handlers, and handler containers. |
| `Removed/` | Removed-parameter declarations (`RemovedParameter`, `RemovedParamCollection`) and request detection. |
| `Reserved/` | Framework-reserved parameters (method name, API version). |
| `Rules/` | Cross-parameter constraint rules and the rule type selector. |
| `Type/` | Core typed parameter classes (string, clearable string, integer, boolean, JSON, ID list, string list, base64 file). |
| `Validation/` | Per-parameter validation types and result collection. |
| `ValueLookup/` | Selectable value support for parameters with fixed value sets. |
| `ValueSource/` | `ParamValueSourceInterface`, `RequestValueSource`, `JSONBodyValueSource` — the raw-value backing store injected into registered parameters. |

---

## Integration Points

- **`BaseAPIMethod`** calls `manageParams()` to obtain the `APIParamManager` and
  registers parameters during `init()`.
- **`api-openapi`** submodule reads parameter metadata via `ParameterConverter` to
  generate OpenAPI 3.1 parameter and request body schema definitions.
- **`api-cache`** submodule uses `getCacheKeyParameters()` which typically map to
  the method's registered parameter values.
- **API documentation** renders parameter tables from the manager's registered
  parameters and rules.
