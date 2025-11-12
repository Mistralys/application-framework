<?php
/**
 * @package Application Tests
 * @subpackage Stubs
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application\Revisionable\RevisionDependentInterface;
use Application\Traits\RevisionDependentTrait;
use Application\Disposables\DisposableInterface;
use Application\Disposables\DisposableTrait;
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
 * @see \Application\Disposables\DisposableInterface
 * @see DisposableTrait
 */
class RevisionDependentDisposableStub
    extends RevisionDependentStub
    implements DisposableInterface
{
    use DisposableTrait;
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
