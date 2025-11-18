<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Submode;

use Application\Revisionable\Admin\Traits\RevisionableSettingsScreenInterface;
use Application\Revisionable\Admin\Traits\RevisionableSettingsScreenTrait;

abstract class BaseRevisionableSettingsSubmode
    extends BaseRevisionableRecordSubmode
    implements RevisionableSettingsScreenInterface
{
    use RevisionableSettingsScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }

    final protected function isEditMode(): bool
    {
        return true;
    }
}
