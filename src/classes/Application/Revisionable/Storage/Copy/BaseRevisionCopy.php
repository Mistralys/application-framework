<?php

declare(strict_types=1);

namespace Application\Revisionable\Storage\Copy;

use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\Storage\BaseRevisionStorage;
use Application\Revisionable\Storage\RevisionStorageException;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use DateTime;

abstract class BaseRevisionCopy
    implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const int ERROR_CLASS_MISMATCH_FOR_TARGET_REVISIONABLE = 720001;

    protected int $sourceRevision;
    protected int $targetRevision;
    protected int $ownerID;
    protected string $ownerName;
    protected string $comments;
    protected RevisionableInterface $revisionable;
    protected ?RevisionableInterface $targetRevisionable = null;

    protected DateTime $date;
    protected BaseRevisionStorage $storage;

    public function __construct(BaseRevisionStorage $storage, RevisionableInterface $revisionable, int $sourceRevision, int $targetRevision, int $ownerID, string $ownerName, ?string $comments, ?DateTime $date = null)
    {
        if (!$date) {
            $date = new DateTime();
        }

        $this->storage = $storage;
        $this->revisionable = $revisionable;
        $this->sourceRevision = $sourceRevision;
        $this->targetRevision = $targetRevision;
        $this->ownerID = $ownerID;
        $this->ownerName = $ownerName;
        $this->comments = (string)$comments;
        $this->date = $date;

        $this->init();
    }

    protected function init(): void
    {

    }

    /**
     * Sets a target revisionable to copy the revision to.
     * @param RevisionableInterface $revisionable
     * @throws RevisionStorageException
     */
    public function setTarget(RevisionableInterface $revisionable): void
    {
        $sourceType = $this->revisionable->getRecordTypeName();
        $targetType = $revisionable->getRecordTypeName();

        if ($sourceType !== $targetType) {
            throw new RevisionStorageException(
                'Not a valid revisionable',
                sprintf(
                    'The target revisionable type [%s] does not match that of the revisionable to copy from: [%s].',
                    $targetType,
                    $sourceType
                ),
                self::ERROR_CLASS_MISMATCH_FOR_TARGET_REVISIONABLE
            );
        }

        $this->targetRevisionable = $revisionable;
        $this->targetRevision = $revisionable->getRevision();

        $this->log(
            'Set the target revisionable to [%s] in revision [%s].',
            get_class($revisionable),
            $this->targetRevision
        );
    }

    /**
     * @return array<int,callable|string>
     */
    abstract protected function getParts(): array;

    public function process(): void
    {
        $this->log('Starting copy.');

        // if no target revisionable object has been set to copy the
        // revision to, we use the source revisionable to create a
        // copy within the same object.
        $target = $this->targetRevisionable ?? $this->revisionable;

        // store it for anyone accessing the property
        $this->targetRevisionable = $target;

        if ($this->storage->hasDataKeys()) {
            $this->processDataKeys($target);
        }

        $this->processParts($target);

        $this->log('Copy complete.');
    }

    protected function processParts(RevisionableInterface $targetRevisionable): void
    {
        $parts = $this->getParts();

        foreach ($parts as $part) {
            if (is_callable($part)) {
                $part($targetRevisionable);
                continue;
            }

            $method = 'copy' . ucfirst($part);
            $this->$method($targetRevisionable);
        }
    }

    protected function processDataKeys(RevisionableInterface $targetRevisionable): void
    {
        if (!$this->storage->hasDataKeys()) {
            $this->log('The revisionable has no data keys, skipping.');
            return;
        }

        $this->log('Processing the revisionable\'s data keys.');

        $this->_processDataKeys($targetRevisionable);
    }

    abstract protected function _processDataKeys(RevisionableInterface $targetRevisionable): void;

    public function getLogIdentifier(): string
    {
        return sprintf(
            '%3$s RevisionCopy | [v%1$s] to [v%2$s]',
            $this->sourceRevision,
            $this->targetRevision,
            $this->revisionable->getRecordTypeName()
        );
    }

    protected bool $debug = false;

    protected function enableDebug(bool $enable = true): self
    {
        $this->debug = $enable;
        return $this;
    }
}
