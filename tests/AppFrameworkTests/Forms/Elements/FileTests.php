<?php

declare(strict_types=1);

namespace AppFrameworkTests\Forms\Elements;

use AppFrameworkTestClasses\FormTestCase;
use UI;

final class FileTests extends FormTestCase
{
    public function test_canCreateFileElement() : void
    {
        $form = UI::getInstance()->createForm('test');

        $form->addFile('upload', 'Upload');

        $this->addToAssertionCount(1);
    }
}
