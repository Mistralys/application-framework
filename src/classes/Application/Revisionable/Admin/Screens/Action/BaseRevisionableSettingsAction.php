<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Action;

use Application\Revisionable\Admin\Traits\RevisionableSettingsScreenInterface;
use Application\Revisionable\Admin\Traits\RevisionableSettingsScreenTrait;

abstract class BaseRevisionableSettingsAction
    extends BaseRevisionableRecordAction
    implements RevisionableSettingsScreenInterface
{
    use RevisionableSettingsScreenTrait;

    final protected function isEditMode(): bool
    {
        return true;
    }
}
