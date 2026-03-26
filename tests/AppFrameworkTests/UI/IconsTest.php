<?php

declare(strict_types=1);

namespace AppFrameworkTests\UI;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI;

final class IconsTest extends ApplicationTestCase
{
    public function test_render() : void
    {
        $html = UI::icon()->code()->render();

        $this->assertStringContainsString('fa-code', $html);
    }

    public function test_addClass() : void
    {
        $html = UI::icon()->code()
            ->addClass('myclass')
            ->render();

        $this->assertStringContainsString('myclass', $html);
    }
}
