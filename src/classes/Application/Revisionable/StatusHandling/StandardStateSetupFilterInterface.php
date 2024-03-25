<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable\StatusHandling;

use Application\RevisionableCollection\RevisionableFilterCriteriaInterface;

/**
 * See the trait: {@see StandardStateSetupFilterTrait}
 *
 * @package Application
 * @subpackage Revisionables
 *
 * @see StandardStateSetupFilterTrait
 */
interface StandardStateSetupFilterInterface extends RevisionableFilterCriteriaInterface
{
    public function selectFinalized() : self;
    public function selectDrafts() : self;
    public function selectDeleted() : self;
    public function selectInactive() : self;

    public function excludeFinalized() : self;
    public function excludeDrafts() : self;
    public function excludeDeleted() : self;
    public function excludeInactive() : self;
}
