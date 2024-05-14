<?php

declare(strict_types=1);

namespace Application\Revisionable;

class TransactionInfo
{
    public const TRANSACTION_ROLLED_BACK = 'rolled_back';
    public const TRANSACTION_UNCHANGED = 'unchanged';
    public const TRANSACTION_CHANGED = 'changed';

    private string $result;
    private RevisionableStatelessInterface $revisionable;
    private ?int $newRevision;
    private int $sourceRevision;
    private bool $simulated;

    public function __construct(RevisionableStatelessInterface $revisionable, string $result, bool $simulated, int $sourceRevision, ?int $newRevision)
    {
        $this->sourceRevision = $sourceRevision;
        $this->newRevision = $newRevision;
        $this->revisionable = $revisionable;
        $this->result = $result;
        $this->simulated = $simulated;
    }

    public function isSimulated() : bool
    {
        return $this->simulated;
    }

    public function getSourceRevision(): int
    {
        return $this->sourceRevision;
    }

    public function getRevisionable() : RevisionableStatelessInterface
    {
        return $this->revisionable;
    }

    /**
     * Gets the result type of the transaction.
     *
     * @return string
     *
     * @see self::TRANSACTION_UNCHANGED
     * @see self::TRANSACTION_CHANGED
     * @see self::TRANSACTION_ROLLED_BACK
     */
    public function getResultID() : string
    {
        return $this->result;
    }

    public function getNewRevision() : ?int
    {
        return $this->newRevision;
    }

    public function isUnchanged() : bool
    {
        return $this->getResultID() === self::TRANSACTION_UNCHANGED;
    }

    public function isChanged() : bool
    {
        return $this->getResultID() === self::TRANSACTION_CHANGED;
    }

    public function isRolledBack() : bool
    {
        return $this->getResultID() === self::TRANSACTION_ROLLED_BACK;
    }

    public function isNewRevision() : bool
    {
        return $this->getNewRevision() > 0;
    }
}