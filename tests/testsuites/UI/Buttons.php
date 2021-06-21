<?php

declare(strict_types=1);

final class UI_ButtonsTest extends ApplicationTestCase
{
    public function test_clickHandler() : void
    {
        $js = 'JavaScriptToExecute()';

        $btn = UI::button('label')
            ->click($js);

        $this->assertStringContainsString('onclick="'.$js.'"', $btn->render());
    }
}
