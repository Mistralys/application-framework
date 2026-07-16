## Synthesis

### Completion Status
- Date: 2026-07-16
- Status: COMPLETE
- Completed by: Standalone Developer Agent
- Archived in Ledger: 2026-07-16

### Outcome Summary

Added a `protected isValueValidatable()` template-method hook to `BaseAPIParameter` and wired it into the private `validate()` method. `ClearableStringParameter` overrides this hook to return `false` when the resolved value is `''`, causing all format validators to be skipped for the clear signal. All seven new tests pass alongside the existing seventeen, and PHPStan reports no new errors.

### Implementation Summary
- Added `isValueValidatable()` to `BaseAPIParameter` — returns `true` by default, preserving behaviour for all existing parameter types.
- Wired the hook into `validate()` immediately after the early-return `isValid()` guard and before the `RequiredValidation` prepend, so a skipped value is treated as valid (returns `true`).
- Overrode `isValueValidatable()` in `ClearableStringParameter` to return `false` when `$value === ''`, delegating all other values to `parent::isValueValidatable()`.
- Extended `ClearableStringParameterTest` with a new `// region: Validation bypass on clear signal` block containing seven tests covering `validateByRegex()`, `validateAs()->url()`, `validateByCallback()`, `validateByEnum()`, and the absent-key regression guard.

### Documentation Updates
- No documentation updates were required: the plan explicitly states this is a self-contained change with no documentation artefacts to update.

### Verification Summary
- Tests run: `composer test-file -- tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php` — 27 tests, 68 assertions, OK.
- Static analysis run: `composer analyze` — No errors (2140 files analysed).
- Result: PASS

### Code Insights
- [low] (improvement) `src/classes/Application/API/Parameters/Validation/Type/RegexValidation.php`: ~~The validator does not short-circuit after the `!is_string($value)` error — it falls through and also calls `preg_match()` on a non-string value. Since `preg_match()` on a non-string would emit a deprecation/TypeError in PHP 8.x, adding an `else if` or an early `return` after the type-error branch would make the control flow safer. This is pre-existing debt; out of scope for this plan.~~ **DONE** — added `return` after the `!is_string()` error branch on 2026-07-16.
- [low] (convention) `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php`: ~~The new `test_validateByRegex_validValue_passesValidation` test uses `'https://example.com'` as the valid URL. `REGEX_URL` is case-insensitive and supports relative URLs; using a full absolute URL with scheme makes the test most readable and unambiguous.~~ **ACKNOWLEDGED**

### Additional Comments
- Consumers can now safely chain `->clearableString()->validateAs()->url()` (and any other format validator) without needing any workaround. The clear signal `''` passes through validation untouched.
- `makeRequired()` on a clearable param is still a semantic contradiction (clearing a required field); the hook fires *before* `RequiredValidation` would be prepended, so `''` would never reach `RequiredValidation`. This is expected and correct — it is documented as out of scope in the plan.
