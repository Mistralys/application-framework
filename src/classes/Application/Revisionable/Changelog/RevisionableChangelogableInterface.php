<?php

declare(strict_types=1);

namespace Application\Revisionable\Changelog;

use Application\Interfaces\ChangelogableInterface;
use Application\Revisionable\RevisionableCollectionInterface;

interface RevisionableChangelogableInterface extends ChangelogableInterface
{
    public function getChangelog() : RevisionableChangelog;
    public function countChangelogEntries() : int;
    public function enableChangelog() : self;
    public function disableChangelog() : self;
    public function setChangelogEnabled(bool $enabled=true) : self;
    public function isChangelogEnabled() : bool;
    public function clearChangelogQueue() : void;
    public function getCollection() : RevisionableCollectionInterface;
}
