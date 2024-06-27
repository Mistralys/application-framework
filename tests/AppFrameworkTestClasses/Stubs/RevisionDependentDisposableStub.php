<?php
/**
 * @package Application Tests
 * @subpackage Stubs
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\RevisionableStatelessInterface;
use Application\Revisionable\RevisionDependentInterface;
use Application\Traits\RevisionDependentTrait;
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

    protected function _getIdentification(): string
    {
        return sprintf(
            '%s | RevisionDependentDisposableStub',
            $this->getRevisionable()->getIdentification()
        );
    }
}
