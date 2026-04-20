## Coding Patterns

## Checking instance types

Use the `ClassHelper` static methods to check for class types. This will throw
an exception if the type does not match, and guarantees that static analyzers
like PHPStan can infer the correct type after the check.

```php
<?php
use AppUtils\ClassHelper;

$campaign = ClassHelper::requireObjectInstanceOf(
    CampaignRecord::class,
    $record
);
```

## Creating Objects Lazily

Use `isset()` for the property to initialize it as needed.

```php
<?php

class CampaignService 
{
    private ?CampaignManager $campaignManager = null;

    public function getCampaignManager() : CampaignManager 
    {
        if (!isset($this->campaignManager)) {
            $this->object = new CampaignManager();
        }
        
        return $this->campaignManager;
    }
}
```

## Testing Singletons

Singleton classes hold a static `$instance` that persists across test cases in the
same PHPUnit process. Without isolation, one test's state leaks into the next,
producing hard-to-diagnose failures.

**Convention:** any singleton that needs to be tested **must** expose a
`public static resetInstance() : void` method annotated with `@internal`. Test
classes covering that singleton **must** call `resetInstance()` in their
`tearDown()` method.

```php
<?php
// In the singleton class:

/**
 * Resets the singleton instance to null.
 *
 * @internal For use in tests only.
 * @return void
 */
public static function resetInstance() : void
{
    self::$instance = null;
}
```

```php
<?php
// In the corresponding test class:

protected function tearDown() : void
{
    MySingleton::resetInstance();
}
```

- `resetInstance()` is **unconditional** — no null-guard is needed because
  assigning `null` to an already-null property is a PHP no-op.
- `tearDown()` runs after every test, regardless of pass/fail, ensuring a clean
  slate for the next case.
- The `@internal` annotation signals that `resetInstance()` is not part of the
  public API and must not be called from production code.

See `IconCollection::resetInstance()` and `IconCollectionTest::tearDown()` for
the reference implementation.
