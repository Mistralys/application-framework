<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Action;

use Application\Revisionable\Admin\Traits\RevisionableChangelogScreenInterface;
use Application\Revisionable\Admin\Traits\RevisionableChangelogScreenTrait;

abstract class BaseRevisionableChangelogAction
    extends BaseRevisionableRecordAction
    implements RevisionableChangelogScreenInterface
{
    use RevisionableChangelogScreenTrait;
}
