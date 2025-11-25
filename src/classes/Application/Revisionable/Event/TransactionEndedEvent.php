<?php

declare(strict_types=1);

namespace Application\Revisionable\Event;

use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\TransactionInfo;
use Application_EventHandler_EventableEvent;

class TransactionEndedEvent extends Application_EventHandler_EventableEvent
{
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
