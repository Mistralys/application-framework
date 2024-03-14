<?php

declare(strict_types=1);

namespace TestDriver\Revisionables\Storage;

use BaseDBCollectionStorage;
use TestDriver\Revisionables\RevisionableRecord;

/**
 * @see RevisionCopy
 * @property RevisionableRecord $revisionable
 */
class RevisionableStorage extends BaseDBCollectionStorage
{

}
