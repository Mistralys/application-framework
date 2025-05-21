<?php
/**
 * @package Application Tests
 * @subpackage Formables
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application_Formable;

/**
 * Stub class for testing the client form rendering.
 *
 * @package Application Tests
 * @subpackage Formables
 */
class ClientFormStub extends Application_Formable
{
    public function __construct()
    {
        $form = $this->createClientForm('client-form-name');

        $this->initFormable($form);

        $this->setDefaultFormValues(array(
            'text_a' => 'default text a',
            'text_b' => 'default text b'
        ));

        $this->injectFormElements();
    }

    public function render(): string
    {
        return $this->renderFormable();
    }

    private function injectFormElements() : void
    {
        $this->addSection('Section A')
            ->collapse();

        $this->addElementText('text_a', 'Text A');

        $this->addSection('Section B')
            ->expand();

        $this->addElementText('text_b', 'Text B');
    }
}
