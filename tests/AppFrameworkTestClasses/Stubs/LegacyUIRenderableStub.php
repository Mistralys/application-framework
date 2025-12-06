<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

class LegacyUIRenderableStub implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const string RETURN_VALUE = 'Stringified';

    public function render(): string
    {
        return self::RETURN_VALUE;
    }
}
