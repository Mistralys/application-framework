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
  `StringParameter`, `IntegerParameter`, `BooleanParameter`, `JSONParameter`,
  `IDListParameter`. Each enforces type-specific parsing and default values.
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

---

## Folder Structure

| Directory | Contents |
|---|---|
| Root | `APIParamManager`, `APIParameterInterface`, `BaseAPIParameter`, `ParamTypeSelector`, `APIParameterException`, `ReservedParamInterface` — core contracts and manager. |
| `CommonTypes/` | Reusable domain-specific parameter types (alias, date, email, etc.). |
| `Flavors/` | Cross-cutting parameter behavior interfaces and traits (header params, required-only). |
| `Handlers/` | Internal pipeline: param handlers, rule handlers, and handler containers. |
| `Reserved/` | Framework-reserved parameters (method name, API version). |
| `Rules/` | Cross-parameter constraint rules and the rule type selector. |
| `Type/` | Core typed parameter classes (string, integer, boolean, JSON, ID list). |
| `Validation/` | Per-parameter validation types and result collection. |
| `ValueLookup/` | Selectable value support for parameters with fixed value sets. |

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
