<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\UI;

use UI;
use UI\Traits\ButtonDecoratorInterface;
use UI_Button;
use UI_Traits_Conditional;
use UI\Traits\ButtonDecoratorTrait;

class GenericButtonStub implements ButtonDecoratorInterface
{
    use ButtonDecoratorTrait;
    use UI_Traits_Conditional;

    protected function _getButtonInstance(): UI_Button
    {
        return UI::button('Stub Button');
    }
}
