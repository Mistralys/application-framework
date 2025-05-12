<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Stubs\ClientFormStub;
use UI\Page\Section\GroupControls;

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
}
