<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application\Admin\Area\BaseMode;

class StubMode extends BaseMode
{
    public function getURLName(): string
    {
        return 'stub-mode';
    }

    public function getNavigationTitle(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getRequiredRight(): ?string
    {
        return null;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }
}
