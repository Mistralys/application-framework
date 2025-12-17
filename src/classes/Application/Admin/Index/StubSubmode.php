<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application\Admin\AdminScreenStubInterface;
use Application\Admin\Area\Mode\BaseSubmode;

class StubSubmode extends BaseSubmode implements AdminScreenStubInterface
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

    public function getDefaultAction(): string
    {
        return '';
    }
}
