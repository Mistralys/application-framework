<?php

declare(strict_types=1);

namespace testsuites\UI;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI;

final class ButtonTest extends ApplicationTestCase
{
    public function test_clickHandler() : void
    {
        $js = 'JavaScriptToExecute()';

        $btn = UI::button('label')
            ->click($js);

        $this->assertStringContainsString('onclick="'.$js.'"', $btn->render());
    }

    /**
     * The action of the button depends on the
     * method called to set the action. If several
     * action methods are called, it is always the
     * last one called that is used.
     */
    public function test_actionPrecedence() : void
    {
        $btn = UI::button('label');

        // By default, the button has no action
        $this->assertFalse($btn->isSubmittable());
        $this->assertFalse($btn->isClickable());
        $this->assertFalse($btn->isLinked());

        $btn->link('https://mistralys.eu');

        // The button is now a link
        $this->assertFalse($btn->isSubmittable());
        $this->assertFalse($btn->isClickable());
        $this->assertTrue($btn->isLinked());

        $btn->makeSubmit('submitter', 'value');

        // The button is now a submit button, overriding the link
        $this->assertTrue($btn->isSubmittable());
        $this->assertFalse($btn->isClickable());
        $this->assertFalse($btn->isLinked());

        $btn->click('statement');

        // The button is now a JS onclick handler
        $this->assertFalse($btn->isSubmittable());
        $this->assertTrue($btn->isClickable());
        $this->assertFalse($btn->isLinked());
    }

    public function test_submit() : void
    {
        $html = UI::button('label')
            ->makeSubmit('submitter', 'submit-value')
            ->render();

        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('submit-value', $html);
    }

    /**
     * Test for a bug, where the submit value was present
     * even if the button was only linked.
     */
    public function test_noSubmitWhenLinked() : void
    {
        $html = UI::button('label')
            ->makeSubmit('submitter', 'submit-value')
            ->link('https://mistralys.eu')
            ->render();

        $this->assertStringNotContainsString('type="submit"', $html);
        $this->assertStringNotContainsString('submit-value', $html);
        $this->assertStringContainsString('mistralys.eu', $html);
    }
}
