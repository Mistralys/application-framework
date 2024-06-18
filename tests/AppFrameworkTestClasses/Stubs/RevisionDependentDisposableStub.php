<?php
/**
 * @package Application Tests
 * @subpackage Stubs
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application_Interfaces_Disposable;
use Application_Traits_Disposable;
use Application_Traits_Eventable;
use Application_Traits_Loggable;

/**
 * Implementation of an object that is both revision-dependent
 * and disposable.
 *
 * @package Application Tests
 * @subpackage Stubs
 *
 * @see RevisionDependentInterface
 * @see RevisionDependentTrait
 * @see Application_Interfaces_Disposable
 * @see Application_Traits_Disposable
 */
class RevisionDependentDisposableStub
    extends RevisionDependentStub
    implements Application_Interfaces_Disposable
{
    use Application_Traits_Disposable;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    public function getIdentification(): string
    {
        return 'RevisionDependentDisposableStub';
    }

    public function getChildDisposables(): array
    {
        return array();
    }

    protected function _dispose(): void
    {
    }

    public function getLogIdentifier(): string
    {
        return $this->getIdentification();
    }
}
