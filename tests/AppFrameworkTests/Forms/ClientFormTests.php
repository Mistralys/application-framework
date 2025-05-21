<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Stubs\ClientFormStub;
use Application_Formable;
use UI\Page\Section\GroupControls;
use UI_Page_Section;

final class ClientFormTests extends ApplicationTestCase
{
    /**
     * Ensure that section group controls are rendered even
     * when using client-side forms, which bypass the usual
     * page rendering.
     *
     * @see GroupControls::handleClientFormRendered()
     */
    public function test_formSectionControlsAreRendered() : void
    {
        $form = new ClientFormStub();

        $this->assertStringNotContainsString(
            GroupControls::CONTROLS_PREFIX,
            $form->render(),
        );
    }

    public function test_buttonsAreFunctional() : void
    {
        $form = new ClientFormStub();

        $this->assertEquals($form->getUI()->getInstanceKey(), $form->getFormInstance()->getUI()->getInstanceKey());
        $this->assertStringContainsString(Application_Formable::CLIENT_FORM_UI_PREFIX, $form->getUI()->getInstanceKey());

        $html = $form->render();

        //echo $this->saveTestFile($html, 'html')->summarize();

        $this->assertStringNotContainsString(GroupControls::CONTROLS_PREFIX, $html);
        $this->assertStringContainsString(UI_Page_Section::STYLESHEET_FILE, $html);
        $this->assertStringContainsString('UI.RegisterSection', $html);
    }
}
