<?php

declare(strict_types=1);

namespace NewsCentral\Entries;

use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application\NewsCentral\NewsEntryCriticality;

class NewsAlert extends NewsEntry
{
    public function getMessage(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_SYNOPSIS);
    }

    public function getCriticalityID(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_CRITICALITY);
    }

    public function isReceiptRequired() : bool
    {
        return $this->getRecordBooleanKey(NewsCollection::COL_REQUIRES_RECEIPT);
    }

    public function setCriticality(NewsEntryCriticality $criticality) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_CRITICALITY, $criticality->getID());
    }

    public function setRequiresReceipt(bool $required) : bool
    {
        return $this->setRecordBooleanKey(NewsCollection::COL_REQUIRES_RECEIPT, $required);
    }
}
