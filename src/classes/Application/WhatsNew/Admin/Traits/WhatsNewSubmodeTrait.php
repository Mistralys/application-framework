<?php

declare(strict_types=1);

namespace Application\WhatsNew\Admin\Traits;

use Application\WhatsNew\Admin\Screens\WhatsNewEditorMode;

trait WhatsNewSubmodeTrait
{
    public function getDefaultAction(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function getParentScreenClass(): string
    {
        return WhatsNewEditorMode::class;
    }
}
