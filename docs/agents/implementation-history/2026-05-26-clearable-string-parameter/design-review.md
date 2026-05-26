# Plan Architect Review

## Plan Under Review
- **Plan:** docs/agents/plans/2026-05-26-clearable-string-parameter/plan.md
- **Date:** 2026-05-26
- **Reviewer:** Plan Architect Reviewer Agent
- **Companion report:** `audit.md` (Plan Auditor, blocking) — produced in parallel; not consulted here.

## Overall Stance: Endorse with Recommendations

### Summary
The plan's shape is proportionate, minimal, and well-aligned with the framework's established extension patterns. Two new source files plus surgical modifications to two existing files is the right size for the problem. One medium-conviction concern exists around the interaction between the empty-string-as-clear-signal and the inherited validation pipeline, which could silently swallow the clearing intent under certain consumer configurations.

### Recommendation Counts
- **Simplifications:** 0
- **Concerns:** 2
- **Affirmations:** 5

---

## Recommendations

### Simplifications

_None identified._ The plan is already compact — two new source classes, one new validation class, two minimal modifications to existing files, and corresponding tests. There are no removable abstractions, speculative configuration, or unused intermediate artefacts.

### Concerns

| # | Subject | Concern | Conviction | Plan Location | Evidence `{SOURCE, LOCATION, CLAIM}` |
|---|---------|---------|------------|---------------|---------------------------------------|
| 1 | Validation pipeline interaction with clear signal | The three-state semantic (`null` / `''` / trimmed value) partially breaks down when inherited validations are applied. `RegexValidation` skips `null` but processes `''` — so a consumer using `->clearableString()->validateByRegex('/^[a-z]+$/')` would have the clear-signal (`''`) rejected by the regex, causing silent fallback to `null` (indistinguishable from "not provided"). The `MaxLengthValidation` correctly skips `''`, but there is no general mechanism ensuring other validations respect the clear semantic. The plan acknowledges this in Risks ("Regex validations on `''` would need to allow `^$`") but does not address it architecturally. Two candidate shapes for the Planner to weigh: (a) override `validate()` in `ClearableStringParameter` to skip the pipeline entirely when the resolved value is `''`, preserving the clear-signal unconditionally; or (b) accept the current shape and document the constraint that consumers must not attach validations that reject empty strings if they want clearing to work. Option (b) is the smaller choice but creates a subtle contract that `validateAs()` helper methods inherited from `StringParameter` may violate. | Medium | Risks & Mitigations, Step 3 | `{src/classes/Application/API/Parameters/Validation/Type/RegexValidation.php, L23–L27, "RegexValidation returns early for null but does NOT return early for empty string — it will attempt to match '' against the regex"}` |
| 2 | `RequiredValidation` interaction is misstated in Risks | The plan states "The `RequiredValidation` checks for null, not empty string, so required + clearable would allow clearing." The actual `RequiredValidation::validate()` uses `empty($value)`, and `empty('')` is `true` in PHP — so `makeRequired()` + `clearableString()` would **reject** the clear signal, not allow it. While this combination is in Out of Scope, the design risk is that a consumer may reasonably expect `makeRequired()` to mean "must be provided" (which clearing satisfies) rather than "must be non-empty" (which clearing violates). This is not a shape flaw — it's consistent with how `makeRequired()` works across all parameter types — but the stated risk mitigation is architecturally misleading and could guide consumers wrong. | Medium | Risks & Mitigations (4th risk entry) | `{src/classes/Application/API/Parameters/Validation/Type/RequiredValidation.php, L16–L22, "RequiredValidation uses empty($value) which returns true for '' — empty string fails the required check"}` |

### Affirmations

| # | Subject | What Is Right | Plan Location | Evidence `{SOURCE, LOCATION, CLAIM}` |
|---|---------|---------------|---------------|---------------------------------------|
| 1 | New type as extension point | Extending `StringParameter` with a single `resolveValue()` override is the framework's designed extension mechanism. It carries zero regression risk to existing code, inherits the full validation pipeline, `selectValue()` bypass, and documentation annotations for free. This is the correct architectural choice over modifying `StringParameter` with opt-in flags. | Approach / Architecture | `{src/classes/Application/API/Parameters/BaseAPIParameter.php, L302, "abstract protected function resolveValue() — explicitly designed for subclass override"}` |
| 2 | ParamTypeSelector factory method pattern | Adding `clearableString()` to `ParamTypeSelector` follows the exact structural pattern of all 14 existing factory methods — same instantiation, same `registerParam()` call, same return type. No departure from established patterns. | Step 4, Pattern Alignment | `{src/classes/Application/API/Parameters/ParamTypeSelector.php, L50–L57, "string() factory method pattern: instantiate, register, return — clearableString() follows identically"}` |
| 3 | MaxLengthValidation as reusable class on StringParameter | Placing `setMaxLength()` on `StringParameter` rather than on `ClearableStringParameter` alone means all string-based parameter types (including `CommonTypes/` subtypes like `LabelParameter`, `NameOrTitleParameter`) gain the capability automatically through inheritance. This mirrors how `validateByRegex()` wraps `RegexValidation` — a proven pattern in this codebase. | Rationale point 7, Step 2 | `{src/classes/Application/API/Parameters/Type/StringParameter.php, L100–L103, "validateByRegex() wraps RegexValidation — setMaxLength() will mirror this pattern exactly"}` |
| 4 | Three-state semantic aligned with HTTP form semantics | The mapping (absent → null → don't touch; present-but-empty → `''` → clear; present-with-value → trimmed string → set) maps directly to how HTML form fields and API clients encode "clear this optional field." This is the natural semantic for Update-style API methods and avoids inventing sentinel values or parallel parameters. | Approach / Architecture (table) | `{src/classes/Application/API/Parameters/BaseAPIParameter.php, L213, "hasValue() returns getValue() !== null — naturally maps to 'parameter was provided' when resolveValue() returns ''"}` |
| 5 | Narrow Out of Scope definition | Explicitly scoping out modifications to `BaseAPIParameter`, `RequestParam`, `StringParameter`'s existing behavior, and HCP Editor consumer code keeps the blast radius minimal. The follow-up plan for `UpdateComtypeAPI` will validate the new type against real consumer needs without coupling the framework change to application-specific logic. | Out of Scope | `{docs/agents/plans/2026-05-26-clearable-string-parameter/plan.md, Out of Scope section, "Modifying the HCP Editor's UpdateComtypeAPI to use the new type will be a separate follow-up plan"}` |

---

## Considered Alternatives

| Decision | Plan's Choice | Alternative(s) Considered | Trade-Off Summary |
|----------|--------------|---------------------------|-------------------|
| How to add clearable semantics | New `ClearableStringParameter` subtype | (A) Opt-in flag on `StringParameter` (e.g., `setClearable(true)`); (B) Handle clearing at the API method level manually | (A) would modify a stable class and add conditional logic to `resolveValue()` — every existing string parameter would carry the flag cost even if unused. (B) would scatter `$_REQUEST` inspection across every Update method. The plan's choice isolates the new behavior in a self-contained class with zero existing-code risk — correct. |
| Where to validate max-length | `MaxLengthValidation` class + `setMaxLength()` convenience on `StringParameter` | (A) Inline regex `'/^.{0,N}$/'` via existing `validateByRegex()`; (B) Generic callback via `validateByCallback()`; (C) `setMaxLength()` on `BaseAPIParameter` | (A) is less readable, doesn't produce a distinct error code, and byte-counts instead of character-counts for multibyte. (B) requires per-method boilerplate. (C) would expose a string-specific concept on non-string types (integer, boolean, JSON). The plan's placement on `StringParameter` with a dedicated validation class is the proportionate choice. |
| How to bypass `RequestParam::get()` empty-string swallowing | Direct `$_REQUEST` access via `array_key_exists()` | (A) Modify `RequestParam` in application-utils to support raw-mode; (B) Add a second request param mode to `StringParameter` | (A) is a cross-package change with wider blast radius and backwards-compatibility concerns. (B) complicates `StringParameter` with conditional resolution paths. Direct `$_REQUEST` is already the established test pattern and is the simplest path that works within the single-package constraint. |
| Validation skip for clear signal | Consumer responsibility (document constraint) | (A) Override `validate()` in `ClearableStringParameter` to skip pipeline when value is `''`; (B) Add a `skipWhenClearing` flag to `ParamValidationInterface` | (A) is a small shape change that could prevent subtle consumer errors. (B) is overengineered for the current need. The plan's choice is the simplest but creates an implicit contract. See Concern #1. |

---

## Notes for the Planner

**Most important recommendation:** Concern #1 (validation pipeline interaction) is the one finding worth deliberating before implementation. If the Planner anticipates consumers combining `clearableString()` with format validations from `validateAs()` (which `StringParameter` exposes and all subtypes inherit), consider option (a) — a small override of `validate()` or wrapping the pipeline call to short-circuit on `''`. This is a 3-line addition that would make the three-state contract unconditional rather than consumer-awareness-dependent. If the only anticipated consumer is `setMaxLength()` (which already handles this correctly), the current shape is acceptable.

**Most important affirmation to preserve:** Affirmation #1 (new type as extension point) and Affirmation #3 (MaxLengthValidation on StringParameter) are the structural pillars. Do not collapse these into a single-class solution or move `setMaxLength()` down to `ClearableStringParameter` only — the inheritance benefit is worth preserving.
