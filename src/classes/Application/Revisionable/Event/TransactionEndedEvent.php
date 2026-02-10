<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\TransactionInfo;

class TransactionEndedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'TransactionEnded';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getRevisionable() : RevisionableInterface
    {
        return $this->getTransactionInfo()->getRevisionable();
    }

    public function getTransactionInfo() : TransactionInfo
    {
        return $this->getArgumentObject(0, TransactionInfo::class);
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
