# Admin - Wizard Support
<INSTRUCTION>
# Admin Module — Wizard (Multi-Step) Support

Base classes and Preselection API for multi-step wizard flows: BaseWizardMode, Step,
and InvalidationHandler for step dependency management; WizardPreselection as a typed
key/value store for pre-populating step fields; WizardConfigurator as the session
orchestrator and URL builder that consumers use to redirect users to a pre-filled wizard.

</INSTRUCTION>
------------------------------------------------------------
_SOURCE: Wizard classes_
# Wizard classes
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── Wizard/
                    └── BaseWizardMode.php
                    └── InvalidationHandler.php
                    └── Step.php
                    └── WizardConfigurator.php
                    └── WizardPreselection.php

```
###  Path: `/src/classes/Application/Admin/Wizard/BaseWizardMode.php`

```php
namespace Application\Admin\Wizard;

use Application\Interfaces\AllowableMigrationInterface as AllowableMigrationInterface;
use Application\Traits\AllowableMigrationTrait as AllowableMigrationTrait;

abstract class BaseWizardMode extends \Application_Admin_Wizard implements AllowableMigrationInterface
{
	use AllowableMigrationTrait;
}


```
###  Path: `/src/classes/Application/Admin/Wizard/InvalidationHandler.php`

```php
namespace Application\Admin\Wizard;

/**
 * Class for invalidation process data.
 *
 * @package Application
 * @subpackage Administration
 * @author Emre Celebi <emre.celebi@ionos.com>
 *
 * @see Application_Admin_Wizard
 */
class InvalidationHandler
{
	/**
	 * @return bool
	 */
	public function isInvalidated(): bool
	{
		/* ... */
	}


	/**
	 * @param bool $isInvalidated
	 * @return InvalidationHandler
	 */
	public function setIsInvalidated(bool $isInvalidated): self
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getInvalidationMessage(): string
	{
		/* ... */
	}


	/**
	 * @param string $invalidationMessage
	 * @return InvalidationHandler
	 */
	public function setInvalidationMessage(string $invalidationMessage): self
	{
		/* ... */
	}


	/**
	 * @return string
	 */
	public function getInvalidationURL(): string
	{
		/* ... */
	}


	/**
	 * @param string $invalidationURL
	 * @return InvalidationHandler
	 */
	public function setInvalidationURL(string $invalidationURL): self
	{
		/* ... */
	}


	/**
	 * @return int
	 */
	public function getInvalidationCallingStep(): int
	{
		/* ... */
	}


	/**
	 * This parameter is used for checking which step started invalidation check.
	 * Application_Traits_Admin_Wizard->handle_stepUpdated function is calling recursively
	 * so system must decide which step is started this progress.
	 * @param int $invalidationCallingStep
	 * @return InvalidationHandler
	 * @see Application_Traits_Admin_Wizard
	 */
	public function setInvalidationCallingStep(int $invalidationCallingStep): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Wizard/Step.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\Interfaces\StringableInterface as StringableInterface;
use AppUtils\OutputBuffering as OutputBuffering;
use Application\EventHandler\Eventables\EventableListener as EventableListener;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\Page\Navigation\QuickNavigation as QuickNavigation;

/**
 * Base class for individual steps in a wizard. Based on the application
 * skeleton for administration pages, this allows for easy form handling
 * and the base structure handles all the data flow and necessary updates.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Admin_Wizard
 */
abstract class Application_Admin_Wizard_Step extends Application_Admin_Skeleton
{
	public const ERROR_CANNOT_UPDATE_FROM_UNMONITORED_STEP = 558002;
	public const ERROR_UNHANDLED_STEP_UPDATE = 558003;
	public const ERROR_STEP_MUST_BE_COMPLETE_FOR_OPERATION = 558004;

	/**
	 * Called when all steps in the wizard have been
	 * initialized, and before the step is processed.
	 * Use this to set up the step's environment.
	 */
	abstract public function initDone(): void;


	abstract public function getLabel(): string;


	abstract public function _process(): bool;


	abstract public function getAbstract(): string;


	/**
	 * Optional icon for the step.
	 * @return UI_Icon|NULL
	 */
	public function getIcon(): ?UI_Icon
	{
		/* ... */
	}


	/**
	 * Retrieves the name of the step, e.g. "StepName".
	 *
	 * @return string
	 */
	public function getID(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	/**
	 * Called before rendering the step's contents. Must return
	 * a boolean value indicating whether the step has been
	 * completed, in which case the wizard can jump to the next step.
	 *
	 * @return boolean
	 */
	public function process(): bool
	{
		/* ... */
	}


	/**
	 * The URL to switch to this step.
	 * @param array<string,int|float|string|StringableInterface> $params
	 * @return string
	 */
	public function getURL(array $params = []): string
	{
		/* ... */
	}


	/**
	 * The URL to review this step when it has been completed.
	 * @param array $params
	 * @return string
	 */
	public function getURLReview(array $params = []): string
	{
		/* ... */
	}


	/**
	 * The step number (begins at 1).
	 * @return int
	 */
	public function getNumber(): int
	{
		/* ... */
	}


	/**
	 * Whether this is the active step.
	 * @return boolean
	 */
	public function isActive(): bool
	{
		/* ... */
	}


	/**
	 * Whether this step can be switched to. This is true for
	 * all steps that have been completed.
	 *
	 * @return boolean
	 */
	public function isEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Checks if this step has been completed.
	 * @return boolean
	 */
	public function isComplete(): bool
	{
		/* ... */
	}


	/**
	 * Sets the completed state of the step.
	 * @param boolean $complete
	 * @return $this
	 */
	public function setComplete(bool $complete = true): self
	{
		/* ... */
	}


	/**
	 * Overridden to add the required hidden form variables.
	 *
	 * @see Application_Formable::createFormableForm()
	 * @inheritDoc
	 */
	public function createFormableForm(string $name, $defaultData = []): self
	{
		/* ... */
	}


	/**
	 * Retrieves the step's session data collection, which is stored
	 * by the wizard itself and restored on every request.
	 *
	 * @return array<string,mixed>
	 */
	public function getData(): array
	{
		/* ... */
	}


	/**
	 * This is called when a step in the wizard has been modified
	 * that comes before this one. Allows the step to adjust its
	 * status according to the new data.
	 *
	 * @param Application_Admin_Wizard_Step $step
	 */
	public function handle_stepUpdated(Application_Admin_Wizard_Step $step): void
	{
		/* ... */
	}


	public function handle_cancelWizardCleanup(): void
	{
		/* ... */
	}


	/**
	 * Checks whether this step monitors changes to the target step.
	 *
	 * @param Application_Admin_Wizard_Step $step
	 * @return bool
	 */
	public function isMonitoring(Application_Admin_Wizard_Step $step): bool
	{
		/* ... */
	}


	public function getURLName(): string
	{
		/* ... */
	}


	public function getURLPath(): string
	{
		/* ... */
	}


	public function getFormName(): string
	{
		/* ... */
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getDataKey(string $name)
	{
		/* ... */
	}


	/**
	 * Gets the parent step name of this step.
	 * @return string|NULL
	 */
	public function getParent(): ?string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getDataKeyNames(): array
	{
		/* ... */
	}


	public function postInit(): void
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	public function getArea(): AdminAreaInterface
	{
		/* ... */
	}


	public function getSidebar(): ?UI_Page_Sidebar
	{
		/* ... */
	}


	public function requireSidebar(): UI_Page_Sidebar
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getParentScreen(): ?AdminScreenInterface
	{
		/* ... */
	}


	public function handleActions(): bool
	{
		/* ... */
	}


	public function renderContent(): string
	{
		/* ... */
	}


	public function getURLParam(): string
	{
		/* ... */
	}


	public function handleBreadcrumb(): void
	{
		/* ... */
	}


	public function getDefaultSubscreenID(): string
	{
		/* ... */
	}


	public function handleSidebar(UI_Page_Sidebar $sidebar): void
	{
		/* ... */
	}


	public function hasActiveSubscreen(): bool
	{
		/* ... */
	}


	public function handleTabs(UI_Bootstrap_Tabs $tabs): void
	{
		/* ... */
	}


	public function handleContextMenu(UI_Bootstrap_DropdownMenu $menu): void
	{
		/* ... */
	}


	public function handleSubnavigation(UI_Page_Navigation $subnav): void
	{
		/* ... */
	}


	public function handleQuickNavigation(QuickNavigation $navigation): void
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onBeforeActionsHandled(callable $listener): EventableListener
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onSidebarHandled(callable $listener): EventableListener
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onBeforeSidebarHandled(callable $listener): EventableListener
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onBreadcrumbHandled(callable $listener): EventableListener
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onBeforeBreadcrumbHandled(callable $listener): EventableListener
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onActionsHandled(callable $listener): EventableListener
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onContentRendered(callable $listener): EventableListener
	{
		/* ... */
	}


	/**
	 * @param callable $listener
	 * @return never
	 * @throws Application_Admin_WizardException {@see Application_Admin_WizardException::ERROR_UNSUPPORTED_STEP_ACTION}
	 */
	public function onBeforeContentRendered(callable $listener): EventableListener
	{
		/* ... */
	}


	public function isUserAllowed(): bool
	{
		/* ... */
	}


	public function isArea(): bool
	{
		/* ... */
	}


	public function handleHelp(UI_Page_Help $help): void
	{
		/* ... */
	}


	public function getActiveSubscreenID(): ?string
	{
		/* ... */
	}


	public function getActiveSubscreen(): ?AdminScreenInterface
	{
		/* ... */
	}


	public function hasSubscreen(string $id): bool
	{
		/* ... */
	}


	public function getSubscreenIDs(): array
	{
		/* ... */
	}


	public function getSubscreens(): array
	{
		/* ... */
	}


	public function hasSubscreens(): bool
	{
		/* ... */
	}


	public function getSubscreenByID(string $id, bool $adminMode): AdminScreenInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Wizard/WizardConfigurator.php`

```php
namespace Application\Admin\Wizard;

use Application\AppFactory as AppFactory;

/**
 * Session orchestrator and URL builder for the Wizard Preselection API.
 *
 * Creates a wizard session pre-populated with preselection values and returns
 * a redirect URL that the consumer can use to send the user directly to the
 * wizard with step fields pre-filled.
 *
 * Usage example:
 *
 * <pre>
 * $configurator = new WizardConfigurator($wizardURL);
 * $configurator->getPreselection()
 *     ->setStepValue('Countries', 'country_id', 'GB');
 *
 * $redirectURL = $configurator->getRedirectURL();
 * // -> "https://example.com/admin/?page=wizardtest&mode=wizard&wizard=WZ12345678"
 * </pre>
 *
 * The `settingPrefix` constructor parameter must match the wizard's own
 * `$settingPrefix` property (defaults to `''`, which is the default for all
 * current wizards). Pass a non-empty prefix only if the target wizard calls
 * `setSettingPrefix()`.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard
 * @see WizardPreselection
 */
class WizardConfigurator
{
	/**
	 * Returns the preselection value store. Use this to set step values
	 * before calling {@see getRedirectURL()}.
	 *
	 * @return WizardPreselection
	 */
	public function getPreselection(): WizardPreselection
	{
		/* ... */
	}


	/**
	 * Creates the wizard session (if not yet created), writes the preselection
	 * values into the session step data slots, and returns a URL with the
	 * wizard session ID appended.
	 *
	 * Calling this method multiple times returns the same URL and reuses the
	 * same session (idempotent after first call).
	 *
	 * @return string The full wizard URL including the ?wizard=<sessionID> parameter.
	 */
	public function getRedirectURL(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Wizard/WizardPreselection.php`

```php
namespace Application\Admin\Wizard;

/**
 * Typed key/value container that stores preselected step values as a
 * nested map (stepName → key → value). Used to pre-populate wizard
 * step properties before redirecting a user to the wizard.
 *
 * Usage example — build the preselection, then pass it to the wizard:
 *
 * <pre>
 * $preselection = (new WizardPreselection())
 *     ->setStepValue('step-account', 'userID', 42)
 *     ->setStepValue('step-account', 'role', 'admin')
 *     ->setStepValue('step-confirm', 'sendEmail', true);
 *
 * // Read back values for a specific step
 * if ($preselection->hasStepValues('step-account')) {
 *     $values = $preselection->getStepValues('step-account');
 *     // $values === array('userID' => 42, 'role' => 'admin')
 * }
 *
 * // Inspect all steps at once
 * $allSteps   = $preselection->getStepNames(); // array('step-account', 'step-confirm')
 * $allValues  = $preselection->toArray();       // full nested map
 * $hasNothing = $preselection->isEmpty();       // false
 * </pre>
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class WizardPreselection
{
	public const ERROR_STEP_CLASS_MISSING_STEP_NAME = 558101;

	/**
	 * Stores a value for a specific step and key.
	 *
	 * @param string $stepName The wizard step name. Must exactly match the string passed to
	 *                         `addStep()` in the target wizard's `_initSteps()` method
	 *                         (e.g., `'Countries'`, not `'country'` or `'step-countries'`).
	 *                         A mismatch will silently produce preselection values that the
	 *                         wizard step never receives.
	 * @param string $key The key within the step.
	 * @param mixed $value The value to preselect.
	 * @return $this
	 */
	public function setStepValue(string $stepName, string $key, mixed $value): self
	{
		/* ... */
	}


	/**
	 * Stores a value for a step identified by its class name. The step name
	 * is resolved from the class's `STEP_NAME` constant.
	 *
	 * This method provides type-safe step identification: using `StepClass::class`
	 * gives IDE auto-completion, refactoring support, and autoloader validation
	 * that string literals cannot offer.
	 *
	 * @param class-string<\Application_Admin_Wizard_Step> $stepClass Fully-qualified step class name.
	 *        The class must declare a public `STEP_NAME` constant whose value matches
	 *        the string passed to `addStep()` in the target wizard's `_initSteps()`.
	 * @param string $key The key within the step.
	 * @param mixed $value The value to preselect.
	 * @return $this
	 *
	 * @throws \Application_Exception When the step class does not declare a `STEP_NAME` constant.
	 *         {@see self::ERROR_STEP_CLASS_MISSING_STEP_NAME}
	 */
	public function setStepValueByClass(string $stepClass, string $key, mixed $value): self
	{
		/* ... */
	}


	/**
	 * Returns all preselection values for the given step.
	 *
	 * @param string $stepName The wizard step name.
	 * @return array<string,mixed> The stored key/value pairs, or an empty array if none are set.
	 */
	public function getStepValues(string $stepName): array
	{
		/* ... */
	}


	/**
	 * Returns whether any preselection values exist for the given step.
	 *
	 * @param string $stepName The wizard step name.
	 * @return bool True if at least one value has been set for this step.
	 */
	public function hasStepValues(string $stepName): bool
	{
		/* ... */
	}


	/**
	 * Returns the full nested values map.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function toArray(): array
	{
		/* ... */
	}


	/**
	 * Returns whether no preselection values have been set at all.
	 *
	 * @return bool True when the values map is empty.
	 */
	public function isEmpty(): bool
	{
		/* ... */
	}


	/**
	 * Returns the list of step names that have preselection values.
	 *
	 * @return string[]
	 */
	public function getStepNames(): array
	{
		/* ... */
	}
}


```