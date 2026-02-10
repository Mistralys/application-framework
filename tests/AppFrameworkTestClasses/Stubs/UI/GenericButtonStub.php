<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\UI;

use UI;
use UI\Traits\ButtonDecoratorInterface;
use UI\Traits\ButtonDecoratorTrait;
use UI_Button;
use UI_Traits_Conditional;

class GenericButtonStub implements ButtonDecoratorInterface
{
    use ButtonDecoratorTrait;
    use UI_Traits_Conditional;

    protected function _getButtonInstance(): UI_Button
    {
        return UI::button('Stub Button');
    }
}
