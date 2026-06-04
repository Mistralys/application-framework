# Wizard Support

Multi-step wizard flows for the admin panel. The wizard system provides a session-backed,
step-by-step data-collection UI with automatic navigation, step invalidation, and an optional
**Preselection API** for pre-populating step fields before redirecting a user to the wizard.

---

## Classes

| Class | Role |
|---|---|
| `BaseWizardMode` | Extend this to create a wizard screen. Combines `Application_Admin_Wizard` with `AllowableMigrationInterface`. |
| `Step` | Abstract base for individual wizard steps. Extend one class per step. |
| `InvalidationHandler` | Carries invalidation state when a preceding step change forces later steps to reset. |
| `WizardPreselection` | Typed key/value store for preselection values. Step-name → key → value. |
| `WizardConfigurator` | Session orchestrator and URL builder. Writes preselection into session and returns the redirect URL. |

The core session management, navigation, and settings persistence live in the trait
`Application_Traits_Admin_Wizard` (`src/classes/Application/Traits/Admin/Wizard.php`).

---

## Preselection API

The Preselection API lets a consumer pre-populate one or more step fields and redirect the
user straight to the wizard. The wizard reads the pre-populated values as if the user had
entered them on a previous visit — no changes to the wizard trait or steps are required.

### Minimal usage

```php
$configurator = new WizardConfigurator($wizardURL);

$configurator->getPreselection()
    ->setStepValue('Countries', 'country_id', 'GB');

$redirectURL = $configurator->getRedirectURL();
// $redirectURL → "https://example.com/admin/?page=…&wizard=WZ12345678"

header('Location: ' . $redirectURL);
exit;
```

`$wizardURL` is the wizard screen's base URL (no `wizard=` parameter). Obtain it via the
same `AdminURL` helper you would use to link to the wizard screen in the normal flow.

### How it works

`getRedirectURL()` creates a new wizard session, writes each preselection value directly
into the session slot the wizard trait already reads (`settingPrefix + '-step_' + stepName`),
and appends `?wizard=<sessionID>` to the base URL. The wizard trait requires no modification.

---

## Pitfalls

### settingPrefix must match exactly

`WizardConfigurator`'s second constructor parameter `$settingPrefix` must **exactly match**
the target wizard's `$settingPrefix` property (case-sensitive). The default for all current
wizards is `''` (empty string), which is also the constructor default, so no argument is
needed unless the target wizard explicitly calls `setSettingPrefix()`.

A mismatch silently writes session keys the wizard cannot find. The user arrives at the
wizard with no preselection applied and no error is raised.

```php
// Wizard that calls $this->setSettingPrefix('myapp') in its constructor:
$configurator = new WizardConfigurator($wizardURL, 'myapp'); // ← must match

// Wizard using the default prefix (all current wizards):
$configurator = new WizardConfigurator($wizardURL); // ← omit prefix
```

### Step names must match addStep() exactly

`WizardPreselection::setStepValue($stepName, ...)` expects `$stepName` to verbatim match
the argument passed to `addStep()` in the wizard's `_initSteps()` method (e.g., `'Countries'`,
not `'country'` or `'step-countries'`). A mismatch silently produces session keys the wizard
step will never receive.

```php
// Wizard _initSteps() registers: $this->addStep('Countries', new Step_Countries($this));
$configurator->getPreselection()
    ->setStepValue('Countries', 'country_id', 'GB'); // ← use 'Countries', not 'country'
```

To find the correct step name, look at the string passed to `addStep()` in the target
wizard's `_initSteps()` implementation.
