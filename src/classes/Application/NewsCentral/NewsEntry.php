<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application\AppFactory;
use Application_Users_User;
use DateTime;
use DBHelper_BaseRecord;

class NewsEntry extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_LABEL);
    }

    public function getAuthorID() : int
    {
        return $this->getRecordIntKey(NewsCollection::COL_AUTHOR);
    }

    public function getAuthor() : Application_Users_User
    {
        return AppFactory::createUsers()->getByID($this->getAuthorID());
    }

    public function getSynopsis(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_SYNOPSIS);
    }

    public function getArticle(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_ARTICLE);
    }

    public function getCriticalityID(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_CRITICALITY);
    }

    public function getScheduledFromDate(): ?DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_SCHEDULED_FROM_DATE);
    }

    public function getScheduledToDate(): ?DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_SCHEDULED_TO_DATE);
    }

    public function isReceiptRequired() : bool
    {
        return $this->getRecordBooleanKey(NewsCollection::COL_REQUIRES_RECEIPT);
    }

    public function getDateCreated(): DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_DATE_CREATED);
    }

    public function getDateModified(): DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_DATE_MODIFIED);
    }
}
