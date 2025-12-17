<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application\Admin\AdminScreenStubInterface;
use Application\Admin\BaseArea;

class StubArea extends BaseArea implements AdminScreenStubInterface
{
    public function getURLName(): string
    {
        return 'stub-area';
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

    public function getDefaultMode(): string
    {
        return '';
    }

    public function getNavigationGroup(): string
    {
        return '';
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }
}
