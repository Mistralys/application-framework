<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\StatusHandling;

/**
 * Trait used to implement filter selection methods for
 * the standard state setup.
 *
 * @package Application
 * @subpackage Revisionables
 *
 * @see StandardStateSetupFilterInterface
 */
trait StandardStateSetupFilterTrait
{
    public function selectFinalized() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_FINALIZED);
    }

    public function selectDrafts() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_DRAFT);
    }

    public function selectDeleted() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_DELETED);
    }

    public function selectInactive() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_INACTIVE);
    }

    public function excludeFinalized() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_FINALIZED, true);
    }

    public function excludeDrafts() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_DRAFT, true);
    }

    public function excludeDeleted() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_DELETED, true);
    }

    public function excludeInactive() : self
    {
        return $this->selectState(StandardStateSetupInterface::STATUS_INACTIVE, true);
    }
}
