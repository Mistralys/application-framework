<?php

declare(strict_types=1);

namespace Application\Revisionable\StatusHandling;

use Application\Revisionable\RevisionableInterface;

interface StandardStateSetupInterface extends RevisionableInterface
{
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DELETED = 'deleted';

    public function makeFinalized() : self;
    public function makeInactive() : self;
    public function makeDeleted() : self;
    public function makeDraft() : self;

    public function isFinalized() : bool;
    public function isInactive() : bool;
    public function isDeleted() : bool;
    public function isDraft() : bool;

    public function canBeMadeInactive() : bool;
    public function canBeDeleted() : bool;
    public function canBeFinalized() : bool;
    public function canBeDestroyed() : bool;
}
