<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Mode;

use Application_Admin_Area_Mode;
use DBHelper\Admin\Traits\RecordCreateScreenInterface;
use DBHelper\Admin\Traits\RecordSettingsScreenTrait;

abstract class BaseRecordCreateMode extends Application_Admin_Area_Mode implements RecordCreateScreenInterface
{
    use RecordSettingsScreenTrait;

    public function isUserAllowedEditing(): bool
    {
        return $this->isUserAllowed();
    }

    final public function isEditMode(): bool
    {
        return false;
    }

    final public function getDefaultSubmode(): string
    {
        return '';
    }
}
