<?php

declare(strict_types=1);

namespace Application\Admin\Area\Mode;

use Application_Admin_Area_Mode;
use RevisionableSettingsTrait;

abstract class RevisionableCreateScreen extends Application_Admin_Area_Mode
{
    use RevisionableSettingsTrait;

    public function getDefaultSubmode(): string
    {
        return '';
    }

    protected function isEditMode(): bool
    {
        return false;
    }

    protected function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }
}
